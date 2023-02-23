<?php

declare(strict_types=1);

namespace PhpSwitch\Exception;

use RuntimeException;
use PhpSwitch\Buildable;

final class SystemCommandException extends RuntimeException
{
    public function __construct(
        string $message,
        protected Buildable $build
    ) {
        parent::__construct($message);
    }

    public function getLogFile(): string
    {
        return $this->build->getBuildLogPath();
    }
}
