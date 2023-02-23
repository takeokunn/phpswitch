<?php

namespace PhpSwitch\PatchKit;

use CLIFramework\Logger;
use PhpSwitch\Buildable;

/**
 * DiffPatchRule implements a diff based patch rule.
 */
final class DiffPatchRule implements PatchRule
{
    /**
     * @var string
     */
    private $patch;

    private int $strip = 0;

    private function __construct()
    {
    }

    /**
     * @param int $level
     *
     * @return $this
     */
    public function strip($level)
    {
        $this->strip = $level;

        return $this;
    }

    /**
     * @param string $patch The path contents
     */
    public static function fromPatch($patch)
    {
        $self = new self();
        $self->patch = $patch;

        return $self;
    }

    public function backup(Buildable $buildable, Logger $logger)
    {
    }

    public function apply(Buildable $buildable, Logger $logger)
    {
        $logger->info('---> Applying patch...');

        $process = proc_open(
            sprintf('patch --forward --backup -p%d', $this->strip),
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes,
            $buildable->getSourceDirectory()
        );

        if (fwrite($pipes[0], $this->patch) === false) {
            return 0;
        }

        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);

        if ($output !== '') {
            $logger->info($output);
        }

        $error = stream_get_contents($pipes[2]);

        if ($error !== '') {
            $logger->error($error);
        }

        if (proc_close($process) !== 0) {
            $logger->error('Patch failed');

            return 0;
        }

        $logger->info('Done.');

        return 1;
    }
}
