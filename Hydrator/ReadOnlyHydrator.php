<?php

namespace steevanb\DoctrineReadOnlyHydrator\Hydrator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\ORM\Internal\Hydration\ArrayHydrator;
use Doctrine\ORM\Mapping\ClassMetadata;
use steevanb\DoctrineReadOnlyHydrator\Entity\ReadOnlyEntityInterface;
use steevanb\DoctrineReadOnlyHydrator\Exception\MethodNotFoundException;
use steevanb\DoctrineReadOnlyHydrator\Exception\PrivateMethodShouldNotAccessPropertiesException;

class ReadOnlyHydrator extends ArrayHydrator
{
    const HYDRATOR_NAME = 'readOnly';

    /**
     * @param array $data
     * @param array $result
     */
    protected function hydrateRowData(array $data, array &$result)
    {
        $arrayData = array();
        parent::hydrateRowData($data, $arrayData);

        $rootAlias = key($this->getPrivatePropertyValue(ArrayHydrator::class, '_rootAliases', $this));
        $result[] = $this->hydrateRowDataReadOnly($this->_rsm->aliasMap[$rootAlias], $arrayData[0]);
    }

    /**
     * @param string $className
     * @param array $data
     * @return object
     * @throws \Exception
     */
    protected function hydrateRowDataReadOnly($className, array $data)
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

        $methods = array();
        $reflection = new \ReflectionClass($className);
        $properties = array_merge($classMetaData->getFieldNames(), array_keys($classMetaData->associationMappings));
        foreach ($reflection->getMethods() as $method) {
            if ($method->getName() === '__construct') {
                continue;
            }

            $usedProperties = $this->getUsedProperties($method, $properties);
            if (count($usedProperties) > 0) {
                if ($method->isPrivate()) {
                    throw new PrivateMethodShouldNotAccessPropertiesException(
                        $className,
                        $method->getName(),
                        $usedProperties
                    );
                }

                $methods[] = $this->createProxyMethod($method, $usedProperties);
            }
        }

        $methodCode = implode("\n\n", $methods);
        $namespace = substr($classMetaData->getName(), 0, strrpos($classMetaData->getName(), '\\'));
        $shortClassName = substr($classMetaData->getName(), strrpos($classMetaData->getName(), '\\') + 1);
        $generator = static::class;
        $readOnlyInterface = ReadOnlyEntityInterface::class;

        $php = <<<PHP
<?php

namespace ReadOnlyProxies\__CG__\\$namespace;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY $generator
 */
class $shortClassName extends $className implements $readOnlyInterface
{
    protected \$loadedProperties;

    public function __construct(array \$loadedProperties)
    {
        \$this->loadedProperties = \$loadedProperties;
    }

$methodCode
}
PHP;
        !dd($php);



        $entity = (new \ReflectionClass($className))->newInstanceWithoutConstructor();

        return $entity;
    }

    /**
     * @param array $mapping
     * @param array $data
     * @return ArrayCollection
     */
    protected function hydrateOneToOne(array $mapping, $data)
    {
        return $this->hydrateRowDataReadOnly($mapping['targetEntity'], $data);
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
            $entities[] = $this->hydrateRowDataReadOnly($mapping['targetEntity'], $linkedData);
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
        return $this->hydrateRowDataReadOnly($mapping['targetEntity'], $data);
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
            $entities[] = $this->hydrateRowDataReadOnly($mapping['targetEntity'], $linkedData);
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

    /**
     * As Doctrine\ORM\EntityManager::newHydrator() call new FooHydrator($this), we can't set parameters to Hydrator.
     * So, we will use proxyDirectory from Doctrine\Common\Proxy\AbstractProxyFactory.
     * It's directory used by Doctrine\ORM\Internal\Hydration\ObjectHydrator.
     *
     * @return string
     */
    protected function getProxyDirectory()
    {
        /** @var ProxyGenerator $proxyGenerator */
        $proxyGenerator = $this->getPrivatePropertyValue(
            AbstractProxyFactory::class,
            'proxyGenerator',
            $this->_em->getProxyFactory()
        );

        $directory = $this->getPrivatePropertyValue(get_class($proxyGenerator), 'proxyDirectory', $proxyGenerator);
        $readOnlyDirectory = $directory . DIRECTORY_SEPARATOR . 'ReadOnly';
        if (is_dir($readOnlyDirectory) === false) {
            mkdir($readOnlyDirectory);
        }

        return $readOnlyDirectory;
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @param array $properties
     * @return string|false
     */
    protected function getUsedProperties(\ReflectionMethod $reflectionMethod, $properties)
    {
        $classLines = file($reflectionMethod->getFileName());
        $methodLines = array_slice(
            $classLines,
            $reflectionMethod->getStartLine() - 1,
            $reflectionMethod->getEndLine() - $reflectionMethod->getStartLine() + 1
        );
        $code = '<?php' . "\n" . implode("\n", $methodLines) . "\n" . '?>';

        $return = array();
        $nextStringIsProperty = false;
        foreach (token_get_all($code) as $token) {
            if (is_array($token)) {
                if ($token[0] === T_VARIABLE && $token[1] === '$this') {
                    $nextStringIsProperty = true;
                } elseif ($nextStringIsProperty && $token[0] === T_STRING) {
                    $nextStringIsProperty = false;
                    if (in_array($token[1], $properties)) {
                        $return[$token[1]] = true;
                    }
                }
            }
        }

        return array_keys($return);
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @param array $properties
     * @return string
     */
    protected function createProxyMethod(\ReflectionMethod $reflectionMethod, array $properties)
    {
        if ($reflectionMethod->isPublic()) {
            $signature = 'public';
        } else {
            $signature = 'protected';
        }
        $signature .= ' function ' . $reflectionMethod->getName() . '()';
        $method = $reflectionMethod->getName();

        array_walk($properties, function(&$name) {
            $name = "'" . $name . "'";
        });
        $propertiesToAssert = implode(', ', $properties);

        $php = <<<PHP
    $signature
    {
        \$this->assertReadOnlyPropertiesAreLoaded($propertiesToAssert);

        return call_user_func_array(array('parent', '$method'), func_get_args());
    }
PHP;

        return $php;
    }
}
