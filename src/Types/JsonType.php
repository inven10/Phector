<?php
declare(strict_types=1);

namespace Phector\Types;

use Phector\Types\TypeInterface;

final class JsonType implements TypeInterface
{
    public static function load($field)
    {
        return json_decode($field, true);
    }

    public static function dump($field)
    {
        return json_encode($field);
    }
}
