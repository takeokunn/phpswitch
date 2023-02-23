<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\{ SetList, LevelSetList };

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ]);

    // define sets of rules
    $rectorConfig->sets([
        SetList::ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION,
        SetList::NAMING,
        SetList::PHP_81,
        SetList::PSR_4,
        LevelSetList::UP_TO_PHP_81,
    ]);

    // $rectorConfig->importNames();
    $rectorConfig->parallel(360);
};
