<?php

namespace PhpSwitch\PatchKit;

use CLIFramework\Logger;
use PhpSwitch\Buildable;

interface PatchRule
{
    public function apply(Buildable $build, Logger $logger);

    public function backup(Buildable $build, Logger $logger);
}
