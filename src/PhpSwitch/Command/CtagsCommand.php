<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;
use PhpSwitch\BuildFinder;
use PhpSwitch\CommandBuilder;
use PhpSwitch\Config;

class CtagsCommand extends Command
{
    public function brief()
    {
        return 'Run ctags at current php source dir for extension development.';
    }

    public function arguments($args)
    {
        $args->add('PHP build')
            ->validValues(fn() => BuildFinder::findInstalledBuilds())
            ;
    }

    public function execute($versionName = null)
    {
        $args = func_get_args();
        array_shift($args);

        if ($versionName) {
            $sourceDir = Config::getBuildDir() . DIRECTORY_SEPARATOR . $versionName;
        } else {
            if (!getenv('PHPSWITCH_PHP')) {
                $this->logger->error(<<<EOF
Error: PHPSWITCH_PHP environment variable is not defined.
  This command requires you specify a PHP version from your build list.
  And it looks like you haven't switched to a version from the builds that were built with phpswitch.
Suggestion: Please install at least one PHP with your preferred version and switch to it.
EOF
                );

                return;
            }
            $sourceDir = Config::getCurrentBuildDir();
        }
        if (!file_exists($sourceDir)) {
            $this->logger->error("$sourceDir does not exist.");

            return;
        }
        $this->logger->info('Scanning ' . $sourceDir);

        $commandBuilder = new CommandBuilder('ctags');
        $commandBuilder->arg('-R');
        $commandBuilder->arg('-a');
        $commandBuilder->arg('-h');
        $commandBuilder->arg('.c.h.cpp');

        $commandBuilder->arg($sourceDir . DIRECTORY_SEPARATOR . 'main');
        $commandBuilder->arg($sourceDir . DIRECTORY_SEPARATOR . 'ext');
        $commandBuilder->arg($sourceDir . DIRECTORY_SEPARATOR . 'Zend');

        foreach ($args as $arg) {
            $commandBuilder->arg($arg);
        }

        $this->logger->debug($commandBuilder->__toString());
        $commandBuilder->execute();

        $this->logger->info('Done');
    }
}
