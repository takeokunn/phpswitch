<?php

declare(strict_types=1);

namespace PhpSwitch\Tests;

use PHPUnit\Framework\TestCase;
use PhpSwitch\VariantParser;
use PhpSwitch\InvalidVariantSyntaxException;

final class VariantParserTest extends TestCase
{
    public function test(): void
    {
        $enabled_variants = ['pdo' => null, 'sqlite' => null, 'debug' => null, 'apxs' => '/opt/local/apache2/bin/apxs', 'calendar' => null];
        $disabled_variants = ['mysql'];
        $extra_options = ['--with-icu-dir=/opt/local'];

        $this->assertEquals(
            ['enabled_variants' => $enabled_variants, 'disabled_variants' => $disabled_variants, 'extra_options' => $extra_options],
            $this->parse(['+pdo', '+sqlite', '+debug', '+apxs=/opt/local/apache2/bin/apxs', '+calendar', '-mysql', '--', '--with-icu-dir=/opt/local'])
        );
    }

    public function testVariantAll(): void
    {
        $enabled_variants = ['all' => null];
        $disabled_variants = ['apxs2', 'mysql'];
        $extra_options = [];
        $this->assertEquals(
            ['enabled_variants' => $enabled_variants, 'disabled_variants' => $disabled_variants, 'extra_options' => $extra_options],
            $this->parse(['+all', '-apxs2', '-mysql'])
        );
    }

    /**
     * @dataProvider variantGroupOverloadProvider
     *
     * @param list<string> $args
     * @param array<string, string|null> $expectedEnabledVariants
     */
    public function testVariantGroupOverload(array $args, array $expectedEnabledVariants): void
    {
        $info = $this->parse($args);
        $this->assertEquals($expectedEnabledVariants, $info['enabled_variants']);
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected static function variantGroupOverloadProvider(): array
    {
        return [
            'overrides default variant value' => [['+default', '+openssl=/usr'], ['default' => null, 'openssl' => '/usr']],
            'order must be irrelevant' => [['+openssl=/usr', '+default'], ['openssl' => '/usr', 'default' => null]],
            'negative variant' => [['+default', '-openssl'], ['default' => null]],
            'negative variant precedence' => [['-openssl', '+default'], ['default' => null]],
            'negative variant with an overridden value' => [['+default', '-openssl=/usr'], ['default' => null]]
        ];
    }

    /**
     * @link https://github.com/phpbrew/phpbrew/issues/495
     */
    public function testBug495(): void
    {
        $enabled_variants = ['gmp' => '/path/x86_64-linux-gnu'];
        $disabled_variants = ['openssl', 'xdebug'];
        $extra_options = [];
        $this->assertEquals(
            ['enabled_variants' => $enabled_variants, 'disabled_variants' => $disabled_variants, 'extra_options' => $extra_options],
            $this->parse(['+gmp=/path/x86_64-linux-gnu', '-openssl', '-xdebug'])
        );
    }

    public function testVariantUserValueContainsVersion(): void
    {
        $enabled_variants = ['openssl' => '/usr/local/Cellar/openssl/1.0.2e', 'gettext' => '/usr/local/Cellar/gettext/0.19.7'];
        $disabled_variants = [];
        $extra_options = [];
        $this->assertEquals(
            ['enabled_variants' => $enabled_variants, 'disabled_variants' => $disabled_variants, 'extra_options' => $extra_options],
            $this->parse(['+openssl=/usr/local/Cellar/openssl/1.0.2e', '+gettext=/usr/local/Cellar/gettext/0.19.7'])
        );
    }

    /**
     * @dataProvider revealCommandArgumentsProvider
     *
     * @param array<string, array<mixed>> $info
     */
    public function testRevealCommandArguments(array $info, string $expected): void
    {
        $this->assertEquals($expected, VariantParser::revealCommandArguments($info));
    }

    protected static function revealCommandArgumentsProvider(): array
    {
        return [
            [
                ['enabled_variants' => ['mysql' => true, 'openssl' => '/usr'], 'disabled_variants' => ['apxs2'], 'extra_options' => ['--with-icu-dir=/usr']],
                '+mysql +openssl=/usr -apxs2 -- --with-icu-dir=/usr'
            ]
        ];
    }

    /**
     * @dataProvider invalidSyntaxProvider
     * @param array<mixed> $args
     */
    public function testInvalidSyntax(array $args, string $expected): void
    {
        $this->expectException(InvalidVariantSyntaxException::class);
        $this->expectExceptionMessage($expected);

        $this->parse($args);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function invalidSyntaxProvider(): array
    {
        return [
            'Empty argument' => [[''], 'Variant cannot be empty'],
            'Empty variant name' => [['+'], 'Variant name cannot be empty'],
            'Empty variant name with value' => [['-='], 'Variant name cannot be empty'],
        ];
    }

    /**
     * @param list<string> $args
     *
     * @return array{
     *     enabled_variants: list<array{string, string|null}>,
     *     disabled_variants: list<string>,
     *     extra_options: list<string>
     * }
     *
     * @throws InvalidVariantSyntaxException
     */
    private function parse(array $args): array
    {
        return VariantParser::parseCommandArguments($args);
    }
}
