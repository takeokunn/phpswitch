<?php

namespace PhpSwitch\Downloader;

use CurlKit\CurlDownloader;
use CurlKit\Progress\ProgressBar;
use RuntimeException;

class PhpCurlDownloader extends BaseDownloader
{
    protected function process($url, $targetFilePath)
    {
        $this->logger->info("Downloading $url via curl extension");

        $curlDownloader = new CurlDownloader();

        $seconds = $this->options->{'connect-timeout'};
        if ($seconds || $seconds = getenv('CONNECT_TIMEOUT')) {
            $curlDownloader->setConnectionTimeout($seconds);
        }
        if ($proxy = $this->options->{'http-proxy'}) {
            $curlDownloader->setProxy($proxy);
        }
        if ($proxyAuth = $this->options->{'http-proxy-auth'}) {
            $curlDownloader->setProxyAuth($proxyAuth);
        }
        if (!$this->options->{'no-progress'} && $this->logger->getLevel() > 2) {
            $curlDownloader->setProgressHandler(new ProgressBar());
        }
        if ($this->options->{'continue'}) {
            $this->logger->warn('--continue is not support by this download.');
        }
        $binary = $curlDownloader->request($url);
        if (false === file_put_contents($targetFilePath, $binary)) {
            throw new RuntimeException("Can't write file $targetFilePath");
        }

        return true;
    }

    public function hasSupport($requireSsl)
    {
        if (!extension_loaded('curl')) {
            return false;
        }
        if ($requireSsl) {
            $info = curl_version();

            return in_array('https', $info['protocols']);
        }

        return true;
    }
}
