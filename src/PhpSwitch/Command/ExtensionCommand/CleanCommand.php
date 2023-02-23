<?php

namespace PhpSwitch\Command\ExtensionCommand;

use PhpSwitch\Config;
use PhpSwitch\Extension\ExtensionFactory;
use PhpSwitch\Extension\ExtensionManager;

class CleanCommand extends BaseCommand
{
    public function brief()
    {
        return 'Clean up the compiled objects in the extension source directory.';
    }

    public function options($opts)
    {
        $opts->add('p|purge', 'Remove all the source files.');
    }

    public function arguments($args)
    {
        $args->add('extensions')
            ->suggestions(function () {
                $extdir = Config::getBuildDir() . '/' . Config::getCurrentPhpName() . '/ext';

                return array_filter(
                    scandir($extdir),
                    fn($d) => $d != '.' && $d != '..' && is_dir($extdir . DIRECTORY_SEPARATOR . $d)
                );
            });
    }

    public function execute($extensionName)
    {
        if ($ext = ExtensionFactory::lookup($extensionName)) {
            $this->logger->info("Cleaning $extensionName...");
            $extensionManager = new ExtensionManager($this->logger);

            if ($this->options->purge) {
                $extensionManager->purgeExtension($ext);
            } else {
                $extensionManager->cleanExtension($ext);
            }
            $this->logger->info('Done');
        }
    }
}
