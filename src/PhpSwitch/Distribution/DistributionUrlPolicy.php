<?php

declare(strict_types=1);

namespace PhpSwitch\Distribution;

final class DistributionUrlPolicy
{
    /**
     * Returns the distribution url for the version.
     */
    public function buildUrl(string $version, string $filename, bool $museum = false): string
    {
        //the historic releases only available at museum
        if ($museum || $this->isDistributedAtMuseum($version)) {
            return 'https://museum.php.net/php5/' . $filename;
        }

        return 'https://www.php.net/distributions/' . $filename;
    }

    private function isDistributedAtMuseum(string $version): int|bool
    {
        return version_compare($version, '5.4.21', '<=');
    }
}
