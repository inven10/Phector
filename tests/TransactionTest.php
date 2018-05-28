<?php
declare(strict_types=1);

namespace Phector\Tests;

use Faker\Factory;

use Phector\Repo;
use Phector\Schema;

use Phector\Tests\DB;
use Phector\Tests\Populator;
use Phector\Tests\Struct\LeftEntity;
use Phector\Tests\Struct\RightEntity;

final class TransactionTest extends \PHPUnit\Framework\TestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        self::$repo = DB::repo();

        $leftTableName = Schema::create(LeftEntity::getSchema())->getTable();
        $rightTableName = Schema::create(RightEntity::getSchema())->getTable();
        $builder = self::$repo->schemaBuilder();

        if ($builder->hasTable($leftTableName)) {
            $builder->drop($leftTableName);
        }

        if ($builder->hasTable($rightTableName)) {
            $builder->drop($rightTableName);
        }

        $builder->create(
            'left_entities',
            function ($table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->string('author');
            }
        );

        $builder->create(
            'right_entities',
            function ($table) {
                $table->uuid('id')->primary();
                $table->string('model');
                $table->string('make');
                $table->string('variant');
            }
        );
    }

    /**
     * Proof that beginTransaction, rollback, commit safely works.
     *
     * @group transaction
     * @group smoke
     * @test
     */
    public function interfaceShouldWork()
    {
        $rawRepo = self::$repo;
        $transactionalRepo = self::$repo->transactional();

        try {
            $transactionalRepo
                ->beginTransaction()
                ->rollback();
        } catch (Exception $ex) {
            $this->assertEmpty("Rollback does not work");
        }

        try {
            $transactionalRepo
                ->beginTransaction()
                ->commit();
        } catch (Exception $ex) {
            $this->assertEmpty("Commit does not work");
        }

        $this->assertNotEmpty("Interface works");
    }

    /**
     * Proof that commit transactions work.
     *
     * @group transaction
     * @group positive
     * @test
     */
    public function commitShouldWork()
    {
        $rawRepo = self::$repo;
        $transactionalRepo = self::$repo->transactional();

        $mapper = $transactionalRepo->entityMapper(LeftEntity::class);
        $rawMapper = $rawRepo->entityMapper(LeftEntity::class);

        $record = [
        "title" => "Mockingjay",
        "author" => "Suzanne Collins"
        ];
        $entity = new LeftEntity($record);

        $transactionalRepo->beginTransaction();

        $this->assertNotEmpty($mapper->insert($entity));
        $this->assertEmpty($rawMapper->where($record)->first());

        $transactionalRepo->commit();

        $this->assertNotEmpty($mapper->where($record)->first());
        $this->assertNotEmpty($rawMapper->where($record)->first());
    }

    /**
     * Proof that rollback transactions work.
     *
     * @group transaction
     * @group positive
     * @test
     */
    public function rollbackShouldWork()
    {
        $rawRepo = self::$repo;
        $transactionalRepo = self::$repo->transactional();

        $mapper = $transactionalRepo->entityMapper(RightEntity::class);
        $rawMapper = $rawRepo->entityMapper(RightEntity::class);

        $record = [
        "make" => "BMW",
        "model" => "Captiva",
        "variant" => "LT - Diesel - Rs. 24.14 Lacs"
        ];
        $entity = new RightEntity($record);

        $transactionalRepo->beginTransaction();

        $this->assertNotEmpty($mapper->insert($entity));
        $this->assertEmpty($rawMapper->where($record)->first());

        $transactionalRepo->rollback();

        $this->assertEmpty($mapper->where($record)->first());
        $this->assertEmpty($rawMapper->where($record)->first());
    }

    /**
     * Proof that multi mapper commit transactions work.
     *
     * @group transaction
     * @group positive
     * @test
     */
    public function multiMapperCommitShouldWork()
    {
        $rawRepo = self::$repo;
        $transactionalRepo = self::$repo->transactional();

        $leftMapper = $transactionalRepo->entityMapper(LeftEntity::class);
        $rightMapper = $transactionalRepo->entityMapper(RightEntity::class);

        $rawLeftMapper = $rawRepo->entityMapper(LeftEntity::class);
        $rawRightMapper = $rawRepo->entityMapper(RightEntity::class);

        $leftRecord = [
        "title" => "Fight Club",
        "author" => "Chuck Palahniuk"
        ];
        $leftEntity = new LeftEntity($leftRecord);

        $rightRecord = [
        "make" => "Honda",
        "model" => "Eon",
        "variant" => "D-Lite - Petrol - Rs. 3.31 Lacs"
        ];
        $rightEntity = new RightEntity($rightRecord);

        $transactionalRepo->beginTransaction();

        $this->assertNotEmpty($leftMapper->insert($leftEntity));
        $this->assertNotEmpty($leftMapper->where($leftRecord)->first());
        $this->assertEmpty($rawLeftMapper->where($leftRecord)->first());

        $this->assertNotEmpty($rightMapper->insert($rightEntity));
        $this->assertNotEmpty($rightMapper->where($rightRecord)->first());
        $this->assertEmpty($rawRightMapper->where($rightRecord)->first());

        $transactionalRepo->commit();

        $this->assertNotEmpty($leftMapper->where($leftRecord)->first());
        $this->assertNotEmpty($rawLeftMapper->where($leftRecord)->first());

        $this->assertNotEmpty($rightMapper->where($rightRecord)->first());
        $this->assertNotEmpty($rawRightMapper->where($rightRecord)->first());
    }

    /**
     * Proof that multi mapper rollback transactions work.
     *
     * @group transaction
     * @group positive
     * @test
     */
    public function multiMapperRollbackShouldWork()
    {
        $rawRepo = self::$repo;
        $transactionalRepo = self::$repo->transactional();

        $leftMapper = $transactionalRepo->entityMapper(LeftEntity::class);
        $rightMapper = $transactionalRepo->entityMapper(RightEntity::class);

        $rawLeftMapper = $rawRepo->entityMapper(LeftEntity::class);
        $rawRightMapper = $rawRepo->entityMapper(RightEntity::class);

        $leftRecord = [
        "title" => "GNU Emacs Manual",
        "author" => "Free Software Foundation"
        ];
        $leftEntity = new LeftEntity($leftRecord);

        $rightRecord = [
        "make" => "Audi",
        "model" => "A5",
        "variant" => "Sportback - Diesel - Rs. 54.02 Lacs"
        ];
        $rightEntity = new RightEntity($rightRecord);

        $transactionalRepo->beginTransaction();

        $this->assertNotEmpty($leftMapper->insert($leftEntity));
        $this->assertNotEmpty($leftMapper->where($leftRecord)->first());
        $this->assertEmpty($rawLeftMapper->where($leftRecord)->first());

        $this->assertNotEmpty($rightMapper->insert($rightEntity));
        $this->assertNotEmpty($rightMapper->where($rightRecord)->first());
        $this->assertEmpty($rawRightMapper->where($rightRecord)->first());

        $transactionalRepo->rollback();

        $this->assertEmpty($leftMapper->where($leftRecord)->first());
        $this->assertEmpty($rawLeftMapper->where($leftRecord)->first());

        $this->assertEmpty($rightMapper->where($rightRecord)->first());
        $this->assertEmpty($rawRightMapper->where($rightRecord)->first());
    }
}
