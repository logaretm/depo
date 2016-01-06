# Depo
Simple and flexible repository pattern implementations.

## Motivation

I was motivated by Jeffery Way's' laracasts videos regarding the decorator pattern and repository pattern implementations, while I am completely aware that those patterns are sometimes are an over-kill but I have been trying to have the best of two worlds; simplicity and functionality.

Just keep it simple :)

The problem with a basic repository implementation is that sometimes you need all of the query builder functionality under your disposal, I mean adding a `getCompletedTasks` to your contract, repository class, and the caching repository isn't exactly straight forward. Normally you just want to add a scope method or use where clause directly.
Which is what this implementation takes into account.

here are some links to the relevant videos

[Decorating Repositories](https://laracasts.com/lessons/decorating-repositories)

[Repositories Simplified](https://laracasts.com/lessons/repositories-simplified)

## Installation

This package can be installed easily via composer just run:

`composer require logaretm/depo`

## Summary

This package provides a base classes and contracts for the usage of a repository class.

The `Repository` class only serves as a wrapper around the Eloquent Model.  Which can be a good place to put complex queries.

The `CachingRepository` class is a decorator for the `Repository` objects, it caches the query results using cache tags, so make sure your cache driver supports the cache tags functionality (redis/memcache).

The `CachingRepository` also allows creating/updating/deleting of Model records in the database. it clears the relevant cached results after any of those operations are executed, then the new results will be cached when the next query executes, making sure your queries are up to date with the database.

So if you are upset about the "remember" method removed from eloquent query builder I believe my implementation is a modest alternative.

## Usage

using a Task model as an example:

Extend the Repository base class and provide an implementation for the `getRepositoryModel` method.

 ```php
class TaskRepository extends Repository
{
    /**
     * Yep. that is it.
     *
     * @return mixed
     */
    function getRepositoryModel()
    {
        return Task::class;
    }
}
 ```

 while the constructor for the `Repository` class allows for model injection, you can skip injecting it and the model will be created by laravel's IOC container.
 I will probably add a general repository to take advantage of the dependency injection properly. then you won't need to extend the `Repository` class.

 ```php
 $taskRepository = new TaskRepository;
 ```

 or

  ```php
  $taskRepository = new TaskRepository(new Task);
  ```

Now to create a caching repository for the model you also need to extend the `CachingRepository` base class.

```php
class CachingTaskRepository extends CachingRepository
{
    /**
     * Returns the primary cache key for this repository.
     *
     * @return mixed
     */
    public function getCacheTag()
    {
        return 'tasks';
    }

    /**
     * Returns the cache tags to be forgotten.
     *
     * @return mixed
     */
    public function getForgetTags()
    {
        return ['tasks'];
    }
}
```

Instantiate it using an instance of the `Repository` class and provide a cache duration and a cache store.

```php
$cachingRepository = new CachingTaskRepository($taskRepository, 10, app()['cache.store']);
```
Now you are ready to cache your queries, any methods that are not included in the repository objects will be delegated to the underlying query builder, allowing you to use your scope methods, and whatever the query builder has to offer.

```php
$completedTasks = $cachingRepository->completed()->get();
```

Note that the caching depends on the called method names and the parameters, something like:
```php
$cachingRepository->completed()->get();
```

and

```php
$cachingRepository->where('completed', true)->get();
```

will be cached under different keys, even though they return exactly the same results.


## License

The package is licensed under the MIT license.