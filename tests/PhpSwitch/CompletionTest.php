<?php

namespace PhpSwitch\Tests;

use PhpSwitch\Testing\CommandTestCase;

class CompletionTest extends CommandTestCase
{
    /**
     * @dataProvider completionProvider
     */
    public function testCompletion($shell)
    {
        $this->expectOutputString(
            file_get_contents(__DIR__ . '/../../completion/' . $shell . '/_phpswitch')
        );

        $this->app->run(['phpswitch', $shell, '--bind', 'phpswitch', '--program', 'phpswitch']);
    }

    public static function completionProvider()
    {
        return ['bash' => ['bash'], 'zsh' => ['zsh']];
    }
}
