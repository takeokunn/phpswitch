<?php

namespace PhpSwitch\PrefixFinder;

use PhpSwitch\PrefixFinder;
use PhpSwitch\Utils;

/**
 * The strategy of finding prefix using library file path.
 */
final class LibPrefixFinder implements PrefixFinder
{
    /**
     * @param string $path
     */
    public function __construct(private $path)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function findPrefix(): ?string
    {
        return Utils::findLibPrefix($this->path);
    }
}
