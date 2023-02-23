<?php

namespace PhpSwitch\Tasks;

use PhpSwitch\Build;
use PhpSwitch\CommandBuilder;
use PhpSwitch\Config;
use PhpSwitch\ConfigureParameters;
use PhpSwitch\Exception\SystemCommandException;

/**
 * Task to run `make`.
 */
class ConfigureTask extends BaseTask
{
    public function run(Build $build, ConfigureParameters $configureParameters)
    {
        $this->debug('Enabled variants: [' . implode(', ', array_keys($build->getEnabledVariants())) . ']');
        $this->debug('Disabled variants: [' . implode(', ', array_keys($build->getDisabledVariants())) . ']');

        $commandBuilder = new CommandBuilder('./configure');
        $commandBuilder->args($this->renderOptions($configureParameters));

        $buildLogPath = $build->getBuildLogPath();
        if (file_exists($buildLogPath)) {
            $newPath = $buildLogPath . '.' . filemtime($buildLogPath);
            $this->info("Found existing build.log, renaming it to $newPath");
            rename($buildLogPath, $newPath);
        }

        $this->info("===> Configuring {$build->version}...");
        $commandBuilder->setAppendLog(true);
        $commandBuilder->setLogPath($buildLogPath);
        $commandBuilder->setStdout($this->options->{'stdout'});

        if (!$this->options->{'stdout'}) {
            $this->logger->info(PHP_EOL);
            $this->logger->info("Use tail command to see what's going on:");
            $this->logger->info('   $ tail -F ' . escapeshellarg($buildLogPath) . PHP_EOL . PHP_EOL);
        }

        $this->debug($commandBuilder->buildCommand());

        if ($this->options->nice) {
            $commandBuilder->nice($this->options->nice);
        }

        if (!$this->options->dryrun) {
            $code = $commandBuilder->execute($lastline);
            if ($code !== 0) {
                throw new SystemCommandException("Configure failed: $lastline", $build, $buildLogPath);
            }
        }
        $build->setState(Build::STATE_CONFIGURE);
    }

    private function renderOptions(ConfigureParameters $configureParameters)
    {
        $args = [];

        foreach ($configureParameters->getOptions() as $option => $value) {
            $arg = $option;

            if ($value !== null) {
                $arg .= '=' . $value;
            }

            $args[] = $arg;
        }

        $pkgConfigPaths = $configureParameters->getPkgConfigPaths();

        if (count($pkgConfigPaths) > 0) {
            $args[] = 'PKG_CONFIG_PATH=' . implode(PATH_SEPARATOR, $pkgConfigPaths);
        }

        return $args;
    }
}
