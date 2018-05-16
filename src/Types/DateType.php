<?php
declare(strict_types=1);

namespace Phector\Types;

use Phector\Types\TypeInterface;

final class DateType implements TypeInterface
{
    public static function get($field)
    {
        return new \DateTime($field);
    }
    public static function set($field)
    {
        $stringForm = $field->format('Y-m-d H:i:s');

        if($stringForm) {
            return $stringForm;
        } else {
            return "0000-00-00 00:00:00";
        }
    }
}
