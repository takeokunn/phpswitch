<?php

declare(strict_types=1);

namespace PhpSwitch\SymfonyCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\{ InputOption, InputInterface };
use PhpSwitch\{ Config, Utils };

final class ConfigCommand extends Command
{
    protected static $defaultName = 'config';

    protected function configure(): void
    {
        $this
            ->setDescription('Edit your current php.ini in your favorite $EDITOR')
            ->addOption('sapi', 's', InputOption::VALUE_OPTIONAL, 'Edit php.ini for SAPI name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sapi = $input->getOption('sapi');
        $file = Config::getVersionEtcPath(Config::getCurrentPhpName()) . '/' . $sapi . '/php.ini';

        return Utils::editor($file);
    }
}
