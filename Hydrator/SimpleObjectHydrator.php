<?php

namespace steevanb\DoctrineReadOnlyHydrator\Hydrator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Internal\Hydration\ArrayHydrator;
use Doctrine\ORM\Mapping\ClassMetadata;

class SimpleObjectHydrator extends ArrayHydrator
{
    const HYDRATOR_NAME = 'simpleObject';

    /**
     * @param array $data
     * @param array $result
     */
    protected function hydrateRowData(array $data, array &$result)
    {
        $arrayData = array();
        parent::hydrateRowData($data, $arrayData);

        $rootAlias = key($this->getPrivatePropertyValue(ArrayHydrator::class, '_rootAliases', $this));
        $result[] = $this->doHydrateRowData($this->_rsm->aliasMap[$rootAlias], $arrayData[0]);
    }

    /**
     * @param string $className
     * @param array $data
     * @return object
     * @throws \Exception
     */
    protected function doHydrateRowData($className, array $data)
    {
        $classMetaData = $this->_em->getClassMetadata($className);
        $mappings = $classMetaData->getAssociationMappings();
        $entity = $this->createEntity($classMetaData, $data);
        $reflection = new \ReflectionObject($entity);

        foreach ($data as $name => $value) {
            if (array_key_exists($name, $mappings)) {
                $mapping = $mappings[$name];
                switch ($mapping['type']) {
                    case ClassMetadata::ONE_TO_ONE:
                        $value = $this->hydrateOneToOne($mapping, $value);
                        break;
                    case ClassMetadata::ONE_TO_MANY:
                        $value = $this->hydrateOneToMany($mapping, $value);
                        break;
                    case ClassMetadata::MANY_TO_ONE:
                        $value = $this->hydrateManyToOne($mapping, $value);
                        break;
                    case ClassMetadata::MANY_TO_MANY:
                        $value = $this->hydrateManyToMany($mapping, $value);
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
    protected function createEntity(ClassMetadata $classMetaData, array $data)
    {
        $className = $this->getEntityClassName($classMetaData, $data);
        $reflection = new \ReflectionClass($className);
        $entity = $reflection->newInstanceWithoutConstructor();

        return $entity;
    }

    /**
     * @param ClassMetadata $classMetaData
     * @param array $data
     * @return string
     * @throws \Exception
     */
    protected function getEntityClassName(ClassMetadata $classMetaData, array $data)
    {
        switch ($classMetaData->inheritanceType) {
            case ClassMetadata::INHERITANCE_TYPE_NONE:
                $return = $classMetaData->name;
                break;
            case ClassMetadata::INHERITANCE_TYPE_SINGLE_TABLE:
                if (array_key_exists($classMetaData->discriminatorColumn['name'], $data) === false) {
                    $exception = 'Discriminator column "' . $classMetaData->discriminatorColumn['name'] . '" ';
                    $exception .= 'for "' . $classMetaData->name . '" does not exists in $data.';
                    throw new \Exception($exception);
                }
                $discriminator = $data[$classMetaData->discriminatorColumn['name']];
                $return = $classMetaData->discriminatorMap[$discriminator];
                break;
            default:
                throw new \Exception('Unknow inheritance type "' . $classMetaData->inheritanceType . '".');
        }

        return $return;
    }

    /**
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected function hydrateOneToOne(array $mapping, $data)
    {
        return $this->doHydrateRowData($mapping['targetEntity'], $data);
    }

    /**
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected function hydrateOneToMany(array $mapping, $data)
    {
        $entities = [];
        foreach ($data as $linkedData) {
            $entities[] = $this->doHydrateRowData($mapping['targetEntity'], $linkedData);
        }

        return new ArrayCollection($entities);
    }

    /**
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected function hydrateManyToOne(array $mapping, $data)
    {
        return $this->doHydrateRowData($mapping['targetEntity'], $data);
    }

    /**
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected function hydrateManyToMany(array $mapping, $data)
    {
        $entities = [];
        foreach ($data as $linkedData) {
            $entities[] = $this->doHydrateRowData($mapping['targetEntity'], $linkedData);
        }

        return new ArrayCollection($entities);
    }

    /**
     * @param string $className
     * @param string $property
     * @param object $object
     * @return mixed
     */
    protected function getPrivatePropertyValue($className, $property, $object)
    {
        $reflection = new \ReflectionProperty($className, $property);
        $accessible = $reflection->isPublic();
        $reflection->setAccessible(true);
        $value = $reflection->getValue($object);
        $reflection->setAccessible($accessible === false);

        return $value;
    }
}
