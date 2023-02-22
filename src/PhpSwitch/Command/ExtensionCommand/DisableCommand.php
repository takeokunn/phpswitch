<?php

namespace PhpSwitch\Command\ExtensionCommand;

use PhpSwitch\Config;
use PhpSwitch\Extension\ExtensionManager;

class DisableCommand extends BaseCommand
{
    public function usage()
    {
        return 'phpswitch ext disable [extension name]';
    }

    public function brief()
    {
        return 'Disable PHP extension';
    }

    public function options($opts)
    {
        $opts->add('s|sapi:=string', 'Disable extension for SAPI name.');
    }

    public function arguments($args)
    {
        $args->add('extensions')
            ->suggestions(function () {
                $extension = '.ini';

                return array_map(function ($path) use ($extension) {
                    return basename($path, $extension);
                }, glob(Config::getCurrentPhpDir() . "/var/db/*{$extension}"));
            });
    }

    public function execute($extensionName)
    {
        $sapi = null;
        if ($this->options->sapi) {
            $sapi = $this->options->sapi;
        }
        $manager = new ExtensionManager($this->logger);
        $manager->disable($extensionName, $sapi);
    }
}
