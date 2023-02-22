<?php

namespace PhpSwitch\Command;

use CLIFramework\Command\HelpCommand as BaseHelpCommand;

class HelpCommand extends BaseHelpCommand
{
    public function execute()
    {
        $args = func_get_args();

        if (empty($args)) {
            $headline = <<<EOS

phpswitch

EOS;
            $this->logger->write($this->formatter->format($headline, 'strong_white'));
        }

        // Compatibility: Calling parent::method by call_user_func_array is only supported from 5.3.0
        return call_user_func_array(array($this, 'parent::execute'), func_get_args());
    }
}
