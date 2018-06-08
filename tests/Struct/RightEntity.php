<?php
declare(strict_types=1);

namespace Phector\Tests\Struct;

use Phector\Entity;
use Phector\MappedEntity;
use Phector\Types\StringType;

final class RightEntity extends \Spruct\Struct implements MappedEntity
{
    use Entity;

    protected $id;
    protected $model;
    protected $make;
    protected $variant;

    public static function getSchema() : array
    {
        return [
            'table' => 'right_entities',
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
                'model' => [
                    'type' => 'string'
                ],
                'make' => [
                    'type' => 'string'
                ],
                'variant' => [
                    'type' => 'string'
                ]
            ]
        ];
    }
}
