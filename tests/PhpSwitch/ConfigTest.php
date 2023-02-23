<?php

declare(strict_types=1);

namespace PhpSwitch\Tests;

use Exception;
use PhpSwitch\Config;
use PHPUnit\Framework\TestCase;

/**
 * You should use predefined $PHPSWITCH_HOME and $PHPSWITCH_ROOT (defined
 * in phpunit.xml), because they are used to create directories in
 * Phpswitch\Config class. When you want to set $PHPSWITCH_ROOT, $PHPSWITCH_HOME
 * or $HOME, you should get its value by calling `getenv' function and set
 * the value to the corresponding environment variable.
 */
final class ConfigTest extends TestCase
{
    public function testGetPhpSwitchHomeWhenHOMEIsNotDefined(): void
    {
        $env = ['PHPSWITCH_HOME' => null, 'PHPSWITCH_ROOT' => null, 'HOME' => null];
        $this->expectException(Exception::class);
        $this->withEnv($env, fn () => Config::getHome());
    }

    public function testGetPhpSwitchHomeWhenHOMEIsDefined(): void
    {
        $env = ['HOME' => '.phpswitch', 'PHPSWITCH_HOME' => null];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/.phpswitch', Config::getHome()));
    }

    public function testGetPhpSwitchHomeWhenPhpswitchHomeIsDefined(): void
    {
        $env = ['HOME' => '.phpswitch', 'PHPSWITCH_HOME' => '.phpswitch'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch', Config::getHome()));
    }

    public function testGetPhpswitchRootWhenPhpswitchRootIsDefined(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch', Config::getRoot()));
    }

    public function testGetPhpswitchRootWhenHOMEIsDefined(): void
    {
        $env = ['HOME' => '.phpswitch', 'PHPSWITCH_ROOT' => null];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/.phpswitch', Config::getRoot()));
    }

    public function testGetBuildDir(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/build', Config::getBuildDir()));
    }

    public function testGetDistFileDir(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/distfiles', Config::getDistFileDir()));
    }

    public function testGetTempFileDir(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/tmp', Config::getTempFileDir()));
    }

    public function testGetCurrentPhpName(): void
    {
        $env = ['PHPSWITCH_PHP' => '5.6.3'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('5.6.3', Config::getCurrentPhpName()));
    }

    public function testGetCurrentBuildDir(): void
    {
        $env = ['PHPSWITCH_PHP' => '5.6.3'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/build/5.6.3', Config::getCurrentBuildDir()));
    }

    public function testGetPHPReleaseListPath(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php-releases.json', Config::getPHPReleaseListPath()));
    }

    public function testGetInstallPrefix(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php', Config::getInstallPrefix()));
    }

    public function testGetVersionInstallPrefix(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php/5.5.1', Config::getVersionInstallPrefix('5.5.1')));
    }

    public function testGetVersionEtcPath(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php/5.5.1/etc', Config::getVersionEtcPath('5.5.1')));
    }

    public function testGetVersionBinPath(): void
    {
        $env = [];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php/5.5.1/bin', Config::getVersionBinPath('5.5.1')));
    }

    public function testGetCurrentPhpConfigBin(): void
    {
        $env = ['PHPSWITCH_PHP' => '5.5.1'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php/5.5.1/bin/php-config', Config::getCurrentPhpConfigBin()));
    }

    public function testGetCurrentPhpizeBin(): void
    {
        $env = ['PHPSWITCH_PHP' => '5.5.1'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php/5.5.1/bin/phpize', Config::getCurrentPhpizeBin()));
    }

    public function testGetCurrentPhpConfigScanPath(): void
    {
        $env = ['PHPSWITCH_PHP' => '5.5.1'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php/5.5.1/var/db', Config::getCurrentPhpConfigScanPath()));
    }

    public function testGetCurrentPhpDir(): void
    {
        $env = ['PHPSWITCH_PHP' => '5.5.1'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch/php/5.5.1', Config::getCurrentPhpDir()));
    }

    public function testGetLookupPrefix(): void
    {
        $env = ['PHPSWITCH_LOOKUP_PREFIX' => '.phpswitch'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch', Config::getLookupPrefix()));
    }

    public function testGetCurrentPhpBin(): void
    {
        $env = ['PHPSWITCH_PATH' => '.phpswitch'];
        $this->withEnv($env, fn () => $this->assertStringEndsWith('.phpswitch', Config::getCurrentPhpBin()));
    }

    public function testGetConfigParam(): void
    {
        $env = ['PHPSWITCH_ROOT' => 'tests/fixtures'];
        $this->withEnv($env, function () {
            $config = Config::getConfig();
            var_dump($config);
            $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $config);
            $this->assertEquals('value1', Config::getConfigParam('key1'));
            $this->assertEquals('value2', Config::getConfigParam('key2'));
        });
    }

    /**
     * PHPSWITCH_HOME and PHPSWITCH_ROOT are automatically defined if
     * the function which invokes this method doesn't set them explicitly.
     * Set PHPSWITCH_HOME and PHPSWITCH_ROOT to null when you want to unset them.
     *
     * @param array<string, string|null> $new_env
     */
    private function withEnv(array $new_env, callable $callback): void
    {
        $initial_env = [
            'HOME' => null,
            'PHPSWITCH_HOME' => '.phpswitch',
            'PHPSWITCH_PATH' => null,
            'PHPSWITCH_PHP' => null,
            'PHPSWITCH_ROOT' => '.phpswitch',
            'PHPSWITCH_LOOKUP_PREFIX' => null,
        ];
        $this->resetEnv([...$initial_env, ...$new_env]);

        try {
            $callback();
            $this->resetEnv($initial_env);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array<string, string|null> $env
     */
    private function resetEnv(array $env): void
    {
        foreach ($env as $key => $value) {
            putenv(is_null($value) ? $key : $key . '=' . $value);
        }
    }
}
