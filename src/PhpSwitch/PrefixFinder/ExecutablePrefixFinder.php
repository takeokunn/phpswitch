<?php

declare(strict_types=1);

namespace PhpSwitch\PrefixFinder;

use PhpSwitch\Utils;
use PhpSwitch\PrefixFinder;

/**
 * The strategy of finding prefix by an executable file.
 */
final class ExecutablePrefixFinder implements PrefixFinder
{
    /**
     * @param string $name Executable name
     */
    public function __construct(
        private readonly string $name
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function findPrefix(): ?string
    {
        $bin = Utils::findBin($this->name);
        return isset($bin) ? dirname($bin) : null;
    }
}
