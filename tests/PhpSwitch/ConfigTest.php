<?php

namespace PhpSwitch\Tests;

use PhpSwitch\Config;
use PHPUnit\Framework\TestCase;

/**
 * You should use predefined $PHPSWITCH_HOME and $PHPSWITCH_ROOT (defined
 * in phpunit.xml), because they are used to create directories in
 * Phpswitch\Config class. When you want to set $PHPSWITCH_ROOT, $PHPSWITCH_HOME
 * or $HOME, you should get its value by calling `getenv' function and set
 * the value to the corresponding environment variable.
 * @small
 */
class ConfigTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testGetPhpswitchHomeWhenHOMEIsNotDefined()
    {
        $env = array(
            'PHPSWITCH_HOME' => null,
            'PHPSWITCH_ROOT' => null,
            'HOME'         => null
        );
        $this->withEnv($env, function () {
            Config::getHome();
        });
    }

    public function testGetPhpswitchHomeWhenHOMEIsDefined()
    {
        $env = array(
            'HOME'         => getenv('PHPSWITCH_ROOT'),
            'PHPSWITCH_HOME' => null
        );
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch/.phpswitch', Config::getHome());
        });
    }

    public function testGetPhpswitchHomeWhenPhpswitchHomeIsDefined()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch', Config::getHome());
        });
    }

    public function testGetPhpswitchRootWhenPhpswitchRootIsDefined()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch', Config::getRoot());
        });
    }

    public function testGetPhpswitchRootWhenHOMEIsDefined()
    {
        $env = array(
            'HOME'         => getenv('PHPSWITCH_ROOT'),
            'PHPSWITCH_ROOT' => null
        );
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch/.phpswitch', Config::getRoot());
        });
    }

    public function testGetBuildDir()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch/build', Config::getBuildDir());
        });
    }

    public function testGetDistFileDir()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch/distfiles', Config::getDistFileDir());
        });
    }

    public function testGetTempFileDir()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch/tmp', Config::getTempFileDir());
        });
    }

    public function testGetCurrentPhpName()
    {
        $env = array('PHPSWITCH_PHP' => '5.6.3');
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('5.6.3', Config::getCurrentPhpName());
        });
    }

    public function testGetCurrentBuildDir()
    {
        $env = array('PHPSWITCH_PHP' => '5.6.3');
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch/build/5.6.3', Config::getCurrentBuildDir());
        });
    }

    public function testGetPHPReleaseListPath()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch/php-releases.json', Config::getPHPReleaseListPath());
        });
    }

    public function testGetInstallPrefix()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch/php', Config::getInstallPrefix());
        });
    }

    public function testGetVersionInstallPrefix()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch/php/5.5.1', Config::getVersionInstallPrefix('5.5.1'));
        });
    }

    public function testGetVersionEtcPath()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch/php/5.5.1/etc', Config::getVersionEtcPath('5.5.1'));
        });
    }

    public function testGetVersionBinPath()
    {
        $this->withEnv(array(), function ($self) {
            $self->assertStringEndsWith('.phpswitch/php/5.5.1/bin', Config::getVersionBinPath('5.5.1'));
        });
    }

    public function testGetCurrentPhpConfigBin()
    {
        $env = array(
            'PHPSWITCH_PHP'  => '5.5.1'
        );
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch/php/5.5.1/bin/php-config', Config::getCurrentPhpConfigBin());
        });
    }

    public function testGetCurrentPhpizeBin()
    {
        $env = array(
            'PHPSWITCH_PHP'  => '5.5.1'
        );
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch/php/5.5.1/bin/phpize', Config::getCurrentPhpizeBin());
        });
    }

    public function testGetCurrentPhpConfigScanPath()
    {
        $env = array(
            'PHPSWITCH_PHP'  => '5.5.1'
        );
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch/php/5.5.1/var/db', Config::getCurrentPhpConfigScanPath());
        });
    }

    public function testGetCurrentPhpDir()
    {
        $env = array(
            'PHPSWITCH_PHP'  => '5.5.1'
        );
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch/php/5.5.1', Config::getCurrentPhpDir());
        });
    }

    public function testGetLookupPrefix()
    {
        $env = array(
            'PHPSWITCH_LOOKUP_PREFIX' => getenv('PHPSWITCH_ROOT'),
        );
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch', Config::getLookupPrefix());
        });
    }

    public function testGetCurrentPhpBin()
    {
        $env = array(
            'PHPSWITCH_PATH' => getenv('PHPSWITCH_ROOT'),
        );
        $this->withEnv($env, function ($self) {
            $self->assertStringEndsWith('.phpswitch', Config::getCurrentPhpBin());
        });
    }

    public function testGetConfigParam()
    {
        $env = array(
            // I guess this causes the failure here: https://travis-ci.org/phpswitch/phpswitch/jobs/95057923
            // 'PHPSWITCH_ROOT' => __DIR__ . '/../fixtures',
            'PHPSWITCH_ROOT' => 'tests/fixtures',
        );
        $this->withEnv($env, function ($self) {
            $config = Config::getConfig();
            $self->assertSame(array('key1' => 'value1', 'key2' => 'value2'), $config);
            $self->assertEquals('value1', Config::getConfigParam('key1'));
            $self->assertEquals('value2', Config::getConfigParam('key2'));
        });
    }

    /**
     * PHPSWITCH_HOME and PHPSWITCH_ROOT are automatically defined if
     * the function which invokes this method doesn't set them explicitly.
     * Set PHPSWITCH_HOME and PHPSWITCH_ROOT to null when you want to unset them.
     */
    public function withEnv($newEnv, $callback)
    {
        // reset environment variables
        $oldEnv = $this->resetEnv($newEnv + array(
            'HOME'                  => null,
            'PHPSWITCH_HOME'          => getenv('PHPSWITCH_HOME'),
            'PHPSWITCH_PATH'          => null,
            'PHPSWITCH_PHP'           => null,
            'PHPSWITCH_ROOT'          => getenv('PHPSWITCH_ROOT'),
            'PHPSWITCH_LOOKUP_PREFIX' => null
        ));

        try {
            $callback($this);
            $this->resetEnv($oldEnv);
        } catch (\Exception $e) {
            $this->resetEnv($oldEnv);
            throw $e;
        }
    }

    public function resetEnv($env)
    {
        $oldEnv = array();
        foreach ($env as $key => $value) {
            $oldEnv[$key] = getenv($key);
            $this->putEnv($key, $value);
        }
        return $oldEnv;
    }

    public function putEnv($key, $value)
    {
        $setting = $key;

        if ($value !== null) {
            $setting .= '=' . $value;
        }

        $this->assertTrue(putenv($setting));
    }
}
