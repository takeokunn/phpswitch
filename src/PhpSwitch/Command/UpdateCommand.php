<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;
use PhpSwitch\Downloader\DownloadFactory;
use PhpSwitch\Tasks\FetchReleaseListTask;

class UpdateCommand extends Command
{
    public function brief()
    {
        return 'Update PHP release source file';
    }

    public function options($opts)
    {
        $opts->add('o|old', 'List versions older than PHP 7.0');

        DownloadFactory::addOptionsForCommand($opts);
    }

    public function execute()
    {
        $fetchReleaseListTask = new FetchReleaseListTask($this->logger, $this->options);
        $releases = $fetchReleaseListTask->fetch();

        foreach ($releases as $majorVersion => $versions) {
            if (version_compare($majorVersion, '5.2', '<=')) {
                continue;
            }
            $versionList = array_keys($versions);
            $this->logger->writeln($this->formatter->format("{$majorVersion}: ", 'yellow')
                . count($versionList) . ' releases');
        }
        $this->logger->info('===> Done');
    }
}
