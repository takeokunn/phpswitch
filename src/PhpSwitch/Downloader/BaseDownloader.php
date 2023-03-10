<?php

namespace PhpSwitch\Downloader;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;
use RuntimeException;

abstract class BaseDownloader
{
    protected $logger;

    protected $options;

    public function __construct(Logger $logger, OptionResult $optionResult)
    {
        $this->logger = $logger;
        $this->options = $optionResult;
    }

    /**
     * @param string $url            the url to be downloaded
     * @param string $targetFilePath the path where file to be saved. null means auto-generated temp path
     *
     * @return bool|string if download successfully, return target file path, otherwise return false.
     *
     * @throws RuntimeException
     */
    public function download($url, $targetFilePath = null): bool|string
    {
        if (empty($targetFilePath)) {
            $targetFilePath = tempnam(sys_get_temp_dir(), 'phpswitch_');
            if ($targetFilePath === false) {
                throw new RuntimeException('Fail to create temp file');
            }
        } else {
            if (!file_exists($targetFilePath)) {
                touch($targetFilePath);
            }
        }
        if (!is_writable($targetFilePath)) {
            throw new RuntimeException("Target path ($targetFilePath) is not writable!");
        }
        if ($this->process($url, $targetFilePath)) {
            $this->logger->debug("$url => $targetFilePath");

            return $targetFilePath;
        } else {
            return false;
        }
    }

    /**
     * fetch the remote content.
     *
     * @param string $url The url to be downloaded
     *
     * @return bool|string return content if download successfully, otherwise false is returned
     *
     * @throws RuntimeException
     */
    public function request($url): bool|string
    {
        $path = $this->download($url);

        return $path === false ? false : file_get_contents($path);
    }

    abstract protected function process($url, $targetFilePath);

    /**
     * @param string $url
     *
     * @return string|bool the resolved download file name or false it
     *                     the url string can't be parsed
     */
    public function resolveDownloadFileName($url): string|bool
    {
        // Check if the url is for php source archive
        if (preg_match('/php-\d.+\.tar\.(bz2|gz|xz)/', $url, $parts)) {
            return $parts[0];
        }

        // try to get the filename through parse_url
        $path = parse_url($url, PHP_URL_PATH);
        if (false === $path || !str_contains($path, '.')) {
            return;
        }

        return basename($path);
    }

    abstract public function hasSupport($requireSsl);
}
