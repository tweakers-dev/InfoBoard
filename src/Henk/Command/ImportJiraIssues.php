<?php

namespace Henk\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use Jira\JiraClient;
use Jira\Remote\RemoteIssue;

class ImportJiraIssues extends Command
{
    protected function configure()
    {
        $this
            ->setName('import:jira')
            ->setDescription('Import jira issues');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getContainer();

        $host = $app['config']->jira->host;
        $username = $app['config']->jira->user;
        $password = $app['config']->jira->pass;

        $jira = new JiraClient($host);
        $jira->login($username, $password);

        /**
         * @var $issues RemoteIssue[]
         */
        $issues = $jira->issues()->getFromJqlSearch('project = DEV AND status in ("In Progress", "Ready for Development", "To Verify", "To UAT") AND assignee in ("p.devink")');

        usort($issues, array($this, 'compareIssues'));

        /**
         * @var $predis \Predis\Client
         */
        $predis = $app['predis'];
        $predis->set('jiraissues', serialize($issues));
    }

    protected function compareIssues(RemoteIssue $issue1, RemoteIssue $issue2)
    {
        if ($issue1->getPriority() < $issue2->getPriority())
            return 1;

        if ($issue1->getProject() > $issue2->getProject())
            return -1;

        return 0;
    }
}
