<?php

declare(strict_types=1);

namespace PhpSwitch\Tests;

use PhpSwitch\Build;
use PHPUnit\Framework\TestCase;

final class BuildTest extends TestCase
{
    public function testBuildAPI()
    {
        $build = new Build('5.3.1');

        $this->assertSame(1, $build->compareVersion('5.3.0'));
        $this->assertSame(1, $build->compareVersion('5.3'));
        $this->assertSame(-1, $build->compareVersion('5.4.0'));
        $this->assertSame(-1, $build->compareVersion('5.4'));
    }

    public function testNeutralVirtualVariant()
    {
        $build = new Build('5.5.0');
        $build->setEnableVariant('neutral', null);
        $build->resolveVariants();

        $this->assertTrue($build->isEnabledVariant('neutral'));
    }
}
