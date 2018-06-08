<?php
declare(strict_types=1);

namespace Phector\Types;

use Phector\Types\TypeInterface;

final class FloatType implements TypeInterface
{
    public static function load($field)
    {
        return (float) $field;
    }

    public static function dump($field)
    {
        return (float) $field;
    }
}
