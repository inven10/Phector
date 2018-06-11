<?php
declare(strict_types=1);

namespace Phector\Tests\Struct;

use Phector\Entity;
use Phector\MappedEntity;

final class GrandEntity extends \Spruct\Struct implements MappedEntity
{
    use Entity;

    protected $id;
    protected $name;
    protected $date;
    protected $json;
    protected $boolean;
    protected $integer;
    protected $float;

    public static function getSchema() : array
    {
        return [
            'table' => 'grand_entities',
            'fields' => [
                'id' => [
                    'type' => 'uuid',
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
                'date' => [
                    'type' => 'date'
                ],
                'json' => [
                    'type' => 'json'
                ],
                'boolean' => [
                    'type' => 'boolean'
                ],
                'integer' => [
                    'type' => 'integer'
                ],
                'float' => [
                    'type' => 'float'
                ],
            ]
        ];
    }
}
