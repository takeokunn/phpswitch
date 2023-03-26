<?php

declare(strict_types=1);

namespace PhpSwitch\Helper;

use Exception;

/**
 * This config class provides settings based on the current environment
 * variables like PHPSWITCH_ROOT or PHPSWITCH_HOME.
 */
final class DirectoryHelper
{
    /**
     * Return optional home directory.
     */
    public function getRoot(): string
    {
        $home = getenv('HOME');
        $custom = getenv('PHPSWITCH_HOME');

        if ($home === false && $custom === false) {
            throw new Exception('Environment variable PHPSWITCH_HOME or HOME is required');
        }

        $path = $custom ?? $home . DIRECTORY_SEPARATOR . '.phpswitch';
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * cache directory for configure.
     */
    public function getCacheDir(): string
    {
        return $this->getRoot() . DIRECTORY_SEPARATOR . 'cache';
    }

    /**
     * php(s) could be global, so we use ROOT path.
     */
    public function getBuildDir(): string
    {
        return $this->getRoot() . DIRECTORY_SEPARATOR . 'build';
    }

    public static function getCurrentBuildDir(): string
    {
        return self::getRoot() . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . self::getCurrentPhpName();
    }

    public static function getDistFileDir(): string
    {
        $dir = self::getRoot() . DIRECTORY_SEPARATOR . 'distfiles';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    public static function getTempFileDir(): string
    {
        $dir = self::getRoot() . DIRECTORY_SEPARATOR . 'tmp';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    public static function getPHPReleaseListPath(): string
    {
        // Release list from php.net
        return self::getRoot() . DIRECTORY_SEPARATOR . 'php-releases.json';
    }

    /**
     * A build prefix is the prefix we specified when we install the PHP.
     *
     * when PHPSWITCH_ROOT is pointing to /home/user/.phpswitch
     *
     * php(s) will be installed into /home/user/.phpswitch/php/php-{version}
     */
    public static function getInstallPrefix(): string
    {
        return self::getRoot() . DIRECTORY_SEPARATOR . 'php';
    }

    public static function getVersionInstallPrefix(string $name): string
    {
        return self::getInstallPrefix() . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * XXX: This method should be migrated to PhpSwitch\Build class.
     */
    public static function getVersionEtcPath(string $name): string
    {
        return self::getVersionInstallPrefix($name) . DIRECTORY_SEPARATOR . 'etc';
    }

    public static function getVersionBinPath(string $name): string
    {
        return self::getVersionInstallPrefix($name) . DIRECTORY_SEPARATOR . 'bin';
    }

    public static function getCurrentPhpConfigBin(): string
    {
        return self::getCurrentPhpDir() . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'php-config';
    }

    public static function getCurrentPhpizeBin(): string
    {
        return self::getCurrentPhpDir() . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpize';
    }

    /**
     * @return list<string>
     */
    public static function getSapis(): array
    {
        return ['cli', 'fpm', 'apache'];
    }

    /**
     * XXX: This method should be migrated to PhpSwitch\Build class.
     */
    public static function getCurrentPhpConfigScanPath(bool $home = false): string
    {
        return self::getCurrentPhpDir($home) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'db';
    }

    public static function getCurrentPhpDir(bool $home = false): string
    {
        if ($home) {
            return self::getHome() . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . self::getCurrentPhpName();
        }

        return self::getRoot() . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . self::getCurrentPhpName();
    }

    /**
     * getCurrentPhpName return the current php version from environment variable `PHPSWITCH_PHP`.
     */
    public static function getCurrentPhpName(): string
    {
        return strval(getenv('PHPSWITCH_PHP'));
    }

    public static function getLookupPrefix(): string
    {
        return getenv('PHPSWITCH_LOOKUP_PREFIX');
    }

    public static function getCurrentPhpBin(): string
    {
        return getenv('PHPSWITCH_PATH');
    }

    /**
     * @return array<mixed>
     */
    public static function getConfig(): array
    {
        $configFile = self::getRoot() . DIRECTORY_SEPARATOR . 'config.yaml';
        if (!file_exists($configFile)) {
            return [];
        }

        return Yaml::parse(file_get_contents($configFile));
    }

    public static function getConfigParam(string $param): string
    {
        $config = self::getConfig();
        if ($param && isset($config[$param])) {
            return $config[$param];
        }

        return $config;
    }

    ///////////////////////////////////////////////////////////////////////////
    //                                set env                                //
    ///////////////////////////////////////////////////////////////////////////
    public function setPhpswitchHome(string $home): void
    {
        putenv('PHPSWITCH_HOME=' . $home);
    }

    public function setPhpswitchRoot(string $root): void
    {
        putenv('PHPSWITCH_ROOT=' . $root);
    }
}
