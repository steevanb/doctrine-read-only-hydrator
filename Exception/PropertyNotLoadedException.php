<?php

namespace steevanb\DoctrineReadOnlyHydrator\Exception;

use steevanb\DoctrineReadOnlyHydrator\Entity\ReadOnlyEntityInterface;

class PropertyNotLoadedException extends \Exception
{
    /**
     * @param ReadOnlyEntityInterface $proxy
     * @param string $property
     */
    public function __construct(ReadOnlyEntityInterface $proxy, $property)
    {
        $message = get_parent_class($proxy) . '::$' . $property . ' is not loaded, you can\'t access it. ';
        $message .= 'Add select() in your QueryBuilder to load it.';

        parent::__construct($message);
    }
}
