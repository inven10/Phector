<?php
declare(strict_types=1);

namespace Phector;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

use Phector\Schema;
use Phector\Repo\RepoConfig;
use Phector\Preload;
use Phector\Association\AssociationEntitiesPair;
use Phector\Association\AssociationTypes;
use Phector\Exception\RecordNotFoundException;
use Phector\Exception\AssociationNotFoundException;

/**
 * Actual class that maps the data into the entity.
 */
final class Mapper
{
    private $entityClass;
    private $query;
    private $schema;
    private $types;
    private $preloads= [];

    private function __construct($entityClass, $query, $schema, $types, $preloads= [])
    {
        $this->entityClass = $entityClass;
        $this->query = $query;
        $this->types= $types;
        $this->schema = $schema;
        $this->preloads= $preloads;
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
    public static function create($connection, string $entityClass, array $schema, array $types)
    {
        $validSchema = Schema::create($schema);
        $query = $connection->table($validSchema->getTable());

        return new self($entityClass, $query, $validSchema, $types);
    }

    function __call(string $name, array $args)
    {
        $catchMethods = ['preload'];
        $endMethods = ['get'];
        $interceptMethods = ['first'];

        $query = $this->cloneQuery();
        $result = null;

        if(in_array($name, $catchMethods)) {
            return $this->{$name}(...$args);
        } elseif(in_array($name, $endMethods)) {
            $result = call_user_func_array([$this->applySelects($this->cloneQuery(), $this->preloads), $name], $args);
        } elseif(in_array($name, $interceptMethods) && $this->preloads) {
            $result = call_user_func_array([$this->applySelects($this->cloneQuery(), $this->preloads), 'get'], $args);
        } else {
            $result = call_user_func_array([$this->cloneQuery(), $name], $args);
        }

        if ($result instanceof QueryBuilder) {
            return new self(
                $this->entityClass,
                $result,
                $this->schema,
                $this->types,
                $this->preloads
            );
        } elseif (is_array($result)) {
            return array_map(
                function ($record) {
                    return $this->build(get_object_vars($record));
                },
                $result
            );
        } elseif ($result instanceof \stdClass) {
            return $this->build($this->entityClass::toRecord($result));
        } elseif ($result instanceof Collection) {
            if($this->preloads) {
                $processedResults = $this->processPreloads($result->toArray(), $this->preloads, $this->schema);
                if(in_array($name, $interceptMethods)) {
                    return $processedResults->first();
                }

                return $processedResults;
            }

            return array_map(
                function ($record) {
                    return $this->build($this->entityClass::toRecord($record));
                },
                $result->toArray()
            );
        }  else {
            return $result;
        }
    }

    /**
     * Support function to generate alias for a given field name and table name
     *
     * @param  string $columnName The column name to generate the alias for
     * @param  stirng $tableName  Table name to assign the alias to
     * @return stirng The aliased column name
     */
    private function generateAlias($tableName,$columnName, $nameOnly = false)
    {
        return $nameOnly ?
        "${tableName}__$columnName"
        :
        "$tableName.$columnName as ${tableName}__$columnName";
    }

    /**
     * Support function to map fields into field aliases
     *
     * @param  array  $fields    Array of Field objects to map over
     * @param  stirng $tableName Table name to assign the alias to
     * @return array Array of alias strings.
     */
    private function generateAliases($fields, $tableName)
    {
        return array_values(
            array_map(
                function ($field) use ($tableName) {
                    return $this->generateAlias($tableName, $field->getColumnName());
                }, $fields
            )
        );
    }

    /**
     * Apply associations to the query
     *
     * @param  object $query    The query to apply the associations.
     * @param  array  $preloads Array of preload objects to apply to the query
     * @return object Query with selects applied.
     */
    private function applySelects($query, array $preloads)
    {
        if($preloads) {
            $tableName = $this->schema->getTable();
            $query->addSelect(
                $this->generateAliases($this->schema->getFields(), $tableName)
            );

            foreach($preloads as $preload) {
                $association = $preload->getAssociation();
                $associationSchema = Schema::create($association->getEntityClass()::getSchema());

                $query->addSelect(
                    $this->generateAliases(
                        $associationSchema->getFields(),
                        empty($preload->getTableAlias()) ?
                            $associationSchema->getTable()
                        : $preload->getTableAlias()
                    )
                );
            }
        }

        return $query;
    }

    /**
     * Transform the raw record into its processed form.
     *
     * @param  array $record Raw record data.
     * @param  array $fields Field objects to use.
     * @return array Processed data.
     */
    private function applySchema(array $record, $fields = null) : array
    {
        $fields = $fields ?? $this->schema->getFields();
        $data = [];
        foreach ($fields as $field) {
            $columnName = $field->getColumnName();
            $fieldName = $field->getFieldName();
            $type = $field->getType();

            if (isset($record[$columnName])) {
                $value = $record[$columnName];

                $data[$fieldName] = $this->types[$type]::load($value);
            }
        }

        return $data;
    }

    /**
     * Turns records from a data set into a mapped entity.
     *
     * @param  array $record      Data from the query manager.
     * @param  array $entityClass The entity class to be used.
     * @param  array $fields      Field objects that will be applied to the schema.
     * @return object Instance of the entity class with the merged
     * database.
     */
    public function build(array $record, string $entityClass = null, $fields = null)
    {
        $entityClass = $entityClass ?? $this->entityClass;
        return $entityClass::fromRecord(
            $entityClass::createInstance(),
            $this->applySchema($record, $fields)
        );
    }

    /**
     * Insert the entity into the database.
     *
     * @return object A new instance of the class that is saved.
     */
    public function insert($entity)
    {
        return $this->insertRecord($this->entityClass::toRecord($entity));
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

                $value = $this->types[$type]::dump($baseValue);
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
            $this->entityClass::toRecord(
                $this->cloneQuery()->where($columnName, $entityRecord[$fieldName])->first()
            )
        );

    }

    /**
     * Delete the given entity
     *
     * @throws RecordNotFoundException If no record was found
     * @param  object The entity to delete
     * @return object A new instance of the updated entity.
     */
    public function delete($entity)
    {
        $primaryField = $this->schema->getPrimaryField();
        $fieldName = $primaryField->getFieldName();
        $columnName = $primaryField->getColumnName();

        $record = $this->cloneQuery()->first();
        if(!$record) {
            throw new RecordNotFoundException();
        }

        $this->cloneQuery()->where($columnName, $entity->{$fieldName})->delete();

        return $entity;
    }

    /**
     * Process the given record
     *
     * @param  array  $record     The record set
     * @param  array  $preloads   The array of preloads
     * @param  Schema $baseSchema The schema of the base entity
     * @return array|object Entity/Array of entities with all preloads created
     */
    private function processPreload(array $record, array $preloads, $baseSchema)
    {
        $recordCollection = Collection::make($recordSet)->map(
            function ($record) {
                return get_object_vars($record);
            }
        );

        $baseEntity = null;
        foreach($baseSchema->getFields() as $field) {
            $columnAlias = $this->generateAlias($baseSchema->getTable(), $field->getColumnName(), true);
            $baseRecord[$field->getColumnName()] = $record[$columnAlias];
        }

        $baseEntity = $this->build($baseRecord);
    }

    /**
     * Process the given record set
     *
     * @param  array  $recordSet  The record set
     * @param  array  $preloads   The array of preloads
     * @param  Schema $baseSchema The schema of the base entity
     * @return array|object Entity/Array of entities with all preloads created
     */
    private function processPreloads(array $recordSet, array $preloads, $baseSchema)
    {
        $unwrap = function ($arr) {
            return $arr[0];
        };

        $getSchemaAlias = function ($tableName, $columnName) {
            return $this->generateAlias($tableName, $columnName, true);
        };

        $buildEntityFrom = function ($record, $schema, $extraBuildParams = []) use ($getSchemaAlias) {
            $tableName = $schema->getTable();
            $baseRecord = [];
            foreach($schema->getFields() as $field) {
                $columnName = $field->getColumnName();
                $baseRecord[$columnName] = $record[$getSchemaAlias($tableName, $columnName)];
            }

            return $this->build($baseRecord, ...$extraBuildParams);
        };

        $recordCollection = Collection::make($recordSet)->map(
            function ($record) {
                return get_object_vars($record);
            }
        );

        $entityClassCollection = $recordCollection
            ->unique($this->generateAlias($baseSchema->getTable(), $baseSchema->getPrimaryField()->getColumnName(), true))
            ->map(
                function ($record) use ($buildEntityFrom) {
                    return $buildEntityFrom($record, $this->schema);
                }
            );

        $buildPairs = function ($preload) use (
            $recordCollection,
            $buildEntityFrom,
            $getSchemaAlias
        ) {
                $schema = Schema::create($preload->getAssociation()->getEntityClass()::getSchema());
                $association = $preload->getAssociation();
                $tableName = $schema->getTable();
                $primaryFieldColumnName = $schema->getPrimaryField()->getColumnName();
                $primaryAlias = $getSchemaAlias($tableName, $primaryFieldColumnName);
                $associations = $recordCollection
                    ->unique($primaryAlias)
                    ->map(
                        function ($record) use (
                            $buildEntityFrom,
                            $schema,
                            $association
                        ) {
                            return $buildEntityFrom(
                                $record,
                                $schema,
                                [$association->getEntityClass(), $schema->getFields()]
                            );
                        }
                    );
                return [
                    $association->getName() => AssociationEntitiesPair::create($association, $associations->toArray())
                ];
        };

        $associationEntitiesPairCollection = Collection::make($this->preloads)
            ->mapToGroups($buildPairs)
            ->map($unwrap);

        $filterAssociationEntityPairs = function ($pair, $associationName, $baseEntity, $baseSchema) {
            $associationEntities = $pair->getEntities();
            $association = $pair->getAssociation();
            $localKeyColumnName = $association->getLocalKey();
            $foreignKeyColumnName = $association->getForeignKey();

            $associationSchema = Schema::create($association->getEntityClass()::getSchema());
            $associationFields = $associationSchema->getFields();
            $baseEntityFields = $baseSchema->getFields();
            $foreignFieldIndex = Collection::make($associationFields)->search(
                function ($field) use ($foreignKeyColumnName) {
                                           return $field->getColumnName() === $foreignKeyColumnName;
                }
            );
            $localFieldIndex = Collection::make($baseEntityFields)->search(
                function ($field) use ($localKeyColumnName) {
                    return $field->getColumnName() === $localKeyColumnName;
                }
            );

            $foreignKeyFieldName = $associationFields[$foreignFieldIndex]->getFieldName();
            $localKeyFieldName = $baseEntityFields[$localFieldIndex]->getFieldName();

            $filteredAssociationEntities = Collection::make($associationEntities)->filter(
                function ($associationEntity) use ($foreignKeyFieldName, $localKeyFieldName, $baseEntity) {
                    return $baseEntity->{$localKeyFieldName} === $associationEntity->{$foreignKeyFieldName};
                }
            );

            return $filteredAssociationEntities->map(
                function ($filteredAssociationEntity) use ($associationSchema) {
                    return $filteredAssociationEntity->{$associationSchema->getPrimaryField()->getFieldName()};
                }
            );
        };

        $links = $entityClassCollection->mapToGroups(
            function ($baseEntity) use (
                $baseSchema,
                $associationEntitiesPairCollection,
                $preloads,
                $recordCollection,
                $filterAssociationEntityPairs
            ) {
                $baseEntityId = $baseEntity->{$baseSchema->getPrimaryField()->getFieldName()};

                $filteredAssociationIds = $associationEntitiesPairCollection->map(
                    function ($pair, $associationName) use (
                        $filterAssociationEntityPairs,
                        $baseEntity,
                        $baseSchema
                    ) {
                        return $filterAssociationEntityPairs($pair, $associationName, $baseEntity, $baseSchema);
                    }
                );

                return [$baseEntityId => $filteredAssociationIds];
            }
        )
        ->map($unwrap);

        $buildAssociationsOnEntity = function ($originalEntity, $entitySchema, $associations, $associationMap, $associationEntityMap) {
            $clonedEntity = get_class($originalEntity)::clone($originalEntity);

            foreach($associations as $associationName => $associationIds) {
                $associationIdCollection = Collection::make($associationIds);
                $association = $associationMap[$associationName];
                $associationSchema = Schema::create($association->getEntityClass()::getSchema());
                $associationEntities = $associationEntityMap[$associationName];

                $kittens = $associationIdCollection->map(
                    function ($id) use ($associationEntities, $associationSchema) {
                        $associationIndex = Collection::make($associationEntities)->search(
                            function ($associationEntity) use ($id, $associationSchema) {
                                $associationPrimaryFieldName = $associationSchema->getPrimaryField()->getFieldName();
                                return $id === $associationEntity->{$associationPrimaryFieldName};
                            }
                        );
                        return $associationEntities[$associationIndex];
                    }
                );

                switch($association->getType()){
                case AssociationTypes::one() :
                    $clonedEntity->{$associationName} = $kittens->first() ?? null;
                    break;
                case AssociationTypes::many() :
                    $clonedEntity->{$associationName} = $kittens->toArray() ?? [];
                    break;
                default:
                    break;
                }
            }

            return $clonedEntity;
        };

        $builtLinks = $links->map(
            function ($associations, $baseEntityPrimaryKey) use (
                $entityClassCollection,
                $associationEntitiesPairCollection,
                $baseSchema,
                $buildAssociationsOnEntity
            ) {
                $baseEntityIndex = $entityClassCollection->search(
                    function ($entity) use ($baseEntityPrimaryKey, $baseSchema) {
                        return $entity->{$baseSchema->getPrimaryField()->getFieldName()} === $baseEntityPrimaryKey;
                    }
                );
                $baseEntity = $entityClassCollection->toArray()[$baseEntityIndex];

                $associationMap = $associationEntitiesPairCollection->map(
                    function ($pair) {
                        return $pair->getAssociation();
                    }
                );
                $associationEntityMap = $associationEntitiesPairCollection->map(
                    function ($pair) {
                        return $pair->getEntities();
                    }
                );

                return $buildAssociationsOnEntity(
                    $baseEntity,
                    $baseSchema,
                    $associations,
                    $associationMap,
                    $associationEntityMap
                );
            }
        )->values();

        return $builtLinks;
    }

    /**
     * Preload an association
     *
     * @param  string|array The association/s to preload
     * @return self Updated self with new preloads
     */
    public function preload($associationIdentifier)
    {
        $preloads = $this->preloads;
        $schema = $this->schema;

        if(is_string($associationIdentifier)) {
            $preloads[] = Preload::create($schema, $associationIdentifier);
        } elseif (array_values($associationIdentifier) != $associationIdentifier) {
            foreach($associationIdentifier as $associationName => $tableAlias) {
                $preloads[] = Preload::create($schema, $associationName, $tableAlias);
            }
        } elseif (is_array($associationIdentifier)) {
            foreach($associationIdentifier as $associationName) {
                $preloads[] = Preload::create($schema, $associationName);
            }
        }
        return new self(
            $this->entityClass,
            $this->query,
            $this->schema,
            $this->types,
            $preloads
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
