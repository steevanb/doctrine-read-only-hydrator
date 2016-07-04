<?php

namespace steevanb\DoctrineReadOnlyHydrator\Exception;

class ReadOnlyEntityCantBeFlushedException extends \Exception
{
    /**
     * @param object $object
     */
    public function __construct($object)
    {
        parent::__construct('Read only entity "' . get_class($object) . '" can\'t be flushed.');
    }
}
