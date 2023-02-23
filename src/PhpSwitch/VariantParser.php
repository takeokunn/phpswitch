<?php

declare(strict_types=1);

namespace PhpSwitch;

final class VariantParser
{
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
    public static function parseCommandArguments(array $args): array
    {
        $target = [];
        $enabled_variants = [];
        $disabled_variants = [];
        $extra_options = [];

        while (true) {
            $arg = array_shift($args);

            if ($arg === null) {
                break;
            }

            if ($arg === '') {
                throw new InvalidVariantSyntaxException('Variant cannot be empty');
            }

            if ($arg === '--') {
                $extra_options = $args;
                break;
            }

            $operator = substr($arg, 0, 1);

            $variant = substr($arg, 1);
            [$name, $value] = array_pad(explode('=', $variant, 2), 2, null);

            if ($name === '') {
                throw new InvalidVariantSyntaxException('Variant name cannot be empty');
            }

            match ($operator) {
                '+' => $enabled_variants[$name] = $value,
                '-' => $disabled_variants[] = $name,
                default => throw new InvalidVariantSyntaxException('Variant must start with a + or -'),
            };

            $target[$name] = $value;
        }

        return [
            'enabled_variants' => $enabled_variants,
            'disabled_variants' => $disabled_variants,
            'extra_options' => $extra_options
        ];
    }

    /**
     * Reveal the variants info to command arguments.
     *
     * @param array{
     *     enabled_variants: list<array{string, string|null}>,
     *     disabled_variants: list<string>,
     *     extra_options: list<string>
     * } $info
     *
     * @return string
     */
    public static function revealCommandArguments(array $info): string
    {
        $args = [];

        if (count($info['enabled_variants']) > 0) {
            foreach ($info['enabled_variants'] as $key => $value) {
                $arg = '+' . $key;
                if (is_string($value)) {
                    $arg .= '=' . $value;
                }
                $args[] = $arg;
            }
        }

        if (count($info['disabled_variants']) > 0) {
            foreach ($info['disabled_variants'] as $value) {
                $args[] = '-' . $value;
            }
        }

        if (!empty($info['extra_options'])) {
            $args = array_merge($args, ['--'], $info['extra_options']);
        }

        return implode(' ', $args);
    }
}
