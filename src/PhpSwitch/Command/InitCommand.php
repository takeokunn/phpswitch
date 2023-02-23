<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;
use Phar;
use PhpSwitch\Config;

class InitCommand extends Command
{
    public function brief()
    {
        return 'Initialize phpswitch config file.';
    }

    public function options($opts)
    {
        $opts->add(
            'c|config:',
            'The YAML config file which should be copied into phpswitch home.' .
            'The config file is used for creating custom virtual variants. ' .
            'For more details, please see https://github.com/phpswitch/phpswitch/wiki/Setting-up-Configuration'
        )->isa('file');

        $opts->add(
            'root:',
            'Override the default PHPSWITCH_ROOT path setting.' .
            'This option is usually used to load system-wide build pool. ' .
            'e.g. phpswitch init --root=/opt/phpswitch '
        )->isa('dir');
    }

    public function execute()
    {
        // $currentVersion;
        $root = $this->options->root ?: Config::getRoot();
        $home = Config::getHome();
        $buildDir = Config::getBuildDir();
        $installPrefix = Config::getInstallPrefix();
        // $versionBuildPrefix = Config::getVersionInstallPrefix($version);
        // $versionBinPath     = Config::getVersionBinPath($version);

        $this->logger->info("Using root: $root");
        if (!file_exists($root)) {
            mkdir($root, 0755, true);
        }

        $paths = [];
        $paths[] = $home;
        $paths[] = $root;
        $paths[] = $buildDir;
        $paths[] = $installPrefix;
        foreach ($paths as $path) {
            $this->logger->debug("Checking directory {$path}");
            if (!file_exists($path)) {
                $this->logger->debug("Creating directory {$path}");
                mkdir($path, 0755, true);
            } else {
                $this->logger->debug("Directory {$path} is already created.");
            }
        }

        $this->logger->debug('Creating .metadata_never_index to prevent SpotLight indexing');
        $indexFiles = [$root . DIRECTORY_SEPARATOR . '.metadata_never_index', $home . DIRECTORY_SEPARATOR . '.metadata_never_index'];
        foreach ($indexFiles as $indexFile) {
            if (!file_exists($indexFile)) {
                touch($indexFile); // prevent spotlight index here
            }
        }

        if ($configFile = $this->options->{'config'}) {
            if (!file_exists($configFile)) {
                $this->logger->error("config file '$configFile' does not exist.");

                return;
            }
            $this->logger->debug("Using yaml config from '$configFile'");
            copy($configFile, $root . DIRECTORY_SEPARATOR . 'config.yaml');
        }

        $this->logger->writeln($this->formatter->format('Initialization successfully finished!', 'strong_green'));
        $this->logger->writeln(
            $this->formatter->format(
                '<=====================================================>',
                'strong_white'
            )
        );

        // write bashrc script to phpswitch home
        file_put_contents($home . '/bashrc', $this->getBashScriptPath());
        // write phpswitch.fish script to phpswitch home
        file_put_contents($home . '/phpswitch.fish', $this->getFishScriptPath());

        if (str_contains(getenv('SHELL'), 'fish')) {
            $initConfig = <<<EOS
Paste the following line(s) to the end of your ~/.config/fish/config.fish and start a
new shell, phpswitch should be up and fully functional from there:

    source $home/phpswitch.fish
EOS;
        } else {
            $initConfig = <<<EOS
Paste the following line(s) to the end of your ~/.bashrc and start a
new shell, phpswitch should be up and fully functional from there:

    source $home/bashrc

To enable PHP version info in your shell prompt, please set PHPSWITCH_SET_PROMPT=1
in your `~/.bashrc` before you source `~/.phpswitch/bashrc`

    export PHPSWITCH_SET_PROMPT=1

To enable .phpswitchrc file searching, please export the following variable:

    export PHPSWITCH_RC_ENABLE=1

EOS;
        }

        echo <<<EOS
Phpswitch environment is initialized, required directories are created under

    $home

$initConfig

For further instructions, simply run `phpswitch` to see the help message.

Enjoy phpswitch at \$HOME!!


EOS;

        $this->logger->writeln(
            $this->formatter->format(
                '<=====================================================>',
                'strong_white'
            )
        );
    }

    protected function getCurrentShellDirectory()
    {
        $path = Phar::running();
        if ($path) {
            $path = $path . DIRECTORY_SEPARATOR . 'shell';
        } else {
            $path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'shell';
        }

        return $path;
    }

    protected function getBashScriptPath()
    {
        $path = $this->getCurrentShellDirectory();

        return file_get_contents($path . DIRECTORY_SEPARATOR . 'bashrc');
    }

    protected function getFishScriptPath()
    {
        $path = $this->getCurrentShellDirectory();

        return file_get_contents($path . DIRECTORY_SEPARATOR . 'phpswitch.fish');
    }
}
