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

    public function __construct(string $table, array $fields)
    {
        $this->table = $table;
        $this->fields = $fields;
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

        $baseFields = $schema['fields'];
        $fields = [];
        foreach($baseFields as $fieldName => $baseField) {
            $field = array_merge(
                $baseField,
                ['fieldName' => $fieldName]
            );

            $fields[$fieldName] = Field::create($field);
        }

        return new self($tableName, $fields);
    }

}
