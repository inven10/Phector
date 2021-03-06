<?php
declare(strict_types=1);

namespace Phector\Tests;

use Faker\Factory;

use Phector\Repo;
use Phector\Schema;

use Phector\Tests\DB;
use Phector\Tests\Populator;
use Phector\Tests\Struct\CoreEntity;

final class CoreTest extends \PHPUnit\Framework\TestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        self::$repo = DB::repo();

        $tableName = Schema::create(CoreEntity::getSchema())->getTable();
        $builder = self::$repo->schemaBuilder();

        if ($builder->hasTable($tableName)) {
            $builder->drop($tableName);
        }

        $builder->create(
            'core_entities',
            function ($table) {
                $table->uuid('id')->primary();
                $table->string('code');
                $table->string('name');
            }
        );

        $populator = new Populator(
            Factory::create(),
            self::$repo
        );

        $populator->addEntity(CoreEntity::class, 5);
        $populator->execute();
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
        $mapper = self::$repo->entityMapper(CoreEntity::class);

        $entities = $mapper->get();

        $this->assertNotEmpty($entities);
        $this->assertContainsOnlyInstancesOf(CoreEntity::class, $entities);

        $fieldNames = $mapper->getSchema()->fieldNames();

        foreach ($entities as $entity) {
            $record = $entity->toRecord($entity);

            $this->assertArraySubset(array_keys($record), $fieldNames);
            $this->assertNotContains(null, array_values($record));
        }

        $repeatEntities = $mapper->get();

        $this->assertNotEmpty($repeatEntities);
    }

    /**
     * Proof that insert works with help from Faker.
     *
     * Again the database fixture proves this may just redundant.
     *
     * @group core
     * @group positive
     * @test
     */
    public function insertShouldWork()
    {
        $populator = new Populator(
            Factory::create(),
            self::$repo
        );

        $populator->addEntity(CoreEntity::class, 3);
        $insertedEntities = $populator->execute();

        $entities = [];
        foreach ($insertedEntities as $insertedEntities) {
            foreach ($insertedEntities as $entity) {
                $entities[] = $entity;
            }
        }

        $mapper = self::$repo->entityMapper(CoreEntity::class);

        foreach ($entities as $entity) {
             $this->assertNotEmpty($mapper->find($entity->id));
        }
    }

    /**
     * Proof that update works.
     *
     * @group core
     * @group positive
     * @test
     */
    public function updateShouldWork()
    {
        $populator = new Populator(
            Factory::create(),
            self::$repo
        );

        $populator->addEntity(CoreEntity::class, 2);
        $insertedEntities = $populator->execute();

        $entities = [];
        foreach ($insertedEntities as $insertedEntities) {
            foreach ($insertedEntities as $entity) {
                $entities[] = $entity;
            }
        }

        $mapper = self::$repo->entityMapper(CoreEntity::class);

        $firstEntity = $entities[0];
        $secondEntity = $entities[1];

        $secondEntityRecord = CoreEntity::toRecord($secondEntity);

        $firstPreUpdatedEntity = CoreEntity::merge(
            $firstEntity,
            $secondEntityRecord
        );
        $firstPreUpdatedEntity->id = $firstEntity->id;
        $firstUpdatedEntity = $mapper->update($firstPreUpdatedEntity);

        $this->assertEquals($firstEntity->id, $firstUpdatedEntity->id);
        $this->assertNotEquals($firstEntity->code, $firstUpdatedEntity->code);

        $firstEntityRecord = CoreEntity::toRecord($firstEntity);

        $secondPreUpdatedEntity = CoreEntity::merge(
            $secondEntity,
            $firstEntityRecord
        );
        $secondPreUpdatedEntity->id = $secondEntity->id;
        $secondUpdatedEntity= $mapper->update($secondPreUpdatedEntity);

        $this->assertEquals($secondEntity->id, $secondUpdatedEntity->id);
        $this->assertNotEquals($secondEntity->code, $secondUpdatedEntity->code);
    }

    /**
     * Proof that delete works.
     *
     * @group core
     * @group positive
     * @test
     */
    public function deleteShouldWork()
    {
        $populator = new Populator(
            Factory::create(),
            self::$repo
        );

        $populator->addEntity(CoreEntity::class, 3);
        $insertedEntities = $populator->execute();

        $entities = [];
        foreach ($insertedEntities as $insertedEntities) {
            foreach ($insertedEntities as $entity) {
                $entities[] = $entity;
            }
        }

        $mapper = self::$repo->entityMapper(CoreEntity::class);

        foreach($entities as $entity)
        {
            $deletedEntity = $mapper->delete($entity);
            $this->assertEquals($deletedEntity->id, $entity->id);
            $this->assertEmpty($mapper->find($entity->id));
        }
    }
}
