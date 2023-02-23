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
        $settings = new BuildSettings(['enabled_variants' => ['sqlite']]);
        $this->assertTrue($settings->isEnabledVariant('sqlite'));
    }

    public function testConstructorWithDisabledVariants(): void
    {
        $settings = new BuildSettings(['disabled_variants' => ['sqlite']]);
        $this->assertFalse($settings->isEnabledVariant('sqlite'));
    }

    public function testToArray(): void
    {
        $enabled_variants = ['sqlite', 'curl'];
        $disabled_variants = ['dom'];
        $extra_options = ['--with-xml'];

        $expected = [
            'enabled_variants' => $enabled_variants,
            'disabled_variants' => $disabled_variants,
            'extra_options' => $extra_options,
        ];

        $settings = new BuildSettings($expected);
        $this->assertEquals($expected, $settings->toArray());
    }

    ///////////////////////////////////////////////////////////////////////////
    //                             getter/setter                             //
    ///////////////////////////////////////////////////////////////////////////

    // for enabled_variant

    public function testGetEnabledVariants(): void
    {
        $expected = ['sqlite', 'curl'];
        $settings = new BuildSettings(['enabled_variants' => $expected]);

        $this->assertEquals($expected, $settings->getEnabledVariants());
    }

    public function testSetEnableVariants(): void
    {
        $expected = ['sqlite', 'curl', 'dom'];

        $settings = new BuildSettings();
        $settings->setEnableVariants($expected);

        $this->assertEquals($expected, $settings->getEnabledVariants());
    }

    public function testSetEnableVariant(): void
    {
        $expected = 'curl';

        $settings = new BuildSettings();
        $settings->setEnableVariant($expected);

        $this->assertEquals([$expected], $settings->getEnabledVariants());
    }

    public function testIsEnabledVariant(): void
    {
        $settings = new BuildSettings(['enabled_variants' => ['sqlite']]);

        $this->assertTrue($settings->isEnabledVariant('sqlite'));
        $this->assertFalse($settings->isEnabledVariant('curl'));
    }

    // for disabled_variant

    public function testGetDisabledVariants(): void
    {
        $expected = ['sqlite', 'curl'];
        $settings = new BuildSettings(['disabled_variants' => $expected]);

        $this->assertEquals($expected, $settings->getDisabledVariants());
    }

    public function testSetDisableVariants(): void
    {
        $expected = ['sqlite', 'curl', 'dom'];

        $settings = new BuildSettings();
        $settings->setDisableVariants($expected);

        $this->assertEquals($expected, $settings->getDisabledVariants());
    }

    public function testSetDisableVariant(): void
    {
        $expected = 'curl';

        $settings = new BuildSettings();
        $settings->setDisableVariant($expected);

        $this->assertEquals([$expected], $settings->getDisabledVariants());
    }

    public function testIsDisableddVariant(): void
    {
        $settings = new BuildSettings(['disabled_variants' => ['sqlite']]);

        $this->assertTrue($settings->isDisabledVariant('sqlite'));
        $this->assertFalse($settings->isDisabledVariant('curl'));
    }

    // for extra_options

    public function testGetExtraOptionsVariants(): void
    {
        $expected = ['sqlite', 'curl'];
        $settings = new BuildSettings(['extra_options' => $expected]);

        $this->assertEquals($expected, $settings->getExtraOptions());
    }

    public function testSetExtraOptionsVariants(): void
    {
        $expected = ['sqlite', 'curl', 'dom'];

        $settings = new BuildSettings();
        $settings->setExtraOptions($expected);

        $this->assertEquals($expected, $settings->getExtraOptions());
    }

    ///////////////////////////////////////////////////////////////////////////
    //                                utility                                //
    ///////////////////////////////////////////////////////////////////////////

    public function testResolveVariants(): void
    {
        $settings = new BuildSettings();
        $settings->setEnableVariant('dom');
        $settings->setEnableVariant('curl');
        $settings->setDisableVariant('curl');
        $settings->resolveVariants();

        $this->assertTrue($settings->isEnabledVariant('dom'));
        $this->assertFalse($settings->isEnabledVariant('curl'));
    }
}
