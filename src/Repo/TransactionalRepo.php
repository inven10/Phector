<?php
declare(strict_types=1);

namespace Phector\Repo;

use Phector\Mapper;

/**
 * Repo but with a focus on managing transactions.
 *
 * Like a Repo but holds a singular connection so that mappers coming
 * from this can be rollbacked or commited.
 */
final class TransactionalRepo
{
    private $connection;
    private $types;

    private function __construct($connection, array $types)
    {
        $this->connection = $connection;
        $this->types = $types;
    }

    /**
     * Create a transactional repo.
     *
     * @param  Connection $connection A connection from the main Repo.
     * @return self A valid repo with a singular connection
     */
    public static function create($connection, $types)
    {
        return new self($connection, $types);
    }

    /**
     * Like with Repo, creates a new mapper from an entity but can be
     * rollbacked.
     *
     * @see    Repo::entityMapper
     * @return Mapper A mapper attached to this repo's connection.
     */
    public function entityMapper($mappedClass)
    {
        $schema = $mappedClass::getSchema();

        return $this->mapper($mappedClass, $schema);
    }

    /**
     * Like with Repo, creates a new mapper with a schema but can be
     * rollbacked.
     *
     * @see    Repo::entityMapper
     * @return Mapper A mapper attached to this repo's connection.
     */
    public function mapper($entityClass, array $schema)
    {
        return Mapper::create($this->connection, $entityClass, $schema, $this->getTypes());
    }


    /**
     * Start the repo's transaction.
     *
     * @return self The mapper under transaction.
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();

        return $this;
    }

    /**
     * Commit the repo's transaction.
     *
     * @return self The mapper with the transaction commited.
     */
    public function commit()
    {
        $this->connection->commit();

        return $this;
    }

    /**
     * Rollback the repo's transaction.
     *
     * @return self The mapper with the transaction cancelled.
     */
    public function rollback()
    {
        $this->connection->rollback();

        return $this;
    }

    /**
     * Get the types from the repo config.
     *
     * @return array Array.
     */
    public function getTypes()
    {
        return $this->types;
    }
}
