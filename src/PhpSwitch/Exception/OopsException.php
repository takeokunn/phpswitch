<?php

namespace PhpSwitch\Exception;

use Exception;

class OopsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Oops, report this issue on GitHub? http://github.com/takeokunn/phpswitch ');
    }
}
