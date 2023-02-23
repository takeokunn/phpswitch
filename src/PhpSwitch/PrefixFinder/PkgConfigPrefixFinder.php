<?php

namespace PhpSwitch\PrefixFinder;

use PhpSwitch\PrefixFinder;
use PhpSwitch\Utils;

/**
 * The strategy of finding prefix using pkg-config.
 */
final class PkgConfigPrefixFinder implements PrefixFinder
{
    /**
     * @param string $package
     */
    public function __construct(private $package)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function findPrefix(): ?string
    {
        return Utils::getPkgConfigPrefix($this->package);
    }
}
