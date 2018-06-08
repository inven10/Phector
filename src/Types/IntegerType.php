<?php
declare(strict_types=1);

namespace Phector\Types;

use Phector\Types\TypeInterface;

final class IntegerType implements TypeInterface
{
    public static function load($field)
    {
        return (int) $field;
    }

    public static function dump($field)
    {
        return (int) $field;
    }
}
