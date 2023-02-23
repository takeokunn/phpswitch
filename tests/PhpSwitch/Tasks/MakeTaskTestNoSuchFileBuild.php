<?php

namespace PhpSwitch\Tests\Tasks;

class MakeTaskTestNoSuchFileBuild implements \PhpSwitch\Buildable
{
    public function getSourceDirectory()
    {
        return __DIR__ . '/../../fixtures/make/dummy';
    }
    public function getBuildLogPath()
    {
        return null;
    }
    public function isBuildable()
    {
        return false;
    }
}
