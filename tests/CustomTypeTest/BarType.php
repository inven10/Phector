<?php
declare(strict_types=1);

namespace Phector\Tests\CustomTypeTest;

use Phector\Types\TypeInterface;

final class BarType implements TypeInterface
{
    public static function load($field)
    {
        return "Bar";
    }
    public static function dump($field)
    {
        return "Bar";
    }
}
