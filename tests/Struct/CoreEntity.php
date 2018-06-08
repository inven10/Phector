<?php
declare(strict_types=1);

namespace Phector\Tests\Struct;

use Phector\Entity;
use Phector\MappedEntity;
use Phector\Types\StringType;
use Phector\Types\DateType;
use Phector\Types\JsonType;

final class CoreEntity extends \Spruct\Struct implements MappedEntity
{
    use Entity;

    protected $id;
    protected $name;
    protected $code;

    public static function getSchema() : array
    {
        return [
            'table' => 'core_entities',
            'fields' => [
                'id' => [
                    'type' => 'string',
                    'primary' => true,
                    'default' => function () {
                        $uuid = md5(uniqid());
                        $uuid = substr_replace($uuid, '-', 8, 0);
                        $uuid = substr_replace($uuid, '-', 13, 0);
                        $uuid = substr_replace($uuid, '-', 18, 0);
                        $uuid = substr_replace($uuid, '-', 23, 0);

                        return $uuid;
                    }],
                'name' => [
                    'type' => 'string'
                ],
                'code' => [
                    'type' => 'string'
                ]
            ]
        ];
    }
}
