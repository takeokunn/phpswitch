<?php

namespace PhpSwitch\Extension;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;
use PhpSwitch\Config;
use PhpSwitch\Downloader\DownloadFactory;
use PhpSwitch\Extension\Provider\Provider;
use PhpSwitch\Utils;

class ExtensionDownloader
{
    public $logger;

    public $options;

    public function __construct(Logger $logger, OptionResult $optionResult)
    {
        $this->logger = $logger;
        $this->options = $optionResult;
    }

    public function download(Provider $provider, $version = 'stable')
    {
        $url = $provider->buildPackageDownloadUrl($version);
        $basename = $provider->resolveDownloadFileName($version);
        $distFileDir = Config::getDistFileDir();
        $targetFilePath = $distFileDir . DIRECTORY_SEPARATOR . $basename;
        DownloadFactory::getInstance($this->logger, $this->options)->download($url, $targetFilePath);

        $currentPhpExtensionDirectory = Config::getBuildDir() . '/' . Config::getCurrentPhpName() . '/ext';

        // tar -C ~/.phpswitch/build/php-5.5.8/ext -xvf ~/.phpswitch/distfiles/memcache-2.2.7.tgz
        $extensionDir = $currentPhpExtensionDirectory . DIRECTORY_SEPARATOR . $provider->getPackageName();
        if (!file_exists($extensionDir)) {
            mkdir($extensionDir, 0755, true);
        }

        $this->logger->info("===> Extracting to $currentPhpExtensionDirectory...");

        $cmds = array_merge(
            $provider->extractPackageCommands($currentPhpExtensionDirectory, $targetFilePath),
            $provider->postExtractPackageCommands($currentPhpExtensionDirectory, $targetFilePath)
        );

        foreach ($cmds as $cmd) {
            $this->logger->debug($cmd);
            Utils::system($cmd);
        }

        return $extensionDir;
    }

    public function knownReleases(Provider $provider)
    {
        $url = $provider->buildKnownReleasesUrl();
        $file = DownloadFactory::getInstance($this->logger, $this->options)->download($url);
        $info = file_get_contents($file);

        return $provider->parseKnownReleasesResponse($info);
    }

    public function renameSourceDirectory(Extension $extension)
    {
        $currentPhpExtensionDirectory = Config::getBuildDir() . '/' . Config::getCurrentPhpName() . '/ext';
        $extName = $extension->getExtensionName();
        $name = $extension->getName();
        $extensionDir = $currentPhpExtensionDirectory . DIRECTORY_SEPARATOR . $extName;
        $extensionExtractDir = $currentPhpExtensionDirectory . DIRECTORY_SEPARATOR . $name;

        if ($name != $extName) {
            $this->logger->info("===> Rename source directory to $extensionDir...");

            $cmds = ["rm -rf $extensionDir", "mv $extensionExtractDir $extensionDir"];

            foreach ($cmds as $cmd) {
                $this->logger->debug($cmd);
                Utils::system($cmd);
            }

            // replace source directory to new source directory
            $sourceDir = str_replace($extensionExtractDir, $extensionDir, $extension->getSourceDirectory());
            $extension->setSourceDirectory($sourceDir);
            $extension->setName($extName);
        }
    }
}
