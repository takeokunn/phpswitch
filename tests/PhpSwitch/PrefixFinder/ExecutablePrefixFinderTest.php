<?php

declare(strict_types=1);

namespace PhpSwitch\Tests\PrefixFinder;

use PHPUnit\Framework\TestCase;
use PhpSwitch\PrefixFinder\ExecutablePrefixFinder;

final class ExecutablePrefixFinderTest extends TestCase
{
    public function testFindValid(): void
    {
        $executablePrefixFinder = new ExecutablePrefixFinder('ls');
        $this->assertNotNull($executablePrefixFinder->findPrefix());
    }

    public function testFindInvalid(): void
    {
        $executablePrefixFinder = new ExecutablePrefixFinder('inexistent-binary');
        $this->assertNull($executablePrefixFinder->findPrefix());
    }
}
