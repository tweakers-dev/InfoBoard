<?php

namespace Henk\Command;

use Cilex\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportJenkinsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import:jenkins')
            ->setDescription('Import Jenkins status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getContainer();

        $host = $app['config']->jenkins->host;
        $username = $app['config']->jenkins->user;
        $password = $app['config']->jenkins->pass;

        $curlHandler = curl_init($host.'/api/json');
        curl_setopt($curlHandler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curlHandler, CURLOPT_USERPWD, $username.":".$password);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curlHandler);

        $error = curl_error($curlHandler);
        if ($error)
        {
            var_dump($error);
            return;
        }

        curl_close($curlHandler);

        $jsonData = json_decode($data);

        $jobStatus = array();
        foreach($jsonData->jobs as $job)
        {
            $jobStatus[$job->name] = $job->color;
        }

        /**
         * @var $predis \Predis\Client
         */
        $predis = $app['predis'];
        $predis->set('jenkinsStatus', serialize($jobStatus));
    }
}
