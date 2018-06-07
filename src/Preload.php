<?php
declare(strict_types=1);

namespace Phector;

use Phector\Association;
use Phector\Exceptions\AssociationNotFoundException;

/**
 * A class representing a preload
 */
final class Preload
{

    private $association;
    private $tableAlias;

    private function __construct(
        $association,
        string $tableAlias = ''
    ) {
        $this->association= $association;
        $this->tableAlias= $tableAlias;
    }

    /**
     * A getter for the preload's association.
     *
     * @return Association The association for the preload.
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * A getter for the preload's table alias.
     *
     * @return string The table alias of the preload.
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     * Validates and creates a preload
     *
     * @param  Schema $schema     Valid schema object of the base entity
     * @param  string $identifier Identifier or name for the association/s
     * @return Preload The preload object
     */
    public static function create($schema, $identifier, $tableAlias = '') : Preload
    {
        $association = $schema->findAssociation($identifier);

        if(!$association) {
            throw new AssociationNotFoundException();
        }

        return new self($association, $tableAlias);
    }
}
