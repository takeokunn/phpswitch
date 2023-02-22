<?php

namespace PhpSwitch\Command\ExtensionCommand;

use PhpSwitch\Config;
use PhpSwitch\Extension\ExtensionFactory;
use PhpSwitch\Utils;

class ConfigCommand extends BaseCommand
{
    public function usage()
    {
        return 'phpswitch ext config [--sapi] [extension name]';
    }

    public function brief()
    {
        return 'Edit extension-specific configuration file';
    }

    public function arguments($args)
    {
        $args->add('extensions')
            ->suggestions(function () {
                return array_map(function ($path) {
                    return basename(basename($path, '.disabled'), '.ini');
                }, glob(Config::getCurrentPhpDir() . '/var/db/*.{ini,disabled}', GLOB_BRACE));
            });
    }

    public function options($opts)
    {
        $opts->add('s|sapi:=string', 'Edit extension for SAPI name.');
    }

    public function execute($extensionName)
    {
        $sapi = null;
        if ($this->options->sapi) {
            $sapi = $this->options->sapi;
        }

        $ext = ExtensionFactory::lookup($extensionName);
        if (!$ext) {
            return $this->error("Extension $extensionName not found.");
        }
        $file = $ext->getConfigFilePath($sapi);
        $this->logger->info("Looking for {$file} file...");
        if (!file_exists($file)) {
            $file .= '.disabled'; // try with ini.disabled file
            $this->logger->info("Looking for {$file} file...");
            if (!file_exists($file)) {
                $this->logger->warn(
                    "Sorry, I can't find the ini file for the requested extension: \"{$extensionName}\"."
                );

                return false;
            }
        }

        return Utils::editor($file) === 0;
    }
}
