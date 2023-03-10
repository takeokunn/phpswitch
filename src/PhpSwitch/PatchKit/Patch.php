<?php

namespace PhpSwitch\PatchKit;

use CLIFramework\Logger;
use PhpSwitch\Buildable;

/**
 * A patch is consist of a bunch of patch rules.
 *
 * The patch rules targets some files and replaces some contents with their own implementation.
 */
abstract class Patch
{
    /**
     * @return string the description for the patch.
     */
    abstract public function desc();

    abstract public function match(Buildable $buildable, Logger $logger);

    /**
     * rules method returns the array of PatchRule class.
     *
     * @return PatchRule[]
     */
    abstract public function rules();

    /**
     * Each patch may implement its own logic to patch the file.
     */
    public function apply(Buildable $buildable, Logger $logger)
    {
        $cnt = 0;
        if ($rules = $this->rules()) {
            // todo: should backup all files in one time (some patch rules have the same file names)
            foreach ($rules as $rule) {
                $rule->backup($buildable, $logger);
            }
            foreach ($rules as $rule) {
                if ($patched = $rule->apply($buildable, $logger)) {
                    $cnt += $patched;
                }
            }
        }

        return $cnt;
    }
}
