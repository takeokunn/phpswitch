<?php

namespace PhpSwitch\Command;

/*
 * @codeCoverageIgnore
 */

class CdCommand extends VirtualCommand
{
    public function brief()
    {
        return 'Change to directories';
    }

    public function arguments($args)
    {
        $args->add('directory')
            ->isa('string')
            ->validValues(explode('|', 'var|etc|build|dist'))
            ;
    }

    public function usage()
    {
        return 'phpswitch cd [var|etc|build|dist]';
    }
}
