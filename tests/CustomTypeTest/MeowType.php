<?php
declare(strict_types=1);

namespace Phector\Tests\CustomTypeTest;

use Phector\Types\TypeInterface;

final class MeowType implements TypeInterface
{
    public static function get($field)
    {
        return "MEOW";
    }
    public static function set($field)
    {
        return "MEOW";
    }
}
