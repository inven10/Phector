<?php
declare(strict_types=1);

namespace Phector\Tests\Struct;

use Phector\Entity;
use Phector\MappedEntity;
use Phector\Types\StringType;

final class LeftEntity extends \Spruct\Struct implements MappedEntity
{
    use Entity;

    protected $id;
    protected $title;
    protected $author;

    public static function getSchema() : array
    {
        return [
            'table' => 'left_entities',
            'fields' => [
                'id' => [
                    'type' => StringType::class,
                    'primary' => true,
                    'default' => function () {
                        $uuid = md5(uniqid());
                        $uuid = substr_replace($uuid, '-', 8, 0);
                        $uuid = substr_replace($uuid, '-', 13, 0);
                        $uuid = substr_replace($uuid, '-', 18, 0);
                        $uuid = substr_replace($uuid, '-', 23, 0);

                        return $uuid;
                    }],
                'title' => [
                    'type' => StringType::class
                ],
                'author' => [
                    'type' => StringType::class
                ]
            ]
        ];
    }
}
