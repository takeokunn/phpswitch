<?php

namespace PhpSwitch;

use Exception;

class VersionDslParser
{
    protected static $schemes = ['git@github.com:', 'github.com:', 'github.com/', 'github:'];

    public function parse($dsl)
    {
        if (preg_match('/^(php-)?(\d+\.\d+\.\d+(alpha|beta|RC)\d+)$/', (string) $dsl, $matches)) {
            $version = 'php-' . $matches[2];

            return ['version' => $version, 'url' => $this->buildGitHubUrl('php', $version), 'is_tag' => true];
        }

        // make url
        $url = str_replace(self::$schemes, 'https://github.com/', (string) $dsl);

        // parse github fork owner and branch
        if (
            preg_match(
                "#https?://(www\.)?github\.com/([0-9a-zA-Z-._]+)/php-src(@([0-9a-zA-Z-._]+))?#",
                $url,
                $matches
            )
        ) {
            $owner = $matches[2];
            $branch = $matches[4] ?? 'master';
            $version = preg_replace('/^php-/', '', $branch);

            if ($owner !== 'php') {
                $version = $owner . '-' . $version;
            }

            return ['version' => 'php-' . $version, 'url' => $this->buildGitHubUrl($owner, $branch)];
        }

        // non github url
        if (preg_match('#^https?://#', $url)) {
            if (!preg_match('#(php-(\d.\d+.\d+(?:(?:RC|alpha|beta)\d+)?)\.tar\.(?:gz|bz2))#', $url, $matches)) {
                throw new Exception("Can not find version name from the given URL: $url");
            }

            return ['version' => "php-{$matches[2]}", 'url' => $url];
        }

        return false;
    }

    /**
     * Builds the URL of the package on GitHub
     *
     * @param string $owner Repository owner
     * @param string $ref Git commit reference
     *
     * @return string
     */
    private function buildGitHubUrl($owner, $ref)
    {
        return sprintf(
            'https://github.com/%s/php-src/archive/%s.tar.gz',
            rawurlencode($owner),
            rawurlencode($ref)
        );
    }
}