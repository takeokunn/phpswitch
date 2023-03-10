<?php

namespace PhpSwitch\Command;

use Exception;
use GetOptionKit\OptionCollection;
use PhpSwitch\Command\ExtensionCommand\BaseCommand;
use PhpSwitch\Config;
use PhpSwitch\Extension\Extension;
use PhpSwitch\Extension\ExtensionFactory;
use PhpSwitch\Extension\M4Extension;

class ExtensionCommand extends BaseCommand
{
    public function aliases()
    {
        return ['ext'];
    }

    public function usage()
    {
        return 'phpswitch ext [install|enable|disable|config|known]';
    }

    public function brief()
    {
        return 'List extensions or execute extension subcommands';
    }

    public function init()
    {
        $this->command('enable');
        $this->command('install');
        $this->command('disable');
        $this->command('config');
        $this->command('clean');
        $this->command('show');
        $this->command('known');
    }

    /**
     * @param OptionCollection $opts
     */
    public function options($opts)
    {
        $opts->add('so|show-options', 'Show extension configure options');
        $opts->add('sp|show-path', 'Show extension config.m4 path');
    }

    public function describeExtension(Extension $extension)
    {
        $this->logger->write(sprintf(
            ' [%s] %-12s %-12s',
            extension_loaded($extension->getExtensionName()) ? '*' : ' ',
            $extension->getExtensionName(),
            phpversion($extension->getExtensionName())
        ));

        if ($this->options->{'show-path'}) {
            $this->logger->write(sprintf(' from %s', $extension->getConfigM4Path()));
        }
        $this->logger->newline();

        // $this->logger->writeln(sprintf('config: %s', $ext->getConfigFilePath()));

        if ($this->options->{'show-options'}) {
            $padding = '     ';
            if ($extension instanceof M4Extension) {
                $options = $extension->getConfigureOptions();
                if (!empty($options)) {
                    $this->logger->info($padding . 'Configure options:');
                    foreach ($options as $option) {
                        $this->logger->info($padding . '  ' . sprintf(
                            '%-32s %s',
                            $option->option . ($option->valueHint ? '[=' . $option->valueHint . ']' : ''),
                            $option->desc
                        ));
                    }
                }
            }
        }
    }

    public function execute()
    {
        $currentBuildDir = Config::getCurrentBuildDir();
        $extDir = $currentBuildDir . DIRECTORY_SEPARATOR . 'ext';

        // list for extensions which are not enabled
        $extensions = [];
        $extensionNames = [];

        // some extension source not in root directory
        $lookupDirectories = ['', 'ext', 'extension'];

        if (file_exists($extDir) && is_dir($extDir)) {
            $this->logger->debug("Scanning $extDir...");
            foreach (scandir($extDir) as $extName) {
                if ($extName == '.' || $extName == '..') {
                    continue;
                }
                $dir = $extDir . DIRECTORY_SEPARATOR . $extName;
                foreach ($lookupDirectories as $lookupDirectory) {
                    $extensionDir = $dir . (empty($lookupDirectory) ? '' : DIRECTORY_SEPARATOR . $lookupDirectory);
                    if ($m4files = ExtensionFactory::configM4Exists($extensionDir)) {
                        $this->logger->debug("Loading extension information $extName from $extensionDir");

                        foreach ($m4files as $m4file) {
                            try {
                                $ext = ExtensionFactory::createM4Extension($extName, $m4file);
                                // $ext = ExtensionFactory::createFromDirectory($extName, $dir);
                                $extensions[$ext->getExtensionName()] = $ext;
                                $extensionNames[] = $extName;
                                break;
                            } catch (Exception) {
                            }
                        }

                        break;
                    }
                }
            }
        }

        $this->logger->info('Loaded extensions:');
        foreach ($extensions as $extName => $ext) {
            if (extension_loaded($extName)) {
                $this->describeExtension($ext);
            }
        }

        $this->logger->info('Available local extensions:');
        foreach ($extensions as $extName => $ext) {
            if (extension_loaded($extName)) {
                continue;
            }
            $this->describeExtension($ext);
        }
    }
}
