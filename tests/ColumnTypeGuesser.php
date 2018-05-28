<?php
declare(strict_types=1);

namespace Phector\Tests;

use Faker\Generator;

use Phector\Field;
use Phector\Types\StringType;

final class ColumnTypeGuesser
{
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function guessFormat(Field $field)
    {
        $generator = $this->generator;
        $type = $field->getType();
        switch ($type) {
        case 'boolean':
            return function () use ($generator) {
                    return $generator->boolean;
            };
            case 'decimal':
                $size = isset($field['precision']) ? $field['precision'] : 2;

                return function () use ($generator, $size) {
                    return $generator->randomNumber($size + 2) / 100;
                };
            case 'smallint':
                return function () use ($generator) {
                    return $generator->numberBetween(0, 65535);
                };
            case 'integer':
                return function () use ($generator) {
                    return $generator->numberBetween(0, 2147483647);
                };
            case 'bigint':
                return function () use ($generator) {
                    return $generator->numberBetween(0, 18446744073709551615);
                };
            case 'float':
                return function () use ($generator) {
                    return $generator->randomFloat(null, 0, 4294967295);
                };
            case 'string':
            case StringType::class:
                // TODO: More consideration
                $size = 25;

                return function () use ($generator, $size) {
                    return $generator->text($size);
                };
            case 'text':
                return function () use ($generator) {
                    return $generator->text;
                };
            case 'datetime':
            case 'date':
            case 'time':
                return function () use ($generator) {
                    return $generator->datetime;
                };
            case 'uuid':
                return function () use ($generator) {
                    $uuid = md5(uniqid());
                    $uuid = substr_replace($uuid, '-', 8, 0);
                    $uuid = substr_replace($uuid, '-', 13, 0);
                    $uuid = substr_replace($uuid, '-', 18, 0);
                    $uuid = substr_replace($uuid, '-', 23, 0);

                    return $uuid;
                };
            default:
                // no smart way to guess what the user expects here
                return null;
        }
    }
}
