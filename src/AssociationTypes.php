<?php
declare(strict_types=1);

namespace Phector;

use vijinho\Enums\Enum;

class AssociationTypes extends Enum
{
    protected static $values = [
    'one' => 'one',
    'many' => 'many',
    ];
}
