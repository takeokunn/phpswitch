<?php

namespace PhpSwitch\Tests\PrefixFinder;

use PhpSwitch\PrefixFinder\ExecutablePrefixFinder;
use PHPUnit\Framework\TestCase;

/**
 * @group prefixfinder
 */
class ExecutablePrefixFinderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindValid()
    {
        $executablePrefixFinder = new \PhpSwitch\PrefixFinder\ExecutablePrefixFinder('ls');
        $this->assertNotNull($executablePrefixFinder->findPrefix());
    }

    public function testFindInvalid()
    {
        $executablePrefixFinder = new \PhpSwitch\PrefixFinder\ExecutablePrefixFinder('inexistent-binary');
        $this->assertNull($executablePrefixFinder->findPrefix());
    }
}
