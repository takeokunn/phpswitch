<?php

declare(strict_types=1);

namespace PhpSwitch;

use PhpSwitch\BuildSettings\BuildSettings;

/**
 * A build object contains version information,
 * variant configuration,
 * paths and an build identifier (BuildId).
 */
final class Build implements Buildable
{
    /**
     * States that describe finished task.
     */
    final const STATE_NONE = 0;
    final const STATE_DOWNLOAD = 1;
    final const STATE_EXTRACT = 2;
    final const STATE_CONFIGURE = 3;
    final const STATE_BUILD = 4;
    final const STATE_INSTALL = 5;

    private readonly string $name;
    private readonly string $version;

    /**
     * @var string The source directory
     */
    private string $sourceDirectory;

    /**
     * @var string the directory that contains bin/php, var/..., includes/
     */
    public ?string $installPrefix = null;
    public BuildSettings $settings;

    /**
     * Build state.
     */
    private int $state;

    /**
     * Construct a Build object,.
     *
     * A build object contains the information of all build options, prefix, paths... etc
     *
     * @param string      $version       build version
     * @param string|null $name          build name
     * @param string|null $installPrefix install prefix
     */
    public function __construct(string $version, ?string $name = null, ?int $installPrefix = null)
    {
        if (str_starts_with($version, 'php-')) {
            $version = substr($version, 4);
        }

        if (is_null($name)) {
            $name = 'php-' . $version;
        }

        $this->version = $version;
        $this->name = $name;

        if ($installPrefix) {
            $this->setInstallPrefix($installPrefix);
        }

        $this->setBuildSettings(new BuildSettings());
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function compareVersion(string $version): int
    {
        return version_compare($this->version, $version);
    }

    /**
     * PHP Source directory, this method returns value only when source directory is set.
     */
    public function setSourceDirectory(string $dir): void
    {
        $this->sourceDirectory = $dir;
    }

    public function getSourceDirectory(): string
    {
        if ($this->sourceDirectory && !file_exists($this->sourceDirectory)) {
            mkdir($this->sourceDirectory, 0755, true);
        }

        return $this->sourceDirectory;
    }

    public function isBuildable(): bool
    {
        return file_exists($this->sourceDirectory . DIRECTORY_SEPARATOR . 'Makefile');
    }

    public function getBuildLogPath(): string
    {
        return $this->getSourceDirectory() . DIRECTORY_SEPARATOR . 'build.log';
    }

    public function setInstallPrefix(string $prefix): void
    {
        $this->installPrefix = $prefix;
    }

    public function getBinDirectory(): string
    {
        return $this->installPrefix . DIRECTORY_SEPARATOR . 'bin';
    }

    public function getEtcDirectory(): string
    {
        $etc = $this->installPrefix . DIRECTORY_SEPARATOR . 'etc';
        if (!file_exists($etc)) {
            mkdir($etc, 0755, true);
        }

        return $etc;
    }

    public function getInstallPrefix(): ?string
    {
        return $this->installPrefix;
    }

    private function setBuildSettings(BuildSettings $buildSettings): void
    {
        $this->settings = $buildSettings;
        if (is_null($this->installPrefix)) {
            return;
        }

        // TODO: in future, we only stores build meta information, and that
        // also contains the variant info,
        // but for backward compatibility, we still need a method to handle
        // the variant info file..
        $variantFile = $this->getInstallPrefix() . DIRECTORY_SEPARATOR . 'phpswitch.variants';
        if (file_exists($variantFile)) {
            $this->settings->loadVariantInfoFile($variantFile);
        }
    }

    /**
     * Find a installed build by name,
     * currently a $name is a php version, but in future we may have customized
     * name for users.
     */
    public static function findByName(string $name): ?Build
    {
        $prefix = Config::getVersionInstallPrefix($name);
        if (!file_exists($prefix)) {
            return null;
        }

        // a installation exists
        return new self($name, null, $prefix);
    }

    /**
     * Where we store the last finished state, currently for:.
     *
     *  - FALSE or NULL - nothing done yet.
     *  - "download" - distribution file was downloaded.
     *  - "extract"  - distribution file was extracted to the build directory.
     *  - "configure" - configure was done.
     *  - "make"      - make was done.
     *  - "install"   - installation was done.
     *
     * Not used yet.
     */
    public function getStateFile(): ?string
    {
        return $this->getInstallPrefix() . DIRECTORY_SEPARATOR . 'phpswitch_status';
    }

    public function setState(int $state): void
    {
        $this->state = $state;
        $stateFile = $this->getStateFile();
        file_put_contents($stateFile, $state);
    }

    public function getState(): int
    {
        if ($this->state) {
            return $this->state;
        }

        $path = $this->getStateFilne();
        if (file_exists($path)) {
            $this->state = intval(file_get_contents($path)) || self::STATE_NONE;
            return $this->state;
        }

        return self::STATE_NONE;
    }

    public function __call($m, $a)
    {
        return call_user_func_array([$this->settings, $m], $a);
    }
}
