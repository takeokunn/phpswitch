<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;

/**
 * @codeCoverageIgnore
 */
class MigratedCommand extends Command
{
    public function brief()
    {
        return 'This command is migrated';
    }

    public function execute()
    {
        echo <<<HELP
- `phpswitch install-ext` command is now moved to `phpswitch ext install`
- `phpswitch enable` command is now moved to `phpswitch ext enable`
HELP;
    }
}
