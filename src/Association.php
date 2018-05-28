<?php
declare(strict_types=1);

namespace Phector;

/**
 * A class representing an association
 */
final class Association
{

    private $name;
    private $type;
    private $entityClass;
    private $primaryKey;
    private $foreignKey;

    private function __construct(
        string $name,
        $type,
        string $entityClass,
        $primaryKey,
        $foreignKey
    ) {
        $this->name= $name;
        $this->type = $type;
        $this->entityClass = $entityClass;
        $this->primaryKey = $primaryKey;
        $this->foreignKey = $foreignKey;
    }

    /**
     * A getter for the association's name.
     *
     * @return string The name of the association.
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * A getter for the association's type.
     *
     * @return string The type of the association.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A getter for the association's entity class.
     *
     * @return string The entity class of the association.
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * A getter for the association's primary key.
     *
     * @return string The primary key of the association.
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * A getter for the association's foreign key.
     *
     * @return string The foregin key of the association.
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Validates and creates a valid association
     *
     * @param  array $association An association spec
     * @return Field A valid association.
     */
    public static function create(array $association) : Association
    {
        // TODO: More processing
        return new self(
            $association['name'],
            $association['type'],
            $association['entityClass'],
            $association['primaryKey'],
            $association['foreignKey']
        );
    }
}
