<?php

namespace PhpSwitch\PrefixFinder;

use PhpSwitch\PrefixFinder;
use PhpSwitch\Utils;

/**
 * The strategy of finding prefix using include paths.
 */
final class IncludePrefixFinder implements PrefixFinder
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
        return Utils::findIncludePrefix($this->path);
    }
}
