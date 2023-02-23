<?php

declare(strict_types=1);

namespace PhpSwitch\Tests\Distribution;

use PHPUnit\Framework\TestCase;
use PhpSwitch\Distribution\DistributionUrlPolicy;

final class DistributionUrlPolicyTest extends TestCase
{
    private DistributionUrlPolicy $distributionUrlPolicy;

    protected function setUp(): void
    {
        $this->distributionUrlPolicy = new DistributionUrlPolicy();
    }

    /**
     * @dataProvider versionDataProvider
     */
    public function testBuildUrl(string $version, string $filename, string $dist_url): void
    {
        $build_url = $this->distributionUrlPolicy->buildUrl($version, $filename);
        $this->assertSame($dist_url, $build_url);
    }

    /**
     * @return list<list<string>>
     */
    public function versionDataProvider(): array
    {
        return [
            ['5.3.29', 'php-5.3.29.tar.bz2', 'https://museum.php.net/php5/php-5.3.29.tar.bz2'],
            ['5.4.7', 'php-5.4.7.tar.bz2', 'https://museum.php.net/php5/php-5.4.7.tar.bz2'],
            ['5.4.21', 'php-5.4.21.tar.bz2', 'https://museum.php.net/php5/php-5.4.21.tar.bz2'],
            ['5.4.22', 'php-5.4.22.tar.bz2', 'https://www.php.net/distributions/php-5.4.22.tar.bz2'],
            ['5.6.23', 'php-5.6.23.tar.bz2', 'https://www.php.net/distributions/php-5.6.23.tar.bz2']
        ];
    }
}
