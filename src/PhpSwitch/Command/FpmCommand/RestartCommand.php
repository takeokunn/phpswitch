<?php

namespace PhpSwitch\Command\FpmCommand;

use PhpSwitch\Command\VirtualCommand;

class RestartCommand extends VirtualCommand
{
    public function brief()
    {
        return 'Restart FPM server';
    }
}
