<?php

declare(strict_types=1);

namespace PhpSwitch\Exception;

use Exception;

final class OopsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Oops, report this issue on GitHub? https://github.com/takeokunn/phpswitch ');
    }
}
