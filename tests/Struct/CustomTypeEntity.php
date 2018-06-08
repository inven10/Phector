<?php
declare(strict_types=1);

namespace Phector\Tests\Struct;

use Phector\Entity;
use Phector\MappedEntity;

final class CustomTypeEntity extends \Spruct\Struct implements MappedEntity
{
    use Entity;

    protected $id;
    protected $body;

    public static function getSchema() : array
    {
        return [
            'table' => 'custom_type_entities',
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
                'body' => [
                    'type' => 'foo'
                ]
            ]
        ];
    }
}
