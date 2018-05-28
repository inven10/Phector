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
        $builder = DB::schemaBuilder();

        if ($builder->hasTable($parentTableName) || $builder->hasTable($childTableName)) {
            $builder->drop($parentTableName);
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
     * Proof that getting started works.
     *
     * However, the database fixture requires that build and insert
     * already work so this test may be slightly redundant.
     *
     * @group core
     * @group positive
     * @test
     */
    public function mapperShouldWork()
    {
        $mapper = self::$repo->entityMapper(ParentEntity::class);

        $aliases = [
        'child_entities' => [
        'id' => 'child_id',
        'name' => 'child_name',
        'code' => 'child_code',
        'parent_id'
        ],
        'parent_entities' => [

        ]
        ];

        $entities = $mapper->join('child_entities', 'parent_entities.id', '=', 'child_entities.parent_id')->select(['*'])->get();
        //$entities = $mapper->get();
        //var_dump($entities);

    }
}
