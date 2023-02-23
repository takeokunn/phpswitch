<?php

namespace PhpSwitch\PrefixFinder;

use PhpSwitch\PrefixFinder;
use PhpSwitch\Utils;

/**
 * The strategy of finding prefix by an executable file.
 */
final class ExecutablePrefixFinder implements PrefixFinder
{
    /**
     * @param string $name Executable name
     */
    public function __construct(private $name)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function findPrefix()
    {
        $bin = Utils::findBin($this->name);

        if ($bin === null) {
            return null;
        }

        return dirname($bin);
    }
}
