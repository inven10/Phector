<?php
declare(strict_types=1);

namespace Phector;

use PDO;
use Illuminate\Container\Container;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Events\StatementPrepared;

use Phector\RepoConfig;
use Phector\Mapper;
use Phector\TransactionalRepo;

/**
 * The Data Mapper for PHP inspired by Ecto
 */
final class Repo
{
    private $config;
    private $factory;

    private function __construct($config, $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Create a repo.
     *
     * Instead of using the normal capsule manager, this uses the
     * ConnectionFactory to allow better transactional support.
     *
     * @param  array $config A repo configuration.
     * @return self A valid repo.
     */
    public static function create(array $config)
    {
        $repoConfig = RepoConfig::create($config);

        $container = new Container();
        $factory = new ConnectionFactory(new Container());

        return new self($repoConfig, $factory);
    }

    private function makeConnection()
    {
        $connection = $this->factory->make($this->config->getDatabaseConfig());

        $dispatcher = new Dispatcher(new Container());
        $dispatcher->listen(
            StatementPrepared::class, function ($event) {
                // TODO: Should be configured to fully tweak the join feature
                $event->statement->setFetchMode(PDO::FETCH_OBJ);
            }
        );

        $connection->setEventDispatcher($dispatcher);

        return $connection;
    }

    /**
     * Create a schema builder.
     *
     * @internal If the query builder can be changed, this method should
     * be refactored.
     * @return   SchemaBuilder The Illuminate schema builder
     */
    public function schemaBuilder()
    {
        return $this->makeConnection()->getSchemaBuilder();
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
        return Mapper::create($this->makeConnection(), $entityClass, $schema);
    }

    /**
     * Create a transactional repo.
     *
     * @return TransactionalRepo A repo with a transaction enabled.
     */
    public function transactional()
    {
        return TransactionalRepo::create($this->makeConnection());
    }
}
