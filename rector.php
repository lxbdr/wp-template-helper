<?php
// rector.php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    // Register sets of rules
    $rectorConfig->sets([
        SetList::PHP_74,
        SetList::PHP_80,
        SetList::PHP_81,
        SetList::PHP_82,
    ]);
};
