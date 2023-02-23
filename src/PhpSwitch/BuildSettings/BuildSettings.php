<?php

declare(strict_types=1);

namespace PhpSwitch\BuildSettings;

use Exception;

final class BuildSettings
{
    /**
     * @var array<string, string|null>
     */
    private array $enabled_variants = [];

    /**
     * @var list<string>
     * */
    private array $disabled_variants = [];

    /**
     * @var list<string>
     */
    private array $extra_options = [];

    /**
     * @param array{
     *     enabled_variants?: array<string, string|null>,
     *     disabled_variants?: list<string>,
     *     extra_options?: list<string>
     * }|array{} $settings
     */
    public function __construct(array $settings = [])
    {
        if (isset($settings['enabled_variants'])) {
            $this->setEnableVariants($settings['enabled_variants']);
        }
        if (isset($settings['disabled_variants'])) {
            $this->setDisableVariants($settings['disabled_variants']);
        }
        if (isset($settings['extra_options'])) {
            $this->extra_options = [...$this->extra_options, ...$settings['extra_options']];
        }
    }

    /**
     * @return array{
     *     enabled_variants: array<string, string|null>,
     *     disabled_variants: list<string>,
     *     extra_options: list<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'enabled_variants' => $this->enabled_variants,
            'disabled_variants' => $this->disabled_variants,
            'extra_options' => $this->extra_options,
        ];
    }

    ///////////////////////////////////////////////////////////////////////////
    //                             getter/setter                             //
    ///////////////////////////////////////////////////////////////////////////

    // for enabled_variant

    /**
     * @return array<string, string|null>
     */
    public function getEnabledVariants(): array
    {
        return $this->enabled_variants;
    }

    /**
     * @param array<string, string|null> $settings
     */
    public function setEnableVariants(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->setEnableVariant($key, $value);
        }
    }

    public function setEnableVariant(string $key, ?string $value): void
    {
        $this->enabled_variants = [...$this->enabled_variants, $key => $value];
    }

    public function isEnabledVariant(string $name): bool
    {
        return in_array($name, array_keys($this->enabled_variants), true);
    }

    // for disabled_variant

    /**
     * @return list<string>
     */
    public function getDisabledVariants(): array
    {
        return $this->disabled_variants;
    }

    /**
     * @param list<string> $settings
     */
    public function setDisableVariants(array $settings): void
    {
        foreach ($settings as $setting) {
            $this->setDisableVariant($setting);
        }
    }

    public function setDisableVariant(string $name): void
    {
        $this->disabled_variants = array_unique([...$this->disabled_variants, $name]);
    }

    public function isDisabledVariant(string $name): bool
    {
        return in_array($name, $this->disabled_variants, true);
    }

    // for extra_options

    /**
     * @return list<string>
     */
    public function getExtraOptions(): array
    {
        return $this->extra_options;
    }

    /**
     * @param list<string> $settings
     */
    public function setExtraOptions(array $settings): void
    {
        foreach ($settings as $setting) {
            $this->setExtraOption($setting);
        }
    }

    public function setExtraOption(string $name): void
    {
        $this->extra_options = array_unique([...$this->extra_options, $name]);
    }

    ///////////////////////////////////////////////////////////////////////////
    //                                utility                                //
    ///////////////////////////////////////////////////////////////////////////
    public function resolveVariants(): void
    {
        foreach ($this->disabled_variants as $disabled_variant) {
            $this->removeVariant($disabled_variant);
        }
    }

    private function removeVariant(string $name): void
    {
        $this->enabled_variants = array_filter($this->enabled_variants, fn ($value) => $value !== $name, ARRAY_FILTER_USE_KEY);
    }

    public function loadVariantInfoFile(string $variantFile): void
    {
        if (!is_readable($variantFile)) {
            throw new Exception("Can't load variant info! Variants file {$variantFile} is not readable.");
        }

        $file = file_get_contents($variantFile);
        if (!$file) {
            throw new Exception("Can't load variant info! Variants file {$variantFile} is not exist.");
        }

        /**
         * @var array{
         *     enabled_variants?: array<string, string|null>,
         *     disabled_variants?: list<string>,
         *     extra_options?: list<string>,
         * } $variant_info
         */
        $variant_info = unserialize($file);
        $this->loadVariantInfo($variant_info);
    }

    public function writeVariantInfoFile(string $variant_info_file): int|bool
    {
        $options = ['enabled_variants' => $this->enabled_variants, 'disabled_variants' => $this->disabled_variants, 'extra_options' => array_unique($this->extra_options)];
        return file_put_contents($variant_info_file, serialize($options));
    }

    /**
     * @param array{
     *     enabled_variants?: array<string, string|null>,
     *     disabled_variants?: list<string>,
     *     extra_options?: list<string>,
     * } $variant_info
     */
    public function loadVariantInfo(array $variant_info): void
    {
        if (isset($variant_info['enabled_variants'])) {
            $this->setEnableVariants($variant_info['enabled_variants']);
        }

        if (isset($variant_info['disabled_variants'])) {
            $this->setDisableVariants($variant_info['disabled_variants']);
        }

        if (isset($variant_info['extra_options'])) {
            $this->extra_options = array_unique([...$this->extra_options, ...$variant_info['extra_options']]);
        }

        $this->resolveVariants();
    }
}
