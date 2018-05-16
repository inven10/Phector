<?php
declare(strict_types=1);

namespace Phector;

use Phector\Schema;

/**
 * A simple interface indicating an entity has a default schema
 */
interface MappedEntity extends PlainEntity
{
    /**
     * A schema for this entity.
     *
     * @throw  InvalidSchemaException If the schema is invalid
     * @return array A schema representing the mapped entity
     */
    public static function getSchema() : array;
}
