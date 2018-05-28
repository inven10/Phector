<?php
declare(strict_types=1);

namespace Phector;

use Phector\Association;

/**
 * A class representing an association-entity pairs 
 */
final class AssociationEntitiesPair
{

    private $association;
    private $entities;

    private function __construct(
        $association,
        $entities
    ) {
        $this->association= $association;
        $this->entities= $entities;
    }

    /**
     * A getter for the pair's association.
     *
     * @return Association The association in the pair.
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * A getter for the pair's entities.
     *
     * @return object The entities for the given association.
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Validates and creates a pair
     *
     * @param  Association $association An association object
     * @param  array       $entities    Array of entities of the given association
     * @return self A valid association-entities pair object.
     */
    public static function create($association, array $entities = []) : self
    {
        // TODO: More processing
        return new self(
            $association,
            $entities
        );
    }
}
