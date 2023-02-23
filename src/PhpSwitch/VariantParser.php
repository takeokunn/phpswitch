<?php

namespace PhpSwitch;

class VariantParser
{
    /**
     * @param string[] $args
     *
     * @return array
     *
     * @throws InvalidVariantSyntaxException
     */
    public static function parseCommandArguments(array $args)
    {
        $target = [];
        $extra = [];
        $enabledVariants = [];
        $disabledVariants = [];

        while (true) {
            $arg = array_shift($args);

            if ($arg === null) {
                break;
            }

            if ($arg === '') {
                throw new InvalidVariantSyntaxException('Variant cannot be empty');
            }

            if ($arg === '--') {
                $extra = $args;
                break;
            }

            $operator = substr($arg, 0, 1);

            match ($operator) {
                '+' => $target =& $enabledVariants,
                '-' => $target =& $disabledVariants,
                default => throw new InvalidVariantSyntaxException('Variant must start with a + or -'),
            };

            $variant            = substr($arg, 1);
            [$name, $value] = array_pad(explode('=', $variant, 2), 2, null);

            if ($name === '') {
                throw new InvalidVariantSyntaxException('Variant name cannot be empty');
            }

            $target[$name] = $value;
        }

        return ['enabled_variants' => $enabledVariants, 'disabled_variants' => $disabledVariants, 'extra_options' => $extra];
    }

    /**
     * Reveal the variants info to command arguments.
     */
    public static function revealCommandArguments(array $info)
    {
        $args = [];

        foreach ($info['enabled_variants'] as $k => $v) {
            $arg = '+' . $k;

            if (!is_bool($v)) {
                $arg .= '=' . $v;
            }

            $args[] = $arg;
        }

        if (!empty($info['disabled_variants'])) {
            foreach ($info['disabled_variants'] as $k => $_) {
                $args[] = '-' . $k;
            }
        }

        if (!empty($info['extra_options'])) {
            $args = array_merge($args, ['--'], $info['extra_options']);
        }

        return implode(' ', $args);
    }
}