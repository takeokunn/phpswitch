<?php

namespace PhpSwitch\Tasks;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;

abstract class BaseTask
{
    /**
     * @var Logger
     */
    public $logger;

    public $options;

    public $startedAt;

    public $finishedAt;

    public function __construct(Logger $logger, OptionResult $optionResult = null)
    {
        $this->startedAt = microtime(true);
        $this->logger = $logger;
        if ($optionResult) {
            $this->options = $optionResult;
        } else {
            $this->options = new OptionResult();
        }
    }

    public function info($msg)
    {
        if ($this->logger) {
            $this->logger->info($msg);
        }
    }

    public function debug($msg)
    {
        if ($this->logger) {
            $this->logger->debug($msg);
        }
    }

    public function __destruct()
    {
        $this->finishedAt = microtime(true);
    }
}
