<?php

declare(strict_types=1);

namespace PhpSwitch\Tests\Tasks;

use PhpSwitch\Buildable;

class MakeTaskTestBuild implements Buildable
{
    public function getSourceDirectory(): string
    {
        return __DIR__ . '/../../fixtures/make/';
    }

    public function getBuildLogPath(): string
    {
        return '';
    }

    public function isBuildable(): bool
    {
        return true;
    }
}
