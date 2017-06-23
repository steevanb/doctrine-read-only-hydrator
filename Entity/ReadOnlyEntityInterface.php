<?php

namespace steevanb\DoctrineReadOnlyHydrator\Entity;

interface ReadOnlyEntityInterface
{
    /** @return bool */
    public function isReadOnlyPropertiesLoaded(array $properties);

    public function assertReadOnlyPropertiesAreLoaded(array $properties);
}
