<?php

namespace PhpSwitch\Tasks;

use PhpSwitch\Buildable;
use PhpSwitch\Utils;

/**
 * Task to run `make clean`.
 */
class MakeTask extends BaseTask
{
    private $buildLogPath;
    private bool $isQuiet = false;

    public function run(Buildable $buildable)
    {
        return $this->make($buildable->getSourceDirectory(), 'all', $buildable);
    }

    public function install(Buildable $buildable)
    {
        return $this->make($buildable->getSourceDirectory(), 'install', $buildable);
    }

    public function clean(Buildable $buildable)
    {
        return $this->make($buildable->getSourceDirectory(), 'clean', $buildable);
    }

    public function setBuildLogPath($buildLogPath)
    {
        $this->buildLogPath = $buildLogPath;
    }

    public function setQuiet()
    {
        $this->isQuiet = true;
    }

    public function isQuiet()
    {
        return $this->isQuiet;
    }

    private function isGNUMake($bin)
    {
        return preg_match('/GNU Make/', shell_exec("$bin --version"));
    }


    /**
     * @param Buildable $build can be PeclExtension or Build object.
     */
    private function make($path, $target = 'all', $build = null)
    {
        if (!file_exists($path . DIRECTORY_SEPARATOR . 'Makefile')) {
            $this->logger->error("Makefile not found in path $path");

            return false;
        }

        // FreeBSD make doesn't support --quiet option
        // We should prefer GNU make instead of BSD make.
        // @see https://github.com/phpbrew/phpbrew/issues/529
        $gmake = Utils::findBin('gmake');
        $make = null;
        if (!$gmake) {
            $make = Utils::findBin('make');
            if ($make && $this->isGNUMake($make)) {
                $gmake = $make;
            }
        }

        // Prefer 'gmake' rather than 'make'
        $cmd = [$gmake ?: $make, '-C', escapeshellarg((string) $path)];

        if ($this->isQuiet()) {
            if ($gmake) {
                $cmd[] = '--quiet';
            } else {
                // make may be a link to gmake, we should prevent that.
                // append '-Q' only when we're really sure it is BSD make.
                if (php_uname('s') === 'FreeBSD') {
                    $cmd[] = '-Q';
                }
            }
        }

        $cmd[] = escapeshellarg((string) $target);
        if (!$this->logger->isDebug() && $this->buildLogPath) {
            $cmd[] = ' >> ' . escapeshellarg((string) $this->buildLogPath) . ' 2>&1';
        }

        $this->logger->info("===> Running make $target: " . implode(' ', $cmd));

        return Utils::system($cmd, $this->logger, $build) === 0;
    }
}
