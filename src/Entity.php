<?php
declare(strict_types=1);

namespace Phector;

use Phector\MappedEntity;

/**
 * Using this trait grants permission to allow the mapper to inject the
 * record values into the instance itself as well as to ask for the
 * record representation of the entity.
 *
 * Methods implemented by this trait should not be used outside of
 * Phector.
 */
trait Entity
{
    /**
     * Ask the entity for a pristine instance which the mapper can
     * inject the values.
     *
     * @return self An instance of the class
     */
    public static function createInstance()
    {
        return new self();
    }

    /**
     * Mutates the instance of this class with the new values from data.
     *
     * @param  self  $instance An instance of this class. Should come from
     *                         createInstance.
     * @param  array $data     Data to be merged into the instance. Should
     *                         come from the mapper.
     * @return self The same instance with the data merged into it
     */
    public static function fromRecord($instance, array $data)
    {
        foreach($data as $key => $value){
            $instance->{$key} = $value;
        }

        return $instance;
    }

    /**
     * Output the array version of the entity to allow records to be
     * inserted and updated throught the mapper.
     *
     * @return array Array representation of the entity.
     */
    public static function toRecord($instance) : array
    {
        return get_object_vars($instance);
    }

    /**
     * Clone the given instance
     *
     * @return self Cloned instance of the class.
     */
    public static function clone($instance)
    {
        return self::fromRecord(self::createInstance(), self::toRecord($instance));
    }

    /**
     * Merges an instance with array.
     *
     * @param  self  $instance An instance of this class.
     * @param  array $data     Data to be merged into the instance.
     * @return self A new instance with the data merged into it
     */
    public static function merge($instance, array $data)
    {
        $clonedInstance = self::clone($instance);
        foreach($data as $key => $value){
            $clonedInstance->{$key} = $value;
        }

        return $clonedInstance;
    }

}
