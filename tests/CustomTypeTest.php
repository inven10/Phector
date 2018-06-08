<?php
declare(strict_types=1);

namespace Phector\Tests;

use Faker\Factory;

use Phector\Repo;
use Phector\Schema;

use Phector\Tests\DB;
use Phector\Tests\Populator;
use Phector\Tests\Struct\CustomTypeEntity;

final class CustomTypeTest extends \PHPUnit\Framework\TestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        self::$repo = DB::repo();

        $tableName = Schema::create(CustomTypeEntity::getSchema())->getTable();
        $builder = self::$repo->schemaBuilder();

        if ($builder->hasTable($tableName)) {
            $builder->drop($tableName);
        }

        $builder->create(
            'custom_type_entities',
            function ($table) {
                $table->uuid('id')->primary();
                $table->string('body');
            }
        );

        $populator = new Populator(
            Factory::create(),
            self::$repo
        );

        $populator->addEntity(CustomTypeEntity::class, 2);
        $populator->execute();
    }

    /**
     * Proof that custom type works
     *
     * @group core
     * @group positive
     * @test
     */
    public function customTypeWorks()
    {
        $mapper = self::$repo->entityMapper(CustomTypeEntity::class);

        $entity = $mapper->first();

        $this->assertEquals('MEOW', $entity->body);
    }
}
