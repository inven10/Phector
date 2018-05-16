<?php
declare(strict_types=1);

namespace Phector;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Builder as QueryBuilder;

use Phector\RepoConfig;
use Phector\Mapper;
use Phector\PlainEntity;
use Phector\MappedEntity;

/**
 * The Data Mapper for PHP inspired by Ecto
 */
final class Repo
{
    private $config;
    private $manager;

    private function __construct($config, $manager)
    {
        $this->config = $config;
        $this->manager = $manager;
    }

    /**
     * Get the underlying database manager.
     *
     * @return object Underlying manager instance for the repo.
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Create a repo.
     *
     * @param  array $config A repo configuration.
     * @return Repo A valid repo.
     */
    public static function create(array $config)
    {
        $repoConfig = RepoConfig::create($config);

        $manager = new Capsule();
        $manager->addConnection($repoConfig->getDatabaseConfig());
        $manager->setAsGlobal();

        return new self($repoConfig, $manager);
    }

    /**
     * Create a mapper from a mapped entity.
     *
     * @param  MappedEntity $mappedClass An entity with a schema
     * @throw  InvalidSchemaException If the schema of the mapped class
     * is invalid
     * @return Mapper A mapper for the expected class
     */
    public function entityMapper($mappedClass)
    {
        $schema = $mappedClass::getSchema();

        return $this->mapper($mappedClass, $schema);
    }

    /**
     * Create a mapper from an abstract entity and schema
     *
     * @param PlainEntity $entityClass A plain old entity
     * @param array       $schema      A generic schema
     * @throw InvalidSchemaException If the schema is invalid

     * @return Mapper A mapper for the entity and schema
     */
    public function mapper($entityClass, array $schema)
    {
        return Mapper::create($this->manager->connection(), $entityClass, $schema);
    }
}
