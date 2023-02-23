<?php

declare(strict_types=1);

namespace PhpSwitch\Tests\BuildSettings;

use PHPUnit\Framework\TestCase;
use PhpSwitch\BuildSettings\BuildSettings;

final class BuildSettingsTest extends TestCase
{
    public function testConstructorWithEnabledVariants(): void
    {
        $buildSettings = new BuildSettings(['enabled_variants' => ['sqlite' => null]]);
        $this->assertTrue($buildSettings->isEnabledVariant('sqlite'));
    }

    public function testConstructorWithDisabledVariants(): void
    {
        $buildSettings = new BuildSettings(['disabled_variants' => ['sqlite' => true]]);
        $this->assertFalse($buildSettings->isEnabledVariant('sqlite'));
    }

    public function testToArray(): void
    {
        $enabledVariants = ['sqlite' => null, 'curl' => 'yes'];
        $disabledVariants = ['dom' => null];
        $extraOptions = [];

        $expected = [
            'enabled_variants' => $enabledVariants,
            'disabled_variants' => $disabledVariants,
            'extra_options' => $extraOptions,
        ];

        $buildSettings = new BuildSettings($expected);
        $this->assertEquals($expected, $buildSettings->toArray());
    }

    public function testEnableVariant(): void
    {
        $buildSettings = new BuildSettings();
        $buildSettings->enableVariant('curl');

        $this->assertTrue($buildSettings->isEnabledVariant('curl'));
    }

    public function testEnableVariants(): void
    {
        $variants = ['sqlite' => null, 'curl' => 'yes', 'dom' => null];

        $buildSettings = new BuildSettings();
        $buildSettings->enableVariants($variants);

        $this->assertEquals($variants, $buildSettings->getEnabledVariants());
    }

    public function testDisableVariants(): void
    {
        $variants = ['sqlite' => null, 'curl' => 'yes', 'dom' => null];

        $buildSettings = new BuildSettings();
        $buildSettings->disableVariants($variants);

        $expected = ['sqlite' => null, 'curl' => null, 'dom' => null];
        $this->assertEquals($expected, $buildSettings->getDisabledVariants());
    }

    public function testIsEnabledVariant(): void
    {
        $buildSettings = new BuildSettings();
        $buildSettings->enableVariant('sqlite');
        $buildSettings->disableVariant('curl');

        $this->assertTrue($buildSettings->isEnabledVariant('sqlite'));
        $this->assertFalse($buildSettings->isEnabledVariant('curl'));
    }

    public function testRemoveVariant(): void
    {
        $buildSettings = new BuildSettings();
        $buildSettings->enableVariant('sqlite');

        $this->assertTrue($buildSettings->isEnabledVariant('sqlite'));

        $buildSettings->removeVariant('sqlite');
        $this->assertFalse($buildSettings->isEnabledVariant('sqlite'));
    }

    public function testResolveVariants(): void
    {
        $buildSettings = new BuildSettings();
        $buildSettings->enableVariant('sqlite');
        $buildSettings->disableVariant('sqlite');
        $buildSettings->resolveVariants();

        $this->assertEquals([], $buildSettings->getEnabledVariants());
    }

    public function testGetVariants(): void
    {
        $buildSettings = new BuildSettings();
        $buildSettings->enableVariant('sqlite');
        $buildSettings->enableVariant('curl');
        $buildSettings->disableVariant('dom');

        $expected = ['sqlite' => null, 'curl' => null];
        $this->assertEquals($expected, $buildSettings->getEnabledVariants());
    }

    public function testGetDisabledVariants(): void
    {
        $buildSettings = new BuildSettings();
        $buildSettings->enableVariant('sqlite');
        $buildSettings->enableVariant('curl');
        $buildSettings->disableVariant('dom');

        $expected = ['dom' => null];
        $this->assertEquals($expected, $buildSettings->getDisabledVariants());
    }
}
