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
        $this->assertDownloaderWorks('PhpSwitch\Downloader\WgetCommandDownloader');
    }

    /**
     * @group noVCR
     */
    public function testDownloadByCurlCommand()
    {
        $this->assertDownloaderWorks('PhpSwitch\Downloader\CurlCommandDownloader');
    }

    public function testDownloadByCurlExtension()
    {
        $this->assertDownloaderWorks('PhpSwitch\Downloader\PhpCurlDownloader');
    }

    public function testDownloadByFileFunction()
    {
        $this->assertDownloaderWorks('PhpSwitch\Downloader\PhpStreamDownloader');
    }

    private function assertDownloaderWorks($downloader)
    {
        $instance = DownloadFactory::getInstance($this->logger, new OptionResult(), $downloader);
        if ($instance->hasSupport(false)) {
            $actualFilePath = tempnam(Config::getTempFileDir(), '');
            $instance->download('http://httpbin.org/', $actualFilePath);
            $this->assertFileExists($actualFilePath);
        } else {
            $this->markTestSkipped();
        }
    }
}
