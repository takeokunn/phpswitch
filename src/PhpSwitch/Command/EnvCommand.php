<?php

namespace PhpSwitch\Command;

use CLIFramework\Command as BaseCommand;
use PhpSwitch\BuildFinder;
use PhpSwitch\Config;

class EnvCommand extends BaseCommand
{
    public function brief()
    {
        return 'Export environment variables';
    }

    public function arguments($args)
    {
        $args->add('PHP build')
            ->optional()
            ->validValues(function () {
                return BuildFinder::findInstalledBuilds();
            })
            ;
    }

    public function execute($buildName = null)
    {
        // get current version
        if (!$buildName) {
            $buildName = getenv('PHPSWITCH_PHP');
        }

        $this->export('PHPSWITCH_ROOT', Config::getRoot());
        $this->export('PHPSWITCH_HOME', Config::getHome());

        $this->replicate('PHPSWITCH_LOOKUP_PREFIX');

        if ($buildName !== false) {
            $targetPhpBinPath = Config::getVersionBinPath($buildName);

            // checking php version existence
            if (is_dir($targetPhpBinPath)) {
                $this->export('PHPSWITCH_PHP', $buildName);
                $this->export('PHPSWITCH_PATH', $targetPhpBinPath);
            }
        }

        $this->replicate('PHPSWITCH_SYSTEM_PHP');

        $this->logger->writeln('# Run this command to configure your shell:');
        $this->logger->writeln('# eval "$(phpswitch env)"');
    }

    private function export($varName, $value)
    {
        $this->logger->writeln(sprintf('export %s=%s', $varName, $value));
    }

    private function replicate($varName)
    {
        $value = getenv($varName);

        if ($value !== false && $value !== '') {
            $this->export($varName, $value);
        }
    }
}
