<?php

namespace PhpSwitch\Tasks;

use PhpSwitch\Build;
use PhpSwitch\Config;

class PrepareDirectoryTask extends BaseTask
{
    public function run(Build $build = null)
    {
        $dirs = [];
        $dirs[] = Config::getRoot();
        $dirs[] = Config::getHome();
        $dirs[] = Config::getBuildDir();
        $dirs[] = Config::getDistFileDir();

        if ($build) {
            $dirs[] = Config::getInstallPrefix() . DIRECTORY_SEPARATOR . $build->getName();
        }
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                $this->logger->debug("Creating directory $dir");
                mkdir($dir, 0755, true);
            }
        }
    }
}
