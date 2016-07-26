2.0.4 (2016-07-26)
------------------

- Fix relation hydration when value is null, instead of array
- Replace some array_key_exists() by isset(), for performance

2.0.3 (2016-07-25)
------------------

- Overload hydrateAllData() instead of hydrateRowData() in SimpleObjectHydrator, to fix hydration bug

2.0.2 (2016-07-19)
------------------

- Fix array parameter type and default value, and boolean default value for ReadOnlyHydrator::getPhpForParameter()

2.0.1 (2016-07-19)
------------------

- Fix INHERITANCE_TYPE_SINGLE_TABLE hydration

2.0.0 (2016-07-19)
------------------

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
