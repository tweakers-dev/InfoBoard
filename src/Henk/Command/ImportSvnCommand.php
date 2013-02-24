<?php

namespace Henk\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use Webcreate\Vcs\Svn;
use Webcreate\Vcs\Common\Commit;
use Webcreate\Vcs\Common\Pointer;
use Webcreate\Vcs\Common\VcsFileInfo;
use Webcreate\Vcs\Common\Reference;

class ImportSvnCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import:svn')
            ->setDescription('Import svn')
            ->addArgument('repo', InputArgument::REQUIRED, 'The url of the repository')
            ->addArgument('branch', InputArgument::OPTIONAL, 'The branch of the repository')
            ->addOption('nrOfChanges', null, InputOption::VALUE_NONE, 'Limit the import to a number of changes. The default is 100.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getContainer();

        $repoUrl = $input->getArgument('repo');
        $branch = $input->getArgument('branch');
        if (!$branch)
            $branch = 'trunk';
        $nrOfChanges = $input->getOption('nrOfChanges');
        if (!$nrOfChanges)
            $nrOfChanges = 100;

        $text = "Importing $nrOfChanges changes from $repoUrl";
        $output->writeln($text);

        $svn = new Svn($repoUrl);
        $path = new VcsFileInfo('/', new Reference($branch), VcsFileInfo::BRANCH);

        /**
         * @var $result Commit[]
         */
        $result = $svn->log($path, null, $nrOfChanges);

        $this->saveLatestCommits($app, $result);
        $this->rebuildCommitReports($app, $result);
    }

    protected function saveLatestCommits($app, array $commits)
    {
        /**
         * @var $predis \Predis\Client
         */
        $predis = $app['predis'];
        $predis->set('svn', serialize($commits));
    }

    /**
     * @param $app
     * @param Commit[] $commits
     */
    protected function rebuildCommitReports($app, array $commits)
    {
        /**
         * @var $predis \Predis\Client
         */
        $predis = $app['predis'];

        $commitsPerDays = unserialize($predis->get('svndaily'));
        if (!$commitsPerDays)
            $commitsPerDays = array();

        foreach($commits as $commit)
        {
            $day = $commit->getDate();
            $dayFormat = $day->format('dmY');

            if (!isset($commitsPerDays[$dayFormat]))
                $commitsPerDays[$dayFormat] = array();

            if (!isset($commitsPerDays[$dayFormat][$commit->getRevision()]))
                $commitsPerDays[$dayFormat][$commit->getRevision()] = $commit;
        }

        $predis->set('svndaily', serialize($commitsPerDays));
    }
}
