<?php
namespace Phector\Tests;

use Faker\Generator;

use Phector\Repo;
use Phector\MappedEntity;

final class Populator
{
    private $generator;
    private $repo;
    private $entities = array();
    private $quantities = array();

    public function __construct(Generator $generator, Repo $repo)
    {
        $this->generator = $generator;
        $this->repo = $repo;
    }

    public function addEntity(
        $mappedEntityClass,
        $number,
        $customColumnFormatters = array(),
        $customModifiers = array(),
        $useExistingData = false
    ) {
        $mapper = $this->repo->entityMapper($mappedEntityClass);

        $entity = new EntityPopulator($mapper, $useExistingData);

        $entity->setColumnFormatters($entity->guessColumnFormatters($this->generator));

        if ($customColumnFormatters) {
            $entity->mergeColumnFormattersWith($customColumnFormatters);
        }

        $entity->mergeModifiersWith($customModifiers);

        $this->entities[$mappedEntityClass] = $entity;
        $this->quantities[$mappedEntityClass ] = $number;
    }

    public function execute()
    {
        $insertedEntities = [];
        foreach ($this->quantities as $entityName => $number) {
            for ($i = 0; $i < $number; $i++) {
                $insertedEntities[$entityName][] = $this->entities[$entityName]->execute(
                    $insertedEntities
                );
            }
        }

        return $insertedEntities;
    }
}
