<?php

namespace Henk\Command;

use Cilex\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTwitterCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import:twitter')
            ->setDescription('Import twitter feed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getContainer();

        $consumerKey = $app['config']->twitter->consumerkey;
        $consumerSecret = $app['config']->twitter->consumersecret;
        $accessToken = $app['config']->twitter->accesstoken;
        $accessTokenSecret = $app['config']->twitter->accesstokensecret;

        $twitter = new \Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

        $searchResults = array_merge(
            $this->searchTwitter($twitter, '#tweakers'),
            $this->searchTwitter($twitter, '@tweakers')
        );

        usort($searchResults, array($this, 'compareTweets'));

        /**
         * @var $predis \Predis\Client
         */
        $predis = $app['predis'];
        $predis->set('tweets', serialize($searchResults));
    }

    /**
     * @param \Twitter $twitter
     * @param string $keyword
     * @return array
     */
    protected function searchTwitter(\Twitter $twitter, $keyword)
    {
        $results = $twitter->search($keyword);
        return $results;
    }

    public function compareTweets($tweet1, $tweet2)
    {
        $createdAt1 = strtotime($tweet1->created_at);
        $createdAt2 = strtotime($tweet2->created_at);

        if ($createdAt1 < $createdAt2)
            return 1;

        if ($createdAt1 > $createdAt2)
            return -1;

        return 0;
    }
}
