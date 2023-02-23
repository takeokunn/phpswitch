<?php

namespace PhpSwitch\Exception;

use PhpSwitch\Buildable;
use RuntimeException;

class SystemCommandException extends RuntimeException
{
    protected $build;

    public function __construct($message, Buildable $buildable = null, protected $logFile = null)
    {
        parent::__construct($message);
        $this->build = $buildable;
    }

    public function getLogFile()
    {
        if ($this->logFile) {
            return $this->logFile;
        } elseif ($this->build) {
            return $this->build->getBuildLogPath();
        }
    }
}
