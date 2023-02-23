<?php

namespace PhpSwitch\Tests\Tasks;

class MakeTaskTestBuild implements \PhpSwitch\Buildable
{
    public function getSourceDirectory()
    {
        return __DIR__ . '/../../fixtures/make/';
    }
    public function getBuildLogPath()
    {
        return null;
    }
    public function isBuildable()
    {
        return true;
    }
}
