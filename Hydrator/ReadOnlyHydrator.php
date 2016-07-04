<?php

namespace steevanb\DoctrineReadOnlyHydrator\Hydrator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class ReadOnlyHydrator
{
    const READ_ONLY_PROPERTY = '__READ_ONLY_HYDRATED_ENTITY__';

    /**
     * @param EntityManagerInterface $manager
     * @param string $className
     * @param array $data
     * @return object
     */
    public static function hydrateAll(EntityManagerInterface $manager, $className, array $data)
    {
        $return = [];
        foreach ($data as $entityData) {
            $return[] = static::hydrate($manager, $className, $entityData);
        }

        return $return;
    }

    /**
     * @param EntityManagerInterface $manager
     * @param string $className
     * @param array $data
     * @return object
     * @throws \Exception
     */
    public static function hydrate(EntityManagerInterface $manager, $className, array $data)
    {
        $classMetaData = $manager->getClassMetadata($className);
        $mappings = $classMetaData->getAssociationMappings();
        $entity = static::createNewEntity($classMetaData, $data);
        $reflection = new \ReflectionObject($entity);

        foreach ($data as $name => $value) {
            if (array_key_exists($name, $mappings)) {
                $mapping = $mappings[$name];
                switch ($mapping['type']) {
                    case ClassMetadata::ONE_TO_ONE:
                        $value = static::hydrateOneToOne($manager, $mapping, $value);
                        break;
                    case ClassMetadata::ONE_TO_MANY:
                        $value = static::hydrateOneToMany($manager, $mapping, $value);
                        break;
                    case ClassMetadata::MANY_TO_ONE:
                        $value = static::hydrateManyToOne($manager, $mapping, $value);
                        break;
                    case ClassMetadata::MANY_TO_MANY:
                        $value = static::hydrateManyToMany($manager, $mapping, $value);
                        break;
                    default:
                        throw new \Exception('Unknow mapping type "' . $mapping['type'] . '".');
                }
            }

            if (
                $classMetaData->inheritanceType === ClassMetadata::INHERITANCE_TYPE_SINGLE_TABLE
                && isset($entity->$name) === false
            ) {
                continue;
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
     * @param ClassMetadata $classMetaData
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    protected static function createNewEntity(ClassMetadata $classMetaData, array $data)
    {
        switch ($classMetaData->inheritanceType) {
            case ClassMetadata::INHERITANCE_TYPE_NONE:
                $className = $classMetaData->name;
                break;
            case ClassMetadata::INHERITANCE_TYPE_SINGLE_TABLE:
                if (array_key_exists($classMetaData->discriminatorColumn['name'], $data) === false) {
                    $exception = 'Discriminator column "' . $classMetaData->discriminatorColumn['name'] . '" ';
                    $exception .= 'for "' . $classMetaData->name . '" does not exists in $data.';
                    throw new \Exception($exception);
                }
                $discriminator = $data[$classMetaData->discriminatorColumn['name']];
                $className = $classMetaData->discriminatorMap[$discriminator];
                break;
            default:
                throw new \Exception('Unknow inheritance type "' . $classMetaData->inheritanceType . '".');
        }

        $entity = new $className();
        $entity->{static::READ_ONLY_PROPERTY} = true;

        return $entity;
    }

    /**
     * @param EntityManagerInterface $manager
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected static function hydrateOneToOne(EntityManagerInterface $manager, array $mapping, $data)
    {
        return static::hydrate($manager, $mapping['targetEntity'], $data);
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

    /**
     * @param EntityManagerInterface $manager
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected static function hydrateManyToMany(EntityManagerInterface $manager, array $mapping, $data)
    {
        $entities = [];
        foreach ($data as $linkedData) {
            $entities[] = static::hydrate($manager, $mapping['targetEntity'], $linkedData);
        }

        return new ArrayCollection($entities);
    }
}
