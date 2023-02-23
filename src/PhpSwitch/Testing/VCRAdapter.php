<?php

namespace PhpSwitch\Testing;

use VCR\VCR;

class VCRAdapter
{
    public static function enableVCR($testInstance)
    {
        VCR::turnOn();
        VCR::insertCassette(self::getVCRCassetteName($testInstance));
    }

    public static function disableVCR()
    {
        VCR::eject();
        VCR::turnOff();
    }

    protected static function getVCRCassetteName($testInstance)
    {
        $classname_parts = explode('\\', (string) $testInstance::class);

        return join('/', array_slice($classname_parts, -2, 2));
    }
}
