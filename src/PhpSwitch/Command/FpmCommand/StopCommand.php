<?php

namespace PhpSwitch\Command\FpmCommand;

use PhpSwitch\Command\VirtualCommand;

class StopCommand extends VirtualCommand
{
    public function brief()
    {
        return 'Stop FPM server';
    }
}
