<?php

namespace steevanb\DoctrineReadOnlyHydrator\Exception;

use steevanb\DoctrineReadOnlyHydrator\Hydrator\ReadOnlyHydrator;

class PrivateMethodShouldNotAccessPropertiesException extends \Exception
{
    /**
     * @param string $className
     * @param string $method
     * @param array $properties
     */
    public function __construct($className, $method, array $properties)
    {
        array_walk($properties, function(&$property) {
            $property = '$this->' . $property;
        });
        $formatedProperties = implode(', ', $properties);

        $message = 'Private method ' . $className . '::' . $method . '() ';
        $message .= 'should not directly access ' . $formatedProperties . ', ';
        $message .= 'it should use accessor or be protected. ';
        $message .= 'As method is private, ' . ReadOnlyHydrator::class . ' could not handle method call, ';
        $message .= 'so it can\'t ensure when property is not loaded, ';
        $message .= 'no calls to ' . $formatedProperties . ' are made.';

        parent::__construct($message);
    }
}
