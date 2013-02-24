<?php

namespace Henk\Web;

use Predis\Client;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Jira\Remote\RemoteIssue;
use Webcreate\Vcs\Common\Commit;

class DisplayController
{
    /**
     * @var Client
     */
    private $predis;

    public function __construct($predis)
    {
        $this->predis = $predis;
    }

    public function listAction(Application $app, Request $request)
    {
        $commits = $this->getCommits();
        $commitHistory = $this->getCommitHistory();

        /**
         * @var $issues RemoteIssue[]
         */
        $issues = unserialize($this->predis->get('jiraissues'));

        $tweets = $this->getTweets();

        $content = $app['twig']->render('index.html.twig', array(
            'commits'       => $commits,
            'commitHistory' => $commitHistory,
            'issues'        => $issues,
            'tweets'        => $tweets
        ));

        return new Response($content, 200, array('Cache-Control' => 'public'));
    }

    /**
     * @return Commit[]
     */
    protected function getCommits()
    {
        /**
         * @var $commits Commit[]
         */
        $commits = unserialize($this->predis->get('svn'));

        return array_slice($commits, 0, 11);
    }

    protected function getCommitHistory()
    {
        $commitHistory = array();
        $commitsPerDay = unserialize($this->predis->get('svndaily'));

        for($i = 0; $i<14; $i++)
        {
            $day = strtotime("-$i day");
            $dayKey = date('dmY', $day);

            if ($commitsPerDay[$dayKey])
            {
                $commitHistory[date('d-m', $day)] = count($commitsPerDay[$dayKey]);
            }
        }

        return array_reverse($commitHistory);
    }

    protected function getTweets()
    {
        $tweets = unserialize($this->predis->get('tweets'));
        return array_slice($tweets, 0, 10);
    }
}
