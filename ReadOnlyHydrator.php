<?php

namespace steevanb\DoctrineReadOnlyHydrator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class ReadOnlyHydrator
{
    /**
     * @param EntityManagerInterface $manager
     * @param string $className
     * @param array $data
     * @return object
     * @throws \Exception
     */
    public function hydrate(EntityManagerInterface $manager, $className, array $data)
    {
        $entity = new $className();
        $reflection = new \ReflectionObject($entity);
        dd($reflection->getMethod('getId')->returnsReference());
        unset($entity->id);
        dd('test');
        $metaData = $manager->getClassMetadata($className);
        $mappings = $metaData->getAssociationMappings();

        foreach ($data as $name => $value) {
            if (array_key_exists($name, $mappings)) {
                $mapping = $mappings[$name];
                switch ($mapping['type']) {
                    case ClassMetadata::ONE_TO_MANY :
                        $value = static::hydrateOneToMany($manager, $mapping, $value);
                        break;
                    case ClassMetadata::MANY_TO_ONE :
                        $value = static::hydrateManyToOne($manager, $mapping, $value);
                        break;
                    default:
                        throw new \Exception('Unknow mapping type "' . $mapping['type'] . '".');
                }
            }

            $property = $reflection->getProperty($name);
            $isAccessible = $property->isPublic() === false;
            $property->setAccessible(true);
            $property->setValue($entity, $value);
            $property->setAccessible($isAccessible);
        }

        return $entity;
    }

    /**
     * @param EntityManagerInterface $manager
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected static function hydrateOneToMany(EntityManagerInterface $manager, array $mapping, $data)
    {
        $entities = [];
        foreach ($data as $linkedData) {
            $entities[] = static::hydrate($manager, $mapping['targetEntity'], $linkedData);
        }

        return new ArrayCollection($entities);
    }

    /**
     * @param EntityManagerInterface $manager
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected static function hydrateManyToOne(EntityManagerInterface $manager, array $mapping, $data)
    {
        return static::hydrate($manager, $mapping['targetEntity'], $data);
    }
}
