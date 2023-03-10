<?php

namespace PhpSwitch\Downloader;

use PhpSwitch\Utils;

class CurlCommandDownloader extends BaseDownloader
{
    protected function process($url, $targetFilePath)
    {
        $this->logger->info('downloading via curl command');
        //todo proxy setting
        $command = ['curl'];

        if ($proxy = $this->options->{'http-proxy'}) {
            $this->logger->warn('http proxy is not support by this download.');
        }
        if ($proxyAuth = $this->options->{'http-proxy-auth'}) {
            $this->logger->warn('http proxy is not support by this download.');
        }

        if ($this->options->{'continue'}) {
            $command[] = '-C -';
        }

        $command[] = '-L';
        if ($this->logger->isQuiet()) {
            $command[] = '--silent';
        }
        $command[] = '-o';
        $command[] = escapeshellarg((string) $targetFilePath);
        $command[] = escapeshellarg((string) $url);
        $cmd = implode(' ', $command);
        $this->logger->debug($cmd);
        Utils::system($cmd);

        return true;
    }

    public function hasSupport($requireSsl)
    {
        return Utils::findbin('curl');
    }
}
