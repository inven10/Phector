<?php
declare(strict_types=1);

namespace Phector;

use Phector\Field;

/**
 * A config class for entity schemas.
 */
final class Schema
{
    private $table;
    private $fields;
    private $associations;

    public function __construct(string $table, array $fields, $associations = [])
    {
        $this->table = $table;
        $this->fields = $fields;
        $this->associations = $associations;
    }

    /**
     * Getter for table name.
     *
     * @return string Table name.
     */
    public function getTable() : string
    {
        return $this->table;
    }

    /**
     * Getter for fields.
     *
     * @return array Array of field objects.
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * Getter for associations.
     *
     * @return array Array of association objects.
     */
    public function getAssociations() : array
    {
        return $this->associations;
    }

    /**
     * Support getter to for the field names.
     *
     * @return array Array of field names.
     */
    public function fieldNames() : array
    {
        return array_map(
            function ($field) {
                return $field->getFieldName();
            },
            array_values(
                $this->fields
            )
        );
    }

    /**
     * Support getter for the primary field.
     *
     * @return Field The primary field for the schema.
     */
    public function getPrimaryField() : Field
    {
        foreach($this->fields as $key => $field)
        {
            if($field->isPrimary()) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Validate and create a schema object
     *
     * @param  array $schema A schema
     * @throws InvalidSchemaException If the schema is invalid
     * @return Schema A valid schema
     */
    public static function create(array $schema)
    {
        // TODO: Further process and refinement of schema
        $tableName = $schema['table'];
        $associations = $schema['associations'] ?? [];

        $baseFields = $schema['fields'];
        $fields = [];
        foreach($baseFields as $fieldName => $baseField) {
            $field = array_merge(
                $baseField,
                ['fieldName' => $fieldName]
            );

            $fields[$fieldName] = Field::create($field);
        }

        return new self($tableName, $fields, $associations);
    }

}
