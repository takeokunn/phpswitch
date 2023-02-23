<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;
use CLIFramework\ValueCollection;
use Exception;
use GetOptionKit\OptionCollection;
use PhpSwitch\Config;
use PhpSwitch\Distribution\DistributionUrlPolicy;
use PhpSwitch\Downloader\DownloadFactory;
use PhpSwitch\ReleaseList;
use PhpSwitch\Tasks\DownloadTask;
use PhpSwitch\Tasks\PrepareDirectoryTask;

class DownloadCommand extends Command
{
    public function brief()
    {
        return 'Download php';
    }

    public function usage()
    {
        return 'phpswitch download [php-version]';
    }

    public function arguments($args)
    {
        $args->add('version')->suggestions(function () {
            $releaseList = ReleaseList::getReadyInstance();
            $releases = $releaseList->getReleases();

            $valueCollection = new ValueCollection();
            foreach ($releases as $major => $versions) {
                $valueCollection->group($major, "PHP $major", array_keys($versions));
            }

            $valueCollection->group('pseudo', 'pseudo', ['latest', 'next']);

            return $valueCollection;
        });
    }

    /**
     * @param OptionCollection $opts
     */
    public function options($opts)
    {
        $opts->add('f|force', 'Force extraction');
        $opts->add('old', 'enable old phps (less than 5.3)');

        DownloadFactory::addOptionsForCommand($opts);
    }

    public function execute($version)
    {
        $version = preg_replace('/^php-/', '', (string) $version);
        $releaseList = ReleaseList::getReadyInstance($this->options);
        $versionInfo = $releaseList->getVersion($version);
        if (!$versionInfo) {
            throw new Exception("Version $version not found.");
        }
        $version = $versionInfo['version'];
        $distributionUrlPolicy = new DistributionUrlPolicy();
        $distUrl = $distributionUrlPolicy->buildUrl($version, $versionInfo['filename'], $versionInfo['museum']);

        $prepareDirectoryTask = new PrepareDirectoryTask($this->logger, $this->options);
        $prepareDirectoryTask->run();

        $distFileDir = Config::getDistFileDir();

        $downloadTask = new DownloadTask($this->logger, $this->options);
        $algo = 'md5';
        $hash = null;
        if (isset($versionInfo['sha256'])) {
            $algo = 'sha256';
            $hash = $versionInfo['sha256'];
        } elseif (isset($versionInfo['md5'])) {
            $algo = 'md5';
            $hash = $versionInfo['md5'];
        }
        $targetDir = $downloadTask->download($distUrl, $distFileDir, $algo, $hash);

        if (!file_exists($targetDir)) {
            throw new Exception('Download failed.');
        }
        $this->logger->info("Done, please look at: $targetDir");
    }
}
