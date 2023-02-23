<?php

namespace PhpSwitch\Tests\Extension;

use CLIFramework\Logger;
use PhpSwitch\Extension\ExtensionFactory;
use PhpSwitch\Extension\ExtensionManager;
use PhpSwitch\Testing\VCRAdapter;
use PHPUnit\Framework\TestCase;

/**
 * ExtensionManagerTest
 *
 * @large
 * @group extension
 */
class ExtensionManagerTest extends TestCase
{
    private \PhpSwitch\Extension\ExtensionManager $manager;

    protected function setUp(): void
    {
        $logger = new Logger();
        $logger->setQuiet();
        $this->manager = new ExtensionManager($logger);

        VCRAdapter::enableVCR($this);
    }

    protected function tearDown(): void
    {
        VCRAdapter::disableVCR();
    }

    public function testCleanExtension()
    {
        $ext = ExtensionFactory::lookup('xdebug', [getenv('PHPSWITCH_EXTENSION_DIR')]);
        $this->assertTrue($this->manager->cleanExtension($ext));
    }
}
