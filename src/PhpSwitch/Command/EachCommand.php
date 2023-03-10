<?php

namespace PhpSwitch\Command;

class EachCommand extends VirtualCommand
{
    public function brief()
    {
        return 'Iterate and run a given shell command over all php versions managed by phpswitch.';
    }
}
