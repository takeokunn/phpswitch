<?php

declare(strict_types=1);

namespace PhpSwitch\Tests;

use PhpSwitch\Utils;
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{
    public function testSupport64bit(): void
    {
        $this->assertIsBool(Utils::support64bit());
    }

    public function testFindbin(): void
    {
        $this->assertNotNull(Utils::findBin('ls'));
    }
}
