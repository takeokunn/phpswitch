<?php

namespace PhpSwitch\Downloader;

use CLIFramework\Logger;
use GetOptionKit\OptionCollection;
use GetOptionKit\OptionResult;

class DownloadFactory
{
    final const METHOD_PHP_CURL = 'php_curl';
    final const METHOD_PHP_STREAM = 'php_stream';
    final const METHOD_WGET = 'wget';
    final const METHOD_CURL = 'curl';

    private static array $availableDownloaders = [self::METHOD_PHP_CURL => \PhpSwitch\Downloader\PhpCurlDownloader::class, self::METHOD_PHP_STREAM => \PhpSwitch\Downloader\PhpStreamDownloader::class, self::METHOD_WGET => \PhpSwitch\Downloader\WgetCommandDownloader::class, self::METHOD_CURL => \PhpSwitch\Downloader\CurlCommandDownloader::class];

    /**
     * When php built-in extensions don't support openssl, we can use curl or wget instead.
     */
    private static array $fallbackDownloaders = ['curl', 'wget'];

    /**
     * @param Logger       $logger      is used for creating downloader
     * @param OptionResult $optionResult options used for create downloader
     * @param array        $preferences Use downloader by preferences.
     * @param bool         $requireSsl
     *
     * @return BaseDownloader|null
     */
    protected static function create(Logger $logger, OptionResult $optionResult, array $preferences, $requireSsl = true)
    {
        foreach ($preferences as $preference) {
            if (isset(self::$availableDownloaders[$preference])) {
                $downloader = self::$availableDownloaders[$preference];
                $down = new $downloader($logger, $optionResult);
                if ($down->hasSupport($requireSsl)) {
                    return $down;
                }
            }
        }

        return;
    }

    /**
     * @param string       $downloader
     *
     * @return BaseDownloader
     */
    public static function getInstance(Logger $logger, OptionResult $optionResult, $downloader = null)
    {
        if (is_string($downloader)) {
            //if we specific a downloader class clearly, then it's the only choice
            if (class_exists($downloader) && is_subclass_of($downloader, \PhpSwitch\Downloader\BaseDownloader::class)) {
                return new $downloader($logger, $optionResult);
            }
            $downloader = [$downloader];
        }
        if (empty($downloader)) {
            $downloader = array_keys(self::$availableDownloaders);
        }

        //if --downloader presents, we will use it as the first choice,
        //even if the caller specific downloader by alias/array
        if ($optionResult->has('downloader')) {
            $logger->info("Found --downloader option, try to use {$optionResult->downloader} as default downloader.");
            $downloader = [...[$optionResult->downloader], ...$downloader];
        }

        $instance = self::create($logger, $optionResult, $downloader);
        if ($instance === null) {
            $logger->debug('Downloader not found, falling back to command-based downloader.');
            //if all downloader not available, maybe we should throw exceptions here instead of returning null?
            return self::create($logger, $optionResult, self::$fallbackDownloaders);
        } else {
            return $instance;
        }
    }

    public static function addOptionsForCommand(OptionCollection $optionCollection)
    {
        $optionCollection->add('downloader:', 'Use alternative downloader.');
        $optionCollection->add('continue', 'Continue getting a partially downloaded file.');
        $optionCollection->add('http-proxy:', 'HTTP proxy address')
            ->valueName('Proxy host[:port]');
        $optionCollection->add('http-proxy-auth:', 'HTTP proxy authentication')
            ->valueName('Proxy username:password');
        $optionCollection->add(
            'connect-timeout:',
            'Connection timeout'
        )
            ->valueName('Timeout in seconds');

        return $optionCollection;
    }
}
