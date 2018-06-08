<?php
declare(strict_types=1);

namespace Phector\Types;

use Phector\Types\TypeInterface;

final class UuidType implements TypeInterface
{
    public static function load($field)
    {
        return $field;
    }
    public static function dump($field)
    {
        return $field;
    }
}
