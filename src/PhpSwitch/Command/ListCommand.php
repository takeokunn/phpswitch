<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;
use PhpSwitch\BuildFinder;
use PhpSwitch\Config;
use PhpSwitch\VariantParser;

class ListCommand extends Command
{
    public function brief()
    {
        return 'List installed PHPs';
    }

    public function options($opts)
    {
        $opts->add('d|dir', 'Show php directories.');
        $opts->add('v|variants', 'Show used variants.');
    }

    public function execute()
    {
        $builds = BuildFinder::findInstalledBuilds();
        $currentPhpName = Config::getCurrentPhpName();

        if (empty($builds)) {
            return $this->logger->notice('Please install at least one PHP with your preferred version.');
        }

        if ($currentPhpName === false or !in_array($currentPhpName, $builds)) {
            $this->logger->writeln('* (system)');
        }

        foreach ($builds as $build) {
            $versionPrefix = Config::getVersionInstallPrefix($build);

            if ($currentPhpName === $build) {
                $this->logger->writeln(
                    $this->formatter->format(sprintf('* %-15s', $build), 'bold')
                );
            } else {
                $this->logger->writeln(
                    $this->formatter->format(sprintf('  %-15s', $build), 'bold')
                );
            }

            if ($this->options->dir) {
                $this->logger->writeln(sprintf('    Prefix:   %s', $versionPrefix));
            }

            // TODO: use Build class to get the variants
            if ($this->options->variants && file_exists($versionPrefix . DIRECTORY_SEPARATOR . 'phpbrew.variants')) {
                $info = unserialize(file_get_contents($versionPrefix . DIRECTORY_SEPARATOR . 'phpbrew.variants'));
                echo '    Variants: ';
                echo wordwrap((string) VariantParser::revealCommandArguments($info), 75, " \\\n              ");
                echo PHP_EOL;
            }
        }
    }
}
