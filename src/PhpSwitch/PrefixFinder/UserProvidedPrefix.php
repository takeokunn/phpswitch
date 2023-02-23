<?php

namespace PhpSwitch\PrefixFinder;

use PhpSwitch\PrefixFinder;

/**
 * The strategy of using the user-provided prefix.
 */
final class UserProvidedPrefix implements PrefixFinder
{
    /**
     * @param string|null $prefix User-provided prefix
     */
    public function __construct(private readonly ?string $prefix)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function findPrefix()
    {
        return $this->prefix;
    }
}
