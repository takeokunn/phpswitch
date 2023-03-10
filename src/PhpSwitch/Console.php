<?php

namespace PhpSwitch;

use Exception;
use BadMethodCallException;
use GetOptionKit\OptionCollection;
use CLIFramework\Application;
use CLIFramework\Exception\{ CommandArgumentNotEnoughException, CommandNotFoundException };
use CLIFramework\ExceptionPrinter\{ DevelopmentExceptionPrinter, ProductionExceptionPrinter };
use PhpSwitch\Exception\SystemCommandException;

class Console extends Application
{
    final const NAME = 'phpswitch';
    final const VERSION = '2.0.0';

    /**
     * @param OptionCollection $opts
     */
    public function options($opts): void
    {
        parent::options($opts);
        $opts->add('no-progress', 'Do not display progress bar.');
    }

    public function init(): void
    {
        parent::init();

        $this->command('init');
        $this->command('known');
        $this->command('install');
        $this->command('list');
        $this->command('use');
        $this->command('switch');
        $this->command('each');

        $this->command('config');
        $this->command('info');
        $this->command('env');
        $this->command('extension');
        $this->command('variants');
        $this->command('path');
        $this->command('cd');
        $this->command('download');
        $this->command('clean');
        $this->command('update');
        $this->command('ctags');
        $this->command('help');

        $this->command('fpm');

        $this->command('list-ini', \PhpSwitch\Command\ListIniCommand::class);
        $this->command('self-update', \PhpSwitch\Command\SelfUpdateCommand::class);

        $this->command('remove');
        $this->command('purge');

        $this->command('off');
        $this->command('switch-off', \PhpSwitch\Command\SwitchOffCommand::class);

        $this->command('system');
        $this->command('system-off');

        $this->configure();

        // We use '#' as the prefix to prevent issue with bash
        if (!extension_loaded('json')) {
            $this->logger->warn('# WARNING: json extension is required for parsing release info.');
        }
        if (!extension_loaded('libxml')) {
            $this->logger->warn('# WARNING: libxml extension is required for parsing pecl package file.');
        }
        if (!extension_loaded('ctype')) {
            $this->logger->warn('# WARNING: ctype extension might be required for parsing yaml file.');
        }
    }

    public function configure(): void
    {
        // avoid warnings when web scraping possible malformed HTML from pecl
        if (extension_loaded('libxml')) {
            libxml_use_internal_errors(true);
        }
        // prevent execution time limit fatal error
        set_time_limit(0);

        // prevent warnings when timezone is not set
        date_default_timezone_set(Utils::readTimeZone() ?: 'America/Los_Angeles');

        // fix bold output so it looks good on light and dark terminals
        $this->getFormatter()->addStyle('bold', ['bold' => 1]);

        $this->logger->levelStyles['warn'] = 'yellow';
        $this->logger->levelStyles['error'] = 'red';
    }

    public function brief(): string
    {
        return 'brew your latest php!';
    }

    /**
     * @param array<mixed> $argv
     */
    public function runWithTry(array $argv): bool
    {
        try {
            return $this->run($argv);
        } catch (CommandArgumentNotEnoughException $e) {
            $this->logger->error($e->getMessage());
            $this->logger->writeln('Expected argument prototypes:');
            $this->logger->writeln("\t" . $e->getCommand()->getAllCommandPrototype());
            $this->logger->newline();
        } catch (CommandNotFoundException $e) {
            $this->logger->error(
                $e->getMessage()
                . ' available commands are: '
                . implode(', ', $e->getCommand()->getVisibleCommandList())
            );
            $this->logger->newline();

            $this->logger->writeln('Please try the command below to see the details:');
            $this->logger->newline();
            $this->logger->writeln("\t" . $this->getProgramName() . ' help ');
            $this->logger->newline();
        } catch (SystemCommandException $e) {
            // Todo: detect $lastline for library missing here...

            $logFile = $e->getLogFile();
            $this->logger->error('Error: ' . trim($e->getMessage()));

            if ($logFile !== null && file_exists($logFile)) {
                $this->logger->error('The last 5 lines in the log file:');
                $lines = array_slice(file($logFile), -5);
                foreach ($lines as $line) {
                    echo $line , PHP_EOL;
                }
                $this->logger->error('Please checkout the build log file for more details:');
                $this->logger->error("\t tail $logFile");
            }
        } catch (BadMethodCallException $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error('Seems like an application logic error, please contact the developer.');
        } catch (Exception $e) {
            if ($this->options && $this->options->debug) {
                $printer = new DevelopmentExceptionPrinter($this->getLogger());
                $printer->dump($e);
            } else {
                $printer = new ProductionExceptionPrinter($this->getLogger());
                $printer->dump($e);
            }
        }

        return false;
    }
}
