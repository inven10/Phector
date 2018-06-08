<?php
declare(strict_types=1);

namespace Phector\Types;

interface TypeInterface
{
    public static function load($field);
    public static function dump($field);
}
