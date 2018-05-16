<?php
declare(strict_types=1);

namespace Phector\Types;

use Phector\Types\TypeInterface;

final class StringType implements TypeInterface
{
    public static function get($field)
    {
        return $field;
    }
    public static function set($field)
    {
        return $field;
    }
}
