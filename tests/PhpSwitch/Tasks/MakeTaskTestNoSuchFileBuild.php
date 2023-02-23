<?php

declare(strict_types=1);

namespace PhpSwitch\Tests\Tasks;

use PhpSwitch\Buildable;

final class MakeTaskTestNoSuchFileBuild implements Buildable
{
    public function getSourceDirectory(): string
    {
        return __DIR__ . '/../../fixtures/make/dummy';
    }

    public function getBuildLogPath(): string
    {
        return '';
    }

    public function isBuildable(): bool
    {
        return false;
    }
}
