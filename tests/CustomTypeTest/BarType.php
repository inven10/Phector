<?php
declare(strict_types=1);

namespace Phector\Tests\CustomTypeTest;

use Phector\Types\TypeInterface;

final class BarType implements TypeInterface
{
    public static function get($field)
    {
        return "Bar";
    }
    public static function set($field)
    {
        return "Bar";
    }
}
