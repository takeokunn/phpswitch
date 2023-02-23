<?php

namespace PhpSwitch\PrefixFinder;

use PhpSwitch\PrefixFinder;
use PhpSwitch\Utils;

/**
 * The strategy of finding prefix using Homebrew.
 */
final class BrewPrefixFinder implements PrefixFinder
{
    /**
     * @param string $formula Homebrew formula
     */
    public function __construct(private $formula)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function findPrefix(): ?string
    {
        $brew = Utils::findBin('brew');

        if ($brew === null) {
            return null;
        }

        $output = $this->execLine(
            sprintf('%s --prefix %s', escapeshellcmd($brew), escapeshellarg($this->formula))
        );

        if ($output === null) {
            printf('Homebrew formula "%s" not found.' . PHP_EOL, $this->formula);

            return null;
        }

        if (!file_exists($output)) {
            printf('Homebrew prefix "%s" does not exist.' . PHP_EOL, $output);

            return null;
        }

        return $output;
    }

    private function execLine($command)
    {
        $output = [];
        exec($command, $output, $retval);

        if ($retval === 0) {
            $output = array_filter($output);

            return end($output);
        }

        return null;
    }
}
