<?php

declare(strict_types=1);

namespace PhpSwitch;

final class BuildFinder
{
    /**
     * @return list<string> PHP builds
     */
    public static function findInstalledBuilds(): array
    {
        $path = Config::getRoot() . DIRECTORY_SEPARATOR . 'php';

        if (!file_exists($path)) {
            return [];
        }

        $names = array_filter(scandir($path), fn($name) => $name != '.'
            && $name != '..'
            && file_exists(
                $path
                . DIRECTORY_SEPARATOR . $name
                . DIRECTORY_SEPARATOR . 'bin'
                . DIRECTORY_SEPARATOR . 'php'
            ));

        uasort($names, 'version_compare'); // ordering version name ascending... 5.5.17, 5.5.12

        // make it descending... since there is no sort function for user-define in reverse order.
        return array_reverse($names);
    }

    /**
     * @return list<string> PHP versions
     */
    public static function findInstalledVersions(): array
    {
        return array_map(fn($name) => preg_replace('/^php-(?=(\d+\.\d+\.\d+(-dev|((alpha|beta|RC)\d+))?)$)/', '', (string) $name), self::findInstalledBuilds());
    }
}
