<?php

namespace PhpSwitch\Command\ExtensionCommand;

use Exception;
use PhpSwitch\Config;
use PhpSwitch\Extension\Extension;
use PhpSwitch\Extension\ExtensionFactory;
use PhpSwitch\Extension\PeclExtension;

class ShowCommand extends BaseCommand
{
    public function usage()
    {
        return 'phpswitch [-dv, -r] ext show [extension name]';
    }

    public function brief()
    {
        return 'Show information of a PHP extension';
    }

    public function options($opts)
    {
        $opts->add('download', 'Download the extension source if extension not found.');
    }

    public function arguments($args)
    {
        $args->add('extension')
            ->suggestions(function () {
                $extdir = Config::getBuildDir() . '/' . Config::getCurrentPhpName() . '/ext';

                return array_filter(
                    scandir($extdir),
                    fn($d) => $d != '.' && $d != '..' && is_dir($extdir . DIRECTORY_SEPARATOR . $d)
                );
            });
    }

    public function describeExtension(Extension $extension)
    {
        $info = ['Name' => $extension->getExtensionName(), 'Source Directory' => $extension->getSourceDirectory(), 'Config' => $extension->getConfigM4Path(), 'INI File' => $extension->getConfigFilePath(), 'Extension' => ($extension instanceof PeclExtension) ? 'Pecl' : 'Core', 'Zend' => $extension->isZend() ? 'yes' : 'no', 'Loaded' => (extension_loaded($extension->getExtensionName())
            ? $this->formatter->format('yes', 'green')
            : $this->formatter->format('no', 'red'))];

        foreach ($info as $label => $val) {
            $this->logger->writeln(sprintf('%20s: %s', $label, $val));
        }

        $options = $extension->getConfigureOptions();
        if (!empty($options)) {
            $this->logger->newline();
            $this->logger->writeln(sprintf('%20s: ', 'Configure Options'));
            $this->logger->newline();
            foreach ($options as $option) {
                $this->logger->writeln(sprintf(
                    '        %-32s %s',
                    $option->option . ($option->valueHint ? '[=' . $option->valueHint . ']' : ''),
                    $option->desc
                ));
                $this->logger->newline();
            }
        }
    }

    public function execute($extensionName)
    {
        $ext = ExtensionFactory::lookup($extensionName);

        if (!$ext) {
            $ext = ExtensionFactory::lookupRecursive($extensionName);
        }

        // Extension not found, use pecl to download it.
        if (!$ext && $this->options->{'download'}) {
            $extensionList = new ExtensionList();
            // initial local list
            $extensionList->initLocalExtensionList($this->logger, $this->options);

            $hosting = $extensionList->exists($extensionName);

            $extensionDownloader = new ExtensionDownloader($this->logger, $this->options);
            $extDir = $extensionDownloader->download($hosting, 'latest');
            // Reload the extension
            $ext = ExtensionFactory::lookupRecursive($extensionName, [$extDir]);
        }
        if (!$ext) {
            throw new Exception("$extensionName extension not found.");
        }
        $this->describeExtension($ext);
    }
}
