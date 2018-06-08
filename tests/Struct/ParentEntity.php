<?php
declare(strict_types=1);

namespace Phector\Tests\Struct;

use Phector\Entity;
use Phector\MappedEntity;
use Phector\Association\AssociationTypes;
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
                'parentName' => [
                    'type' => 'string'
                ],
                'parentCode' => [
                    'type' => 'string'
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
