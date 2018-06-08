<?php
declare(strict_types=1);

namespace Phector\Tests\CustomTypeTest;

use Phector\Types\TypeInterface;

final class FooType implements TypeInterface
{
    public static function get($field)
    {
        return "Foo";
    }
    public static function set($field)
    {
        return "Foo";
    }
}
