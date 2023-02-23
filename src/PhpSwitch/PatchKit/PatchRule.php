<?php

namespace PhpSwitch\PatchKit;

use CLIFramework\Logger;
use PhpSwitch\Buildable;

interface PatchRule
{
    public function apply(Buildable $buildable, Logger $logger);

    public function backup(Buildable $buildable, Logger $logger);
}
