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
     * @var string
     */
    private $package;

    /**
     * @param string $package
     */
    public function __construct($package)
    {
        $this->package = $package;
    }

    /**
     * {@inheritDoc}
     */
    public function findPrefix()
    {
        return Utils::getPkgConfigPrefix($this->package);
    }
}
