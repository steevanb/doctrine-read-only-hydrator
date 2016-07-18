<?php

namespace steevanb\DoctrineReadOnlyHydrator\Hydrator;

use steevanb\DoctrineStats\Doctrine\ORM\Event\OverloadedHydratorTrait;

class ReadOnlyHydrator extends \ComposerOverloadClass\steevanb\DoctrineReadOnlyHydrator\Hydrator\ReadOnlyHydrator
{
    use OverloadedHydratorTrait;
}
