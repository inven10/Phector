<?php
declare(strict_types=1);

namespace Phector\Tests;

use Faker\Factory;

use Phector\Repo;
use Phector\Schema;

use Phector\Tests\DB;
use Phector\Tests\Populator;
use Phector\Tests\Struct\GrandEntity;

final class TypeTest extends \PHPUnit\Framework\TestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        self::$repo = DB::repo();

        $tableName = Schema::create(GrandEntity::getSchema())->getTable();
        $builder = self::$repo->schemaBuilder();

        if ($builder->hasTable($tableName)) {
            $builder->drop($tableName);
        }

        $builder->create(
            'grand_entities',
            function ($table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->date('date');
                $table->json('json');
                $table->boolean('boolean');
                $table->integer('integer');
                $table->float('float');
            }
        );

        $populator = new Populator(
            Factory::create(),
            self::$repo
        );

        $populator->addEntity(GrandEntity::class, 2);
        $populator->execute();
    }

    /**
     * Proof that all base types work
     *
     * @group core
     * @group positive
     * @test
     */
    public function baseTypesShouldWork()
    {
        $mapper = self::$repo->entityMapper(GrandEntity::class);

        $entity = $mapper->first();

        $this->assertNotNull($entity);
    }
}
