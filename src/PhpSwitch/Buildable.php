<?php

declare(strict_types=1);

namespace PhpSwitch;

interface Buildable
{
    /**
     * @return path return source directory
     */
    public function getSourceDirectory(): string;

    /**
     * @return boolean
     */
    public function isBuildable(): bool;

    /**
     * @return string return build log file path.
     */
    public function getBuildLogPath(): string;
}
