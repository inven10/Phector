<?php
declare(strict_types=1);

namespace Phector\Types;

interface TypeInterface
{
    public static function set($field);
    public static function get($field);
}
