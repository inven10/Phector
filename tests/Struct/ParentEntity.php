<?php
declare(strict_types=1);

namespace Phector\Tests\Struct;

use Phector\Entity;
use Phector\MappedEntity;
use Phector\Types\StringType;
use Phector\AssociationTypes;
use Phector\Types\DateType;
use Phector\Types\JsonType;
use Phector\Tests\Struct\ChildEntity;

final class ParentEntity extends \Spruct\Struct implements MappedEntity
{
    use Entity;

    protected $id;
    protected $parentName;
    protected $parentCode;
    protected $children;

    public static function getSchema() : array
    {
        return [
            'table' => 'parent_entities',
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
                'parentName' => [
                    'type' => StringType::class
                ],
                'parentCode' => [
                    'type' => StringType::class
                ]
            ],
            'associations' => [
                'children' => [
                    'type' => AssociationTypes::Many(),
                    'entityClass' => ChildEntity::class,
                    'foreignKey' => 'parent_id',
            'localKey' => 'id'
                ] 
            ]
        ];
    }
}
