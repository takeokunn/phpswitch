<?php

declare(strict_types=1);

namespace PhpSwitch;

/**
 * A strategy of finding prefix
 */
interface PrefixFinder
{
    /**
     * Returns the found prefix or NULL of it's not found.
     */
    public function findPrefix(): ?string;
}
