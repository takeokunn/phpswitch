<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;

class ListIniCommand extends Command
{
    public function brief()
    {
        return 'List loaded ini config files.';
    }

    public function execute()
    {
        $this->logger->warn(
            'The list-ini command is deprecated and will be removed in the future.' . PHP_EOL
            . 'Please use `php --ini` instead.'
        );

        if ($filelist = php_ini_scanned_files()) {
            echo "Loaded ini files:" . PHP_EOL;
            if (strlen($filelist) > 0) {
                $files = explode(',', $filelist);
                foreach ($files as $file) {
                    echo ' - ' . trim($file) . PHP_EOL;
                }
            }
        }
    }
}
