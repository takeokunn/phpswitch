<?php

namespace PhpSwitch\Tests\Downloader;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;
use PhpSwitch\Config;
use PhpSwitch\Downloader\DownloadFactory;
use PhpSwitch\Testing\VCRAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @large
 */
class DownloaderTest extends TestCase
{
    public $logger;

    protected function setUp(): void
    {
        $this->logger = Logger::getInstance();
        $this->logger->setQuiet();

        VCRAdapter::enableVCR($this);
    }

    protected function tearDown(): void
    {
        VCRAdapter::disableVCR();
    }

    /**
     * @group noVCR
     */
    public function testDownloadByWgetCommand()
    {
        $this->assertDownloaderWorks(\PhpSwitch\Downloader\WgetCommandDownloader::class);
    }

    /**
     * @group noVCR
     */
    public function testDownloadByCurlCommand()
    {
        $this->assertDownloaderWorks(\PhpSwitch\Downloader\CurlCommandDownloader::class);
    }

    public function testDownloadByCurlExtension()
    {
        $this->assertDownloaderWorks(\PhpSwitch\Downloader\PhpCurlDownloader::class);
    }

    public function testDownloadByFileFunction()
    {
        $this->assertDownloaderWorks(\PhpSwitch\Downloader\PhpStreamDownloader::class);
    }

    private function assertDownloaderWorks($downloader)
    {
        $baseDownloader = DownloadFactory::getInstance($this->logger, new OptionResult(), $downloader);
        if ($baseDownloader->hasSupport(false)) {
            $actualFilePath = tempnam(Config::getTempFileDir(), '');
            $baseDownloader->download('http://httpbin.org/', $actualFilePath);
            $this->assertFileExists($actualFilePath);
        } else {
            $this->markTestSkipped();
        }
    }
}
