<?php
declare(strict_types=1);

namespace Phector\Tests;

use Faker\Factory;

use Phector\Repo;
use Phector\Schema;

use Phector\Tests\DB;
use Phector\Tests\Populator;
use Phector\Tests\Struct\ParentEntity;
use Phector\Tests\Struct\ChildEntity;

final class JoinTest extends \PHPUnit\Framework\TestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        self::$repo = DB::repo();

        $parentTableName = Schema::create(ParentEntity::getSchema())->getTable();
        $childTableName = Schema::create(ChildEntity::getSchema())->getTable();
        $builder = self::$repo->schemaBuilder();

        if ($builder->hasTable($parentTableName)) {
            $builder->drop($parentTableName);
        }

        if($builder->hasTable($childTableName)) {
            $builder->drop($childTableName);
        }

        $builder->create(
            'parent_entities',
            function ($table) {
                $table->uuid('id')->primary();
                $table->string('parent_code');
                $table->string('parent_name');
            }
        );

        $builder->create(
            'child_entities',
            function ($table) {
                $table->uuid('id')->primary();
                $table->string('code');
                $table->string('name');
                $table->uuid('parent_id');
            }
        );

        $populator = new Populator(
            Factory::create(),
            self::$repo
        );

        $populator->addEntity(ParentEntity::class, 2);
        $insertedParentEntities = $populator->execute();

        foreach ($insertedParentEntities as $insertedParentEntities) {
            foreach ($insertedParentEntities as $entity) {
                $populator->addEntity(ChildEntity::class, 2, ['parentId' => $entity->id]);
                $populator->execute();
            }
        }
    }

    /**
     * Proof that the parent child join (or the many association) works
     *
     * @group core
     * @group positive
     * @test
     */
    public function oneToManyShouldWork()
    {
        $mapper = self::$repo->entityMapper(ParentEntity::class);

        $parentEntities= $mapper->preload('children')->join('child_entities', 'child_entities.parent_id', '=', 'parent_entities.id')->get();

        foreach($parentEntities as $parentEntity) {
            $this->assertInstanceOf(ParentEntity::class, $parentEntity);

            $this->assertTrue(is_array($parentEntity->children));
            foreach($parentEntity->children as $childEntity) {
                $this->assertInstanceOf(ChildEntity::class, $childEntity);
                $this->assertEquals($parentEntity->id, $childEntity->parentId);
            }
        }
    }

    /**
     * Proof that the child parent join (or the one association) works
     *
     * @group core
     * @group positive
     * @test
     */
    public function hasOneShouldWork()
    {
        $mapper = self::$repo->entityMapper(ChildEntity::class)->preload('parentEntity')->join('parent_entities', 'parent_entities.id', '=', 'child_entities.parent_id');

        $childEntities = $mapper->get();

        foreach($childEntities as $childEntity) {
            $this->assertInstanceOf(ChildEntity::class, $childEntity);
            $this->assertInstanceOf(ParentEntity::class, $childEntity->parentEntity);
            $this->assertTrue($childEntity->parentEntity->id === $childEntity->parentId);
        }

        $childEntity = $mapper->first();

        $this->assertInstanceOf(ChildEntity::class, $childEntity);
        $this->assertInstanceOf(ParentEntity::class, $childEntity->parentEntity);
        $this->assertTrue($childEntity->parentEntity->id === $childEntity->parentId);

    }
}
