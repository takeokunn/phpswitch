<?php

namespace PhpSwitch\Extension;

use CLIFramework\Logger;
use Exception;
use PhpSwitch\Config;
use PhpSwitch\Tasks\MakeTask;
use PhpSwitch\Utils;

class ExtensionManager
{
    public $logger;

    /**
     * Map of extensions that can't be enabled at the same time.
     * This helps phpswitch to unload antagonist extensions before enabling
     * an extension with a known conflict.
     *
     * @var array
     */
    protected $conflicts = [
        'json' => ['jsonc'],
        // enabling jsonc disables json
        'jsonc' => ['json'],
    ];

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function purgeExtension(Extension $extension)
    {
        if ($path = $extension->getSourceDirectory()) {
            $currentPhpExtensionDirectory = Config::getBuildDir() . '/' . Config::getCurrentPhpName() . '/ext';
            $extName = $extension->getExtensionName();
            $extensionDir = $currentPhpExtensionDirectory . DIRECTORY_SEPARATOR . $extName;
            if (file_exists($extensionDir)) {
                Utils::system("rm -rvf $extensionDir");
            }
        }
    }

    public function cleanExtension(Extension $extension)
    {
        $makeTask = new MakeTask($this->logger);
        $makeTask->setQuiet();
        $code = !is_dir($path = $extension->getSourceDirectory()) ||
                !$extension->isBuildable() ||
                !$makeTask->clean($extension);

        if ($code != 0) {
            $this->logger->error("Could not clean extension: {$extension->getName()}.");
        }

        return $code == 0;
    }

    /**
     * Whenever you call this method, you shall have already downloaded the extension
     * And have set the source directory on the Extension object.
     */
    public function installExtension(Extension $extension, array $options = [])
    {
        $this->disableExtension($extension);

        $path = $extension->getSourceDirectory();
        $name = $extension->getName();

        if (!file_exists($path)) {
            throw new Exception("Source directory $path does not exist.");
        }

        // Install local extension
        $extensionInstaller = new ExtensionInstaller($this->logger);
        $this->logger->info("===> Installing {$name} extension...");
        $this->logger->debug("Extension path $path");
        // $installer->runInstall($name, $sourceDir, $options);
        $extensionInstaller->install($extension, $options);

        $this->createExtensionConfig($extension);
        $this->enableExtension($extension);
        $this->logger->info('Done.');

        return $path;
    }

    public function createExtensionConfig(Extension $extension)
    {
        $ini = $extension->getConfigFilePath();
        $this->logger->info("===> Creating config file {$ini}");

        if (!file_exists(dirname((string) $ini))) {
            if (!mkdir($concurrentDirectory = dirname((string) $ini), 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        if (file_exists($ini)) {
            return true;
        }

        // @see https://github.com/php/php-src/commit/0def1ca59a
        $content = sprintf(
            '%s=%s' . PHP_EOL,
            $extension->isZend() ? 'zend_extension' : 'extension',
            $extension->isZend() && PHP_VERSION_ID < 50500
                ? $extension->getSharedLibraryPath()
                : $extension->getSharedLibraryName()
        );

        // create extension config file
        if (file_put_contents($ini, $content) === false) {
            return false;
        }

        $this->logger->debug("{$ini} is created.");

        return true;
    }

    public function disable($extensionName, $sapi = null)
    {
        $ext = ExtensionFactory::lookup($extensionName);
        if (!$ext) {
            $ext = ExtensionFactory::lookupRecursive($extensionName);
        }
        if ($ext) {
            return $this->disableExtension($ext, $sapi);
        } else {
            $this->logger->info("{$extensionName} extension is not installed. ");
        }
    }

    public function enable($extensionName, $sapi = null)
    {
        $ext = ExtensionFactory::lookup($extensionName);
        if (!$ext) {
            $ext = ExtensionFactory::lookupRecursive($extensionName);
        }
        if ($ext) {
            return $this->enableExtension($ext, $sapi);
        } else {
            $this->logger->info("{$extensionName} extension is not installed. ");
        }
    }

    /**
     * Enables ini file for current extension.
     *
     * @return bool
     */
    public function enableExtension(Extension $extension, $sapi = null)
    {
        $name = $extension->getExtensionName();
        $this->logger->info("===> Enabling extension $name");

        if ($sapi) {
            return $this->enableSapiExtension($extension, $name, $sapi, true);
        }

        $first = true;
        $result = true;
        foreach (Config::getSapis() as $availableSapi) {
            $result = $result && $this->enableSapiExtension($extension, $name, $availableSapi, $first);
            $first = false;
        }

        return $result;
    }

    private function enableSapiExtension(Extension $extension, $name, $sapi, $first = false)
    {
        $default_file = $extension->getConfigFilePath();
        $enabled_file = $extension->getConfigFilePath($sapi);
        if (file_exists($enabled_file) && ($extension->isLoaded() && !$this->hasConflicts($extension))) {
            $this->logger->info("[*] {$name} extension is already enabled for SAPI {$sapi}.");

            return true;
        }

        if (
            $first
            && !file_exists($default_file)
            && !(file_exists($extension->getSharedLibraryPath())
            && $this->createExtensionConfig($extension))
        ) {
            $this->logger->info("{$name} extension is not installed. Suggestions:");
            $this->logger->info("\t\$ phpswitch ext install {$name}");

            return false;
        }

        if (!file_exists(dirname((string) $enabled_file))) {
            return true;
        }

        $this->disableAntagonists($extension, $sapi);

        $disabled_file = $enabled_file . '.disabled';
        if (file_exists($disabled_file)) {
            if (!rename($disabled_file, $enabled_file)) {
                $this->logger->warning("failed to re-enable {$name} extension for SAPI {$sapi}.");

                return false;
            }

            $this->logger->info("[*] {$name} extension is re-enabled for SAPI {$sapi}.");
            return true;
        }

        if (!copy($default_file, $enabled_file)) {
            $this->logger->warning("failed to enable {$name} extension for SAPI {$sapi}.");

            return false;
        }

        $this->logger->info("[*] {$name} extension is enabled for SAPI {$sapi}.");

        return true;
    }

    /**
     * Disables ini file for current extension.
     *
     * @return bool
     */
    public function disableExtension(Extension $extension, $sapi = null)
    {
        $name = $extension->getExtensionName();

        if (null !== $sapi) {
            return $this->disableSapiExtension($extension->getConfigFilePath($sapi), $name, $sapi);
        }

        $result = true;
        foreach (Config::getSapis() as $availableSapi) {
            $result = $result && $this->disableSapiExtension($extension->getConfigFilePath($availableSapi), $name, $sapi);
        }

        return $result;
    }

    private function disableSapiExtension($extension_file, $name, $sapi)
    {
        if (!file_exists(dirname((string) $extension_file))) {
            return true;
        }

        if (!file_exists($extension_file)) {
            $this->logger->info("[ ] {$name} extension is already disabled for SAPI {$sapi}.");

            return true;
        }

        if (file_exists($extension_file)) {
            if (rename($extension_file, $extension_file . '.disabled')) {
                $this->logger->info("[ ] {$name} extension is disabled for SAPI {$sapi}.");

                return true;
            }
            $this->logger->warning("failed to disable {$name} extension for SAPI {$sapi}.");
        }

        return false;
    }

    /**
     * Disable extensions known to conflict with current one.
     */
    public function disableAntagonists(Extension $extension, $sapi = null)
    {
        $name = $extension->getName();
        if (isset($this->conflicts[$name])) {
            $conflicts = $this->conflicts[$name];
            $this->logger->info('===> Applying conflicts resolution (' . implode(', ', $conflicts) . '):');
            foreach ($conflicts as $conflict) {
                $extension = ExtensionFactory::lookup($conflict);
                $this->disableExtension($extension, $sapi);
            }
        }
    }

    public function hasConflicts(Extension $extension)
    {
        return array_key_exists($extension->getName(), $this->conflicts);
    }
}
