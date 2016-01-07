# Depo
Simple and flexible repository pattern implementations for the laravel framework.

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

This package provides base classes and contracts for the usage of a repository class. along with a general implementation of both base classes.

The `RepositoryBase` and `Repository` classes only serves as a wrapper around the Eloquent Model.  Which can be a good place to put complex queries.

The `CachingRepositoryBase` class is a decorator for the `RepositoryBase` objects, it caches the query results using cache tags, so make sure your cache driver supports the cache tags functionality (redis/memcache).

The `CachingRepositoryBase` also allows creating/updating/deleting of Model records in the database. it clears the relevant cached results after any of those operations are executed, then the new results will be cached when the next query executes, making sure your queries are up to date with the database.

## Usage

There are many ways to use the classes. But first the next snippets assumes that  a `Task` model exist.

```php
class Task extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'body',
        'completed'
    ];


    /**
     * @param $query
     * @return mixed
     */
    public function scopeCompleted ($query)
    {
        return $query->where('completed', true);
    }


    /**
     * @param $query
     * @return mixed
     */
    public function scopeInProgress ($query)
    {
        return $query->where('completed', false);
    }
}
```

Its a basic model with two scopes.

Now to use the classes you have multiple ways, use whatever suits your needs.

### Using the General implementations

using the `Repository` class you can create a repository for the `Task` model easily using dependency injection.

```php
$repository = new Repository(new Task);
```

Then if we need a caching repository for the `Task` model we only need to inject the previously created repository object into the constructor. Which takes a repository (or a model) and a remembering duration in minutes, and a cache store that supports tags.

```php
$cachingRepository = new CachingRepository($repository, 10, app()['cache.store']);
```

you can also instantiate the caching repository without having to create an underlying one, internally it will create one for you.

```php
$cachingRepository = new CachingRepository(new Task, 10, app()['cache.store']);
```

now you can do some querying, note that it both classes provide a fluent API, you can chain methods as long the last called method does not return a Builder object.

```php
$completedTasks = $cachingRepository->where('completed', true)->get();
```

or use the scope we've previously created.
```php
$completedTasks = $cachingRepository->completed()->get();
```
while those two classes provide a simple and straight forward. they do not solve some of the problems that the repository pattern can solve. For example what if you have some complex query, so complex that mudding the model with it won't be ideal in your case, you need something that is "resposible" for this task.

This is where the second way comes in, extending base classes.

### Extending Base Classes

Extend the Repository base class and provide an implementation for the `getRepositoryModel` method, which should return the type of the model to be used in this repository.

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

 while the constructor for the `RepositoryBase` class allows for model injection, you can skip injecting it and the model will be created by laravel's IOC container.

 ```php
 $taskRepository = new TaskRepository;
 ```

 or

  ```php
  $taskRepository = new TaskRepository(new Task);
  ```

Now to create a caching repository for the model you also need to extend the `CachingRepositoryBase` base class.

```php
class CachingTaskRepository extends CachingRepositoryBase
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

Instantiate it using an instance of the `RepositoryBase` class and provide a cache duration and a cache store.

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