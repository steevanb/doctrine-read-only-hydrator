### [2.3.0](../../compare/2.2.4...2.3.0) (2021-03-28)

- [lerminou](https://github.com/lerminou) Allow PHP8 in `composer.json`

### [2.2.4](../../compare/2.2.3...2.2.4) (2020-12-15)

- [maxhelias](https://github.com/maxhelias) Fix Composer autoload error with ComposerOverloadClass directory

### [2.2.3](../../compare/2.2.2...2.2.3) (2019-10-28)

- Add try / catch in ReadOnlySubscriber to catch empty class name exception throwned by class_implements()

### [2.2.2](../../compare/2.2.1...2.2.2) (2018-02-14)

- Fix ReadOnly method proxy return when parent return type is void

### [2.2.1](../../compare/2.2.0...2.2.1) (2017-09-04)

- Fix ReadOnlyProxy not added to classMetadata list, you can now use $queryBuilder->getParameter('foo', $foo) instead of $queryBuilder->getParameter('foo', $foo->getId()

### [2.2.0](../../compare/2.1.4...2.2.0) (2017-06-23)

ReadOnlyInterface is changed. I should create a 3.0.0 instead of 2.2.0, but I assume this BC will not break your code, cause it's internal code.
- [BC] Add ReadOnlyInterface::isReadOnlyPropertiesLoaded()
- [BC] Add ReadOnlyInterface::assertReadOnlyPropertiesAreLoaded()
- ReadOnlyHydrator::assertReadOnlyPropertiesAreLoaded() from protected to public

### [2.1.4](../../compare/2.1.3...2.1.4) (2017-03-28)

- [Desjardins Jérôme](https://github.com/jewome62) Fix PHP 7.0.0 and 7.0.1 ReflectionMethod::getReturnType()::getName() who is protected, should be public as of 7.1.2

### [2.1.3](../../compare/2.1.2...2.1.3) (2017-03-24)

- Fix PHP 7.1.0 and 7.1.1 ReflectionMethod::getReturnType()::getName() who is protected, should be public as of 7.1.2
- [Desjardins Jérôme](https://github.com/jewome62) Fix INHERITANCE_TYPE_JOINED hydration

### [2.1.2](../../compare/2.1.1...2.1.2) (2017-03-01)

- Fix PHP7 compatibility

### [2.1.1](../../compare/2.1.0...2.1.1) (2017-01-18)

- Create cache dir recursivly

### [2.1.0](../../compare/2.0.5...2.1.0) (2016-08-17)

- Add suggest steevanb/doctrine-stats to composer.json
- Change steevanb/doctrine-stats requirement to ^1.1.0 (only if you want to install it)
- ComposerOverloadClass\steevanb\DoctrineReadOnlyHydrator\Hydrator\ReadOnlyHydrator and SimpleObjectHydrator now
overload createEntity(), to call dispatchPostCreateEntityEvent()
- Add php version ^5.4.6 || ^7.0 to composer.json

### [2.0.5](../../compare/2.0.4...2.0.5) (2016-08-02)

- Add postLoad event call after SimpleObjectHydrator and ReadOnlyHydrator hydration
- Fix indexBy configuration for collections (oneToMany and manyToMany)

### [2.0.4](../../compare/2.0.3...2.0.4) (2016-07-26)

- Fix relation hydration when value is null, instead of array
- Replace some array_key_exists() by isset(), for performance

### [2.0.3](../../compare/2.0.2...2.0.3) (2016-07-25)

- Overload hydrateAllData() instead of hydrateRowData() in SimpleObjectHydrator, to fix hydration bug

### [2.0.2](../../compare/2.0.1...2.0.2) (2016-07-19)

- Fix array parameter type and default value, and boolean default value for ReadOnlyHydrator::getPhpForParameter()

### [2.0.1](../../compare/2.0.0...2.0.1) (2016-07-19)

- Fix INHERITANCE_TYPE_SINGLE_TABLE hydration

### [2.0.0](../../compare/1.0.0...2.0.0) (2016-07-19)

- Add steevanb\DoctrineReadOnlyHydrator\Hydrator\SimpleObjectHydrator (old ReadOnlyHydrator)
- Change ReadOnlyHydrator : now use proxy to throw exception when you try to access a non-loaded property
- Add bridge to [steevanb/doctrine-stats](https://github.com/steevanb/doctrine-stats)
- Performance optimizations with BlackFire
- Merge Symfony2 and Symfony2 bundle into steevanb\DoctrineReadOnlyHydrator\Bridge\ReadOnlyHydratorBundle
- Change Doctrine version to ^2.4
- Change PHP version to >= 5.4.6
- Remove static methods in ReadOnlyHydrator, now use $query->getResult(ReadOnlyHydrator::HYDRATOR_NAME)

1.0.0 (2016-07-04)
------------------

- Creating steevanb\DoctrineReadOnlyHydrator\Hydrator\ReadOnlyHydrator
- Creating ReadOnlyHydrator::hydrate() and ReadOnlyHydrator::hydrateAll()
- Creating steevanb\DoctrineReadOnlyHydrator\EventSubscriber\ReadOnlySubscriber, to disable persist() and flush() on read only entities
- Creating Symfony2 and Symfony3 bridges
