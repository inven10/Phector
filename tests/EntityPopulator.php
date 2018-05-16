<?php

namespace Phector\Tests;

use Faker\Generator;
use Faker\Guesser\Name;

use Phector\Mapper;
use Phector\Repo;

final class EntityPopulator
{
    const RELATED_FETCH_COUNT = 10;

    private $mapper;
    private $columnFormatters = array();
    private $modifiers = array();
    private $useExistingData = false;

    public function __construct(Mapper $mapper, $useExistingData = false)
    {
        $this->mapper = $mapper;
        $this->useExistingData = $useExistingData;
    }

    public function mergeColumnFormattersWith($columnFormatters)
    {
        $this->columnFormatters = array_merge($this->columnFormatters, $columnFormatters);
    }

    public function setColumnFormatters($columnFormatters)
    {
        $this->columnFormatters = $columnFormatters;
    }

    public function setModifiers(array $modifiers)
    {
        $this->modifiers = $modifiers;
    }

    public function getModifiers()
    {
        return $this->modifiers;
    }

    public function mergeModifiersWith(array $modifiers)
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);
    }

    public function guessColumnFormatters(Generator $generator)
    {
        $formatters = [];
        $columnTypeGuesser = new ColumnTypeGuesser($generator);

        $fields = $this->mapper->getSchema()->getFields();
        foreach ($fields as $fieldName => $field) {
            if ($field->isPrimary()) {
                continue;
            }

            if ($formatter = $columnTypeGuesser->guessFormat($field)) {
                $formatters[$fieldName] = $formatter;
                continue;
            }
        }

        return $formatters;
    }

    public function execute($insertedEntities)
    {
        $entity = $this->mapper->build([]);

        $this->fillColumns($entity, $insertedEntities);
        $this->callMethods($entity, $insertedEntities);

        $record = $entity->toRecord($entity);
        return $this->mapper->insertRecord($record);
    }

    private function fillColumns($instance, $insertedEntities)
    {
        foreach ($this->columnFormatters as $field => $format) {
            if (null !== $format) {
                $value = is_callable($format) ? $format($insertedEntities, $instance) : $format;
                $instance->{$field} = $value;
            }
        }
    }

    private function callMethods($obj, $insertedEntities)
    {
        foreach ($this->getModifiers() as $modifier) {
            $modifier($obj, $insertedEntities);
        }
    }
}
