<?php
declare(strict_types=1);

namespace Phector\Types;

use Phector\Types\TypeInterface;

final class BooleanType implements TypeInterface
{
    public static function load($field)
    {
        return (bool) $field;
    }

    public static function dump($field)
    {
        return (bool) $field ? 1 : 0;
    }
}
