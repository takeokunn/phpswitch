<?php

declare(strict_types=1);

namespace PhpSwitch;

use CLIFramework\Logger;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PhpSwitch\Buildable;
use PhpSwitch\Exception\SystemCommandException;

final class Utils
{
    /**
     * @return string|false
     */
    public static function readTimeZone()
    {
        $tz_file = '/etc/timezone';
        if (!is_readable($tz_file)) {
            return false;
        }

        $tz = file($tz_file);
        if (!$tz) {
            return false;
        }

        $lines = array_filter($tz, fn ($line) => !preg_match('/^#/', (string) $line));
        if (count($lines) === 0) {
            return false;
        }

        return trim((string) $lines[0]);
    }

    /**
     * Detect support 64bit.
     */
    public static function support64bit(): bool
    {
        $int = '9223372036854775807';
        $int = intval($int);

        /* Not support 64bit */
        if ($int !== 9_223_372_036_854_775_807) {
            return false;
        }

        return true;
    }

    /**
     * Find bin from prefix list.
     */
    public static function findBinByPrefix(string $bin): ?string
    {
        $prefixes = self::getLookupPrefixes();

        foreach ($prefixes as $prefix) {
            $binPath = $prefix . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $bin;

            if (file_exists($binPath)) {
                return $binPath;
            }

            $binPath = $prefix . DIRECTORY_SEPARATOR . 'sbin' . DIRECTORY_SEPARATOR . $bin;

            if (file_exists($binPath)) {
                return $binPath;
            }
        }

        return null;
    }

    public static function findLibPrefix(): ?string
    {
        $files = func_get_args();
        $prefixes = self::getLookupPrefixes();

        foreach ($prefixes as $prefix) {
            foreach ($files as $file) {
                $p = $prefix . DIRECTORY_SEPARATOR . $file;
                if (file_exists($p)) {
                    return $prefix;
                }
            }
        }

        return null;
    }

    public static function findIncludePrefix(): ?string
    {
        $files = func_get_args();
        $prefixes = self::getLookupPrefixes();

        foreach ($prefixes as $prefix) {
            foreach ($files as $file) {
                $path = $prefix . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . $file;
                if (file_exists($path)) {
                    return $prefix;
                }
            }
        }

        return null;
    }

    public static function getPkgConfigPrefix(string $package): ?string
    {
        if (!self::findBin('pkg-config')) {
            return null;
        }

        $path = exec('pkg-config --variable=prefix ' . escapeshellarg($package) . ' 2>/dev/null', $_, $ret);

        if ($ret !== 0) {
            return null;
        }

        if (!$path) {
            return null;
        }

        if (!file_exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * @param list<string>|string $command
     */
    public static function system($command, Logger $logger = null, Buildable $build = null): int
    {
        if (is_array($command)) {
            $command = implode(' ', $command);
        }

        if (isset($logger)) {
            $logger->debug('Running Command:' . $command);
        }

        $lastline = system($command, $return_value);
        if ($return_value !== 0) {
            throw new SystemCommandException("Command failed: $command returns: $lastline", $build);
        }

        return $return_value;
    }

    /**
     * Find executable binary by PATH environment.
     */
    public static function findBin(string $bin): ?string
    {
        $path = getenv('PATH');
        $paths = explode(PATH_SEPARATOR, $path);

        foreach ($paths as $path) {
            $f = $path . DIRECTORY_SEPARATOR . $bin;
            // realpath will handle file existence or symbolic link automatically
            $f = realpath($f);
            if ($f !== false) {
                return $f;
            }
        }

        return null;
    }

    /**
     * Finds prefix using the given finders.
     *
     * @param  list<PrefixFinder> $prefixFinders
     */
    public static function findPrefix(array $prefixFinders): ?string
    {
        foreach ($prefixFinders as $prefixFinder) {
            $prefix = $prefixFinder->findPrefix();
            if (isset($prefix)) {
                return $prefix;
            }
        }

        return null;
    }

    public static function recursive_unlink(string $path, Logger $logger): void
    {
        $directory_iterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($directory_iterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($it as $file) {
            $logger->debug('Deleting ' . $file->getPathname());
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        if (is_dir($path)) {
            rmdir($path);
        }

        if (is_file($path)) {
            unlink($path);
        }
    }

    public static function editor(string $file): int
    {
        $tty = exec('tty');
        $editor = escapeshellarg(getenv('EDITOR') ?: 'nano');
        exec("{$editor} {$file} > {$tty}", $_, $ret);

        return $ret;
    }

    ///////////////////////////////////////////////////////////////////////////
    //                            private utility                            //
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @return list<string>
     */
    private static function detectArchitecture(string $prefix): array
    {
        /**
         * Prioritizes the FHS compliant
         * /usr/lib/i386-linux-gnu/
         * /usr/include/i386-linux-gnu/
         * /usr/lib/x86_64-linux-gnu/
         * /usr/local/lib/powerpc-linux-gnu/
         * /usr/local/include/powerpc-linux-gnu/
         * /opt/foo/lib/sparc-solaris/
         * /opt/bar/include/sparc-solaris/
         */
        $multi_archs = [
            'lib/lib64',
            'lib/lib32',
            'lib64',
            // Linux Fedora
            'lib',
            // CentOS
            'lib/ia64-linux-gnu',
            // Linux IA-64
            'lib/x86_64-linux-gnu',
            // Linux x86_64
            'lib/x86_64-kfreebsd-gnu',
            // FreeBSD
            'lib/i386-linux-gnu',
        ];

        return array_filter($multi_archs, fn($arch_name) => file_exists($prefix . '/' . $arch_name));
    }

    /**
     * @return list<string>
     */
    private static function getLookupPrefixes(): array
    {
        $prefixes = [
            '/usr',
            '/usr/local',
            '/usr/local/opt',
            // homebrew link
            '/opt',
            '/opt/local',
        ];

        if ($pathStr = getenv('PHPSWITCH_LOOKUP_PREFIX')) {
            $paths = explode(':', $pathStr);
            foreach ($paths as $path) {
                $prefixes = [...$prefixes, $path];
            }
        }

        // append detected lib paths to the end
        foreach ($prefixes as $prefix) {
            foreach (self::detectArchitecture($prefix) as $arch) {
                $prefixes = [...$prefixes, "$prefix/$arch"];
            }
        }

        return array_reverse($prefixes);
    }
}
