<?php

namespace PhpSwitch\Command;

/**
 * @codeCoverageIgnore
 */
class SystemOffCommand extends VirtualCommand
{
    public function brief()
    {
        return 'Use the currently effective PHP binary internally';
    }
}
