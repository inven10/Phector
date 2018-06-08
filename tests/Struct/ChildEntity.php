<?php
declare(strict_types=1);

namespace Phector\Tests\Struct;

use Phector\Entity;
use Phector\MappedEntity;
use Phector\Association\AssociationTypes;
use Phector\Tests\Struct\ParentEntity;

final class ChildEntity extends \Spruct\Struct implements MappedEntity
{
    use Entity;

    protected $id;
    protected $name;
    protected $code;
    protected $parentId;
    protected $parentEntity;

    public static function getSchema() : array
    {
        return [
            'table' => 'child_entities',
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
                ],
                'parentId' => [
                'columnName' => 'parent_id',
                'type' => 'string'
                ]
            ],
            'associations' => [
                'parentEntity' => [
                    'type' => AssociationTypes::One(),
                    'entityClass' => ParentEntity::class,
                    'foreignKey' => 'id',
                    'localKey' => 'parent_id'
                ]
            ]
        ];
    }
}
