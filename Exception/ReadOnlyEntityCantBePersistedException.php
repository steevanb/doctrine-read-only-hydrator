<?php

namespace steevanb\DoctrineReadOnlyHydrator\Exception;

class ReadOnlyEntityCantBePersistedException extends \Exception
{
    /**
     * @param object $object
     */
    public function __construct($object)
    {
        parent::__construct('Read only entity "' . get_class($object) . '" can\'t be persisted.');
    }
}
