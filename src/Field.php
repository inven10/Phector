<?php
declare(strict_types=1);

namespace Phector;

use Stringy\StaticStringy;

/**
 * A class representing the a schema field
 */
final class Field
{

    private $columnName;
    private $defaultValue;
    private $fieldName;
    private $primary;
    private $type;

    private function __construct(
        string $columnName,
        $defaultValue,
        string $fieldName,
        bool $primary,
        $type
    ) {
        $this->columnName = $columnName;
        $this->defaultValue = $defaultValue;
        $this->fieldName = $fieldName;
        $this->primary = $primary;
        $this->type = $type;
    }

    /**
     * A getter for the field's column name.
     *
     * @return string The column name
     */
    public function getColumnName() : string
    {
        return $this->columnName;
    }

    /**
     * A getter for the field's default value.
     *
     * @return The field's default value
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }


    /**
     * A getter for the field's mapping name.
     *
     * @return string The field name
     */
    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    /**
     * A getter for the field's is primary.
     *
     * @return bool The field primary-ness
     */
    public function isPrimary() : bool
    {
        return $this->primary;
    }

    /**
     * A getter for the field's type .
     *
     * @return class The field class
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Validates and creates a valid field
     *
     * @param  array $field A field spec
     * @throw  InvalidFieldException If the field is invalid
     * @return Field A valid field.
     */
    public static function create(array $field) : Field
    {
        // TODO: More processing
        return new self(
            $field['columnName'] ?? StaticStringy::underscored($field['fieldName']),
            $field['default'] ?? null,
            $field['fieldName'],
            $field['primary'] ?? false,
            $field['type']
        );
    }
}
