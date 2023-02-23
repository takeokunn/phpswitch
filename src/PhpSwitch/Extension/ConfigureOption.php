<?php

namespace PhpSwitch\Extension;

class ConfigureOption
{
    public $defaultValue;

    public function __construct(public $option, public $desc, public $valueHint = null)
    {
    }

    public function getOption()
    {
        return $this->option;
    }

    public function getDescription()
    {
        return $this->desc;
    }

    public function getValueHint()
    {
        return $this->valueHint;
    }

    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }
}
