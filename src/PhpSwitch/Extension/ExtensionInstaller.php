<?php

namespace PhpSwitch\Extension;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;
use PhpSwitch\Config;
use PhpSwitch\Tasks\MakeTask;
use PhpSwitch\Utils;

class ExtensionInstaller
{
    public $logger;

    public $options;

    public function __construct(Logger $logger, OptionResult $optionResult = null)
    {
        $this->logger = $logger;
        $this->options = $optionResult ?: new OptionResult();
    }

    public function install(Extension $extension, array $configureOptions = [])
    {
        $path = $extension->getSourceDirectory();
        $pwd = getcwd();
        $buildLogPath = $path . DIRECTORY_SEPARATOR . 'build.log';
        $make = new MakeTask($this->logger, $this->options);

        $make->setBuildLogPath($buildLogPath);

        $this->logger->info("Log stored at: $buildLogPath");

        $this->logger->info("Changing directory to $path");
        chdir($path);

        if (!$this->options->{'no-clean'} && $extension->isBuildable()) {
            $clean = new MakeTask($this->logger, $this->options);
            $clean->setQuiet();
            $clean->clean($extension);
        }

        if ($extension->getConfigM4File() !== 'config.m4' && !file_exists($path . DIRECTORY_SEPARATOR . 'config.m4')) {
            symlink($extension->getConfigM4File(), $path . DIRECTORY_SEPARATOR . 'config.m4');
        }

        // If the php version is specified, we should get phpize with the correct version.
        $this->logger->info('===> Phpize...');
        Utils::system("phpize > $buildLogPath 2>&1", $this->logger);

        $this->logger->info('===> Configuring...');

        $phpConfig = Config::getCurrentPhpConfigBin();
        if (file_exists($phpConfig)) {
            $this->logger->debug("Appending argument: --with-php-config=$phpConfig");
            $configureOptions[] = '--with-php-config=' . $phpConfig;
        }

        $cmd = './configure ' . implode(' ', array_map('escapeshellarg', $configureOptions));

        if (!$this->logger->isDebug()) {
            $cmd .= ' >> ' . escapeshellarg($buildLogPath) . ' 2>&1';
        }

        Utils::system($cmd, $this->logger);

        $this->logger->info('===> Building...');

        if ($this->logger->isDebug()) {
            passthru('make');
        } else {
            $make->run($extension);
        }

        $this->logger->info('===> Installing...');

        // This function is disabled when PHP is running in safe mode.
        if ($this->logger->isDebug()) {
            passthru('make install');
        } else {
            $make->install($extension);
        }

        // TODO: use getSharedLibraryPath()
        $this->logger->debug('Installed extension library: ' . $extension->getSharedLibraryPath());

        // Try to find the installed path by pattern
        // Installing shared extensions: /Users/c9s/.phpswitch/php/php-5.4.10/lib/php/extensions/debug-non-zts-20100525/
        chdir($pwd);
        $this->logger->info('===> Extension is installed.');
    }
}
