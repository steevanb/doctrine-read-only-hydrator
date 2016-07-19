2.0.0 (2016-07-19)
------------------

- Add steevanb\DoctrineReadOnlyHydrator\Hydrator\ReadOnlyHydrator, who use proxy to throw exception when you try to access a non-loaded property
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
