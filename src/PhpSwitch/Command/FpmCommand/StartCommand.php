<?php

namespace PhpSwitch\Command\FpmCommand;

use PhpSwitch\Command\VirtualCommand;

class StartCommand extends VirtualCommand
{
    public function brief()
    {
        return 'Start FPM server';
    }
}
