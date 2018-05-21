<?php
declare(strict_types=1);

namespace Phector;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

use Phector\Schema;
use Phector\RepoConfig;
use Phector\RecordNotFoundException;

/**
 * Actual class that maps the data into the entity.
 */
final class Mapper
{
    private $entityClass;
    private $query;
    private $schema;

    private function __construct($entityClass, $query, $schema)
    {
        $this->entityClass = $entityClass;
        $this->query = $query;
        $this->schema = $schema;
    }

    /**
     * A getter for the mapper's schema.
     *
     * @return Schema The mapper's schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * A getter for the mapper's entity class.
     *
     * @return string The mapper's entity class
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Validates and creates a mapper.
     *
     * @param  object $connect     A connection from the repository.
     * @param  string $entityClass The class of the entity.
     * @param  array  $schema      Schema for the entity.
     * @throw  InvalidSchemaException If the schema is invalid.
     * @return Mapper A valid mapper.
     */
    public static function create($connection, string $entityClass, array $schema)
    {
        $validSchema = Schema::create($schema);
        $query = $connection->table($validSchema->getTable());

        return new self($entityClass, $query, $validSchema);
    }

    function __call(string $name, array $args)
    {
        $result = call_user_func_array([$this->cloneQuery(), $name], $args);

        if ($result instanceof QueryBuilder) {
            return new self(
                $this->entityClass,
                $result,
                $this->schema
            );
        } elseif (is_array($result)) {
            return array_map(
                function ($record) {
                    return $this->build(get_object_vars($record));
                },
                $result
            );
        } elseif ($result instanceof \stdClass) {
            return $this->build(get_object_vars($result));
        } elseif ($result instanceof Collection) {
            return array_map(
                function ($record) {
                    return $this->build(get_object_vars($record));
                },
                $result->toArray()
            );
        }  else {
            return $result;
        }

    }

    /**
     * Transform the raw record into its processed form.
     *
     * @param  array $record Raw record data.
     * @return array Processed data.
     */
    private function applySchema(array $record) : array
    {
        $data = [];
        foreach ($this->schema->getFields() as $field) {
            $columnName = $field->getColumnName();
            $fieldName = $field->getFieldName();
            $type = $field->getType();

            if (isset($record[$columnName])) {
                $value = $record[$columnName];

                $data[$fieldName] = $type::get($value);
            }
        }

        return $data;
    }

    /**
     * Turns records from a data set into a mapped entity.
     *
     * @param  array $record Data from the query manager.
     * @return object Instance of the entity class with the merged
     * database.
     */
    public function build(array $record)
    {
        return $this->entityClass::fromRecord(
            $this->entityClass::createInstance(),
            $this->applySchema($record)
        );
    }

    /**
     * Insert the entity into the database.
     *
     * @return object A new instance of the class that is saved.
     */
    public function insert($entity)
    {
        return $this->insertRecord($entity->toRecord());
    }

    /**
     * Insert the record into the database.
     *
     * @return object A new instance of the entity of the inserted
     * record.
     */
    public function insertRecord(array $entityRecord)
    {
        $data = [];
        foreach ($this->schema->getFields() as $field) {
            $columnName = $field->getColumnName();
            $fieldName = $field->getFieldName();
            $defaultValue = $field->getDefaultValue();
            $type = $field->getType();

            $value = null;
            if (array_key_exists($fieldName, $entityRecord)) {
                $rawValue = $entityRecord[$fieldName];
                $baseValue = $rawValue ?
                           $rawValue  :
                           (is_callable($defaultValue) ?
                            $defaultValue() :
                            $defaultValue);

                $value = $type::set($baseValue);
            }

            $data[$columnName] = $value;
        }

        $this->query->insert($data);

        return $this->build($data);
    }

    /**
     * Update an entity
     *
     * @throws RecordNotFoundException If no record was found
     * @param  object The entity with updated fields
     * @return object A new instance of the updated entity.
     */
    public function update($entity)
    {
        return $this->updateRecord($this->entityClass::toRecord($entity, []));
    }

    /**
     * Update an entity record
     *
     * @throws RecordNotFoundException If no record was found
     * @param  array The entity record with updated fields
     * @return object A new instance of the updated entity.
     */
    public function updateRecord(array $entityRecord)
    {
        $primaryField = $this->schema->getPrimaryField();
        $fieldName = $primaryField->getFieldName();
        $columnName = $primaryField->getColumnName();

        $record = $this->cloneQuery()->first();
        if(!$record) {
            throw new RecordNotFoundException();
        }

        $this->cloneQuery()->where($columnName, $entityRecord[$fieldName])->update($entityRecord);

        return $this->build(
            get_object_vars(
                $this->cloneQuery()->where($columnName, $entityRecord[$fieldName])->first()
            )
        );

    }
    
    /**
     * Clones the query property of the mapper
     *
     * @return object The cloned query object.
     */
    private function cloneQuery()
    {
        return $this->query->cloneWithout([]);
    }
}
