<?php

declare(strict_types=1);

namespace PhpSwitch\Tests\BuildSettings;

use PHPUnit\Framework\TestCase;
use PhpSwitch\BuildSettings\BuildSettings;

final class BuildSettingsTest extends TestCase
{
    ///////////////////////////////////////////////////////////////////////////
    //                              constructor                              //
    ///////////////////////////////////////////////////////////////////////////

    public function testConstructorWithEnabledVariants(): void
    {
        $buildSettings = new BuildSettings(['enabled_variants' => ['sqlite' => null]]);
        $this->assertTrue($buildSettings->isEnabledVariant('sqlite'));
    }

    public function testConstructorWithDisabledVariants(): void
    {
        $buildSettings = new BuildSettings(['disabled_variants' => ['sqlite']]);
        $this->assertFalse($buildSettings->isEnabledVariant('sqlite'));
    }

    public function testToArray(): void
    {
        $enabled_variants = ['sqlite' => null, 'curl' => '--with-curl'];
        $disabled_variants = ['dom'];
        $extra_options = ['--with-xml'];

        $expected = [
            'enabled_variants' => $enabled_variants,
            'disabled_variants' => $disabled_variants,
            'extra_options' => $extra_options,
        ];

        $buildSettings = new BuildSettings($expected);
        $this->assertEquals($expected, $buildSettings->toArray());
    }

    ///////////////////////////////////////////////////////////////////////////
    //                             getter/setter                             //
    ///////////////////////////////////////////////////////////////////////////

    // for enabled_variant

    public function testGetEnabledVariants(): void
    {
        $expected = ['sqlite' => null, 'curl' => null];
        $buildSettings = new BuildSettings(['enabled_variants' => $expected]);

        $this->assertEquals($expected, $buildSettings->getEnabledVariants());
    }

    public function testSetEnableVariants(): void
    {
        $expected = ['sqlite' => null, 'curl' => null, 'dom' => null];

        $buildSettings = new BuildSettings();
        $buildSettings->setEnableVariants($expected);

        $this->assertEquals($expected, $buildSettings->getEnabledVariants());
    }

    public function testSetEnableVariant(): void
    {
        $buildSettings = new BuildSettings();
        $buildSettings->setEnableVariant('curl', null);

        $this->assertEquals(['curl' => null], $buildSettings->getEnabledVariants());
    }

    public function testIsEnabledVariant(): void
    {
        $buildSettings = new BuildSettings(['enabled_variants' => ['sqlite' => null]]);

        $this->assertTrue($buildSettings->isEnabledVariant('sqlite'));
        $this->assertFalse($buildSettings->isEnabledVariant('curl'));
    }

    // for disabled_variant

    public function testGetDisabledVariants(): void
    {
        $expected = ['sqlite', 'curl'];
        $buildSettings = new BuildSettings(['disabled_variants' => $expected]);

        $this->assertEquals($expected, $buildSettings->getDisabledVariants());
    }

    public function testSetDisableVariants(): void
    {
        $expected = ['sqlite', 'curl', 'dom'];

        $buildSettings = new BuildSettings();
        $buildSettings->setDisableVariants($expected);

        $this->assertEquals($expected, $buildSettings->getDisabledVariants());
    }

    public function testSetDisableVariant(): void
    {
        $expected = 'curl';

        $buildSettings = new BuildSettings();
        $buildSettings->setDisableVariant($expected);

        $this->assertEquals([$expected], $buildSettings->getDisabledVariants());
    }

    public function testIsDisableddVariant(): void
    {
        $buildSettings = new BuildSettings(['disabled_variants' => ['sqlite']]);

        $this->assertTrue($buildSettings->isDisabledVariant('sqlite'));
        $this->assertFalse($buildSettings->isDisabledVariant('curl'));
    }

    // for extra_options

    public function testGetExtraOptionsVariants(): void
    {
        $expected = ['sqlite', 'curl'];
        $buildSettings = new BuildSettings(['extra_options' => $expected]);

        $this->assertEquals($expected, $buildSettings->getExtraOptions());
    }

    public function testSetExtraOptionsVariants(): void
    {
        $expected = ['sqlite', 'curl', 'dom'];

        $buildSettings = new BuildSettings();
        $buildSettings->setExtraOptions($expected);

        $this->assertEquals($expected, $buildSettings->getExtraOptions());
    }

    ///////////////////////////////////////////////////////////////////////////
    //                                utility                                //
    ///////////////////////////////////////////////////////////////////////////

    public function testResolveVariants(): void
    {
        $buildSettings = new BuildSettings();
        $buildSettings->setEnableVariant('dom', null);
        $buildSettings->setEnableVariant('curl', null);
        $buildSettings->setDisableVariant('curl');
        $buildSettings->resolveVariants();

        $this->assertTrue($buildSettings->isEnabledVariant('dom'));
        $this->assertFalse($buildSettings->isEnabledVariant('curl'));
    }
}
