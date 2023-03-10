<?php

declare(strict_types=1);

namespace PhpSwitch\Tests;

use PHPUnit\Framework\TestCase;
use PhpSwitch\VersionDslParser;

/**
 * VersionDslParserTest
 *
 * @small
 */
class VersionDslParserTest extends TestCase
{
    /**
     * @var VersionDslParser
     */
    protected VersionDslParser $parser;

    protected function setUp(): void
    {
        $this->parser = new VersionDslParser();
    }

    /**
     * @return list<list<string>>
     */
    public static function dslProvider(): array
    {
        return [
            // official
            // implicit branch
            ['github:php/php-src', 'https://github.com/php/php-src/archive/master.tar.gz', 'php-master'],
            // explicit branch
            ['github:php/php-src@branch', 'https://github.com/php/php-src/archive/branch.tar.gz', 'php-branch'],
            // implicit branch
            ['github.com:php/php-src', 'https://github.com/php/php-src/archive/master.tar.gz', 'php-master'],
            // explicit branch
            ['github.com:php/php-src@branch', 'https://github.com/php/php-src/archive/branch.tar.gz', 'php-branch'],
            // implicit branch
            ['git@github.com:php/php-src', 'https://github.com/php/php-src/archive/master.tar.gz', 'php-master'],
            // explicit branch
            ['git@github.com:php/php-src@branch', 'https://github.com/php/php-src/archive/branch.tar.gz', 'php-branch'],
            // tag
            ['git@github.com:php/php-src@php-7.1.0RC3', 'https://github.com/php/php-src/archive/php-7.1.0RC3.tar.gz', 'php-7.1.0RC3'],
            // pre-release versions without the github: prefix
            ['php-7.2.0alpha1', 'https://github.com/php/php-src/archive/php-7.2.0alpha1.tar.gz', 'php-7.2.0alpha1'],
            ['7.2.0beta2', 'https://github.com/php/php-src/archive/php-7.2.0beta2.tar.gz', 'php-7.2.0beta2'],
            ['php-7.2.0RC3', 'https://github.com/php/php-src/archive/php-7.2.0RC3.tar.gz', 'php-7.2.0RC3'],
            // github urls
            ['https://www.github.com/php/php-src', 'https://github.com/php/php-src/archive/master.tar.gz', 'php-master'],
            ['http://www.github.com/php/php-src', 'https://github.com/php/php-src/archive/master.tar.gz', 'php-master'],
            ['www.github.com/php/php-src', 'https://github.com/php/php-src/archive/master.tar.gz', 'php-master'],
            // forks
            ['github:marc/php-src', 'https://github.com/marc/php-src/archive/master.tar.gz', 'php-marc-master'],
            // implicit branch
            ['github.com:marc/php-src', 'https://github.com/marc/php-src/archive/master.tar.gz', 'php-marc-master'],
            ['git@github.com:marc/php-src', 'https://github.com/marc/php-src/archive/master.tar.gz', 'php-marc-master'],
            ['https://www.github.com/marc/php-src', 'https://github.com/marc/php-src/archive/master.tar.gz', 'php-marc-master'],
            // tag in fork
            ['git@github.com:marc/php-src@php-7.1.0RC3', 'https://github.com/marc/php-src/archive/php-7.1.0RC3.tar.gz', 'php-marc-7.1.0RC3'],
            // Other URLs
            ['https://www.php.net/~ab/php-7.0.0alpha1.tar.gz', 'https://www.php.net/~ab/php-7.0.0alpha1.tar.gz', 'php-7.0.0alpha1'],
            ['https://www.php.net/~ab/php-7.0.0beta2.tar.gz', 'https://www.php.net/~ab/php-7.0.0beta2.tar.gz', 'php-7.0.0beta2'],
            ['https://www.php.net/~ab/php-7.0.0RC3.tar.gz', 'https://www.php.net/~ab/php-7.0.0RC3.tar.gz', 'php-7.0.0RC3'],
            ['https://www.php.net/~ab/php-7.0.0.tar.gz', 'https://www.php.net/~ab/php-7.0.0.tar.gz', 'php-7.0.0'],
            ['http://php.net/distributions/php-5.6.14.tar.bz2', 'http://php.net/distributions/php-5.6.14.tar.bz2', 'php-5.6.14'],
        ];
    }

    /**
     * @dataProvider dslProvider
     */
    public function testGithubDsl(string $dsl, string $url, string $version): void
    {
        $info = $this->parser->parse($dsl);
        $this->assertSame($version, $info['version']);
        $this->assertSame($url, $info['url']);
    }
}
