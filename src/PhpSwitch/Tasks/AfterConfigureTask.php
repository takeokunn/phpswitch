<?php

namespace PhpSwitch\Tasks;

use PhpSwitch\Build;
use PhpSwitch\Patches\IntlWith64bitPatch;
use PhpSwitch\Patches\OpenSSLDSOPatch;
use PhpSwitch\Patches\PHP56WithOpenSSL11Patch;

/**
 * Task run before 'configure'.
 */
class AfterConfigureTask extends BaseTask
{
    public function run(Build $build)
    {
        if (!$this->options->{'no-patch'}) {
            $this->logger->info('===> Checking patches...');
            $patches = array();
            $patches[] = new IntlWith64bitPatch();
            $patches[] = new OpenSSLDSOPatch();
            $patches[] = new PHP56WithOpenSSL11Patch();
            foreach ($patches as $patch) {
                $this->logger->info('Checking patch for ' . $patch->desc());
                if ($patch->match($build, $this->logger)) {
                    $patched = $patch->apply($build, $this->logger);
                    $this->logger->info("$patched changes patched.");
                }
            }
        }
    }
}
