<?php

namespace Logaretm\Depo\Repositories;

use Log;
use Logaretm\Depo\Repositories\Contracts\CachingRepository as CachingRepositoryContract;
use Logaretm\Depo\Repositories\RepositoryBase as RepositoryImplementation;

abstract class CachingRepositoryBase implements CachingRepositoryContract
{

    /**
     * The duration for which the items will be cached.
     *
     * @var integer
     */
    protected $duration;

    /**
     * @var
     */
    protected $cache;

    /**
     * @var RepositoryImplementation
     */
    protected $repository;

    /**
     * Makes sure that specific query methods gets logged to generate unique cache keys.
     *
     * @var array
     */
    protected $cacheKeywords = [];

    /**
     * CachingRepository constructor.
     * @param $repository
     * @param $duration
     * @param $cache
     */
    public function __construct($repository, $duration, $cache)
    {
        $this->repository = $repository;
        $this->duration = $duration;
        $this->cache = $cache;
    }

    /**
     * @param $method
     * @param $arguments
     * @return $this
     */
    public function __call ($method, $arguments)
    {
        $this->addKeyword($method . '.' . implode('.', $arguments));

        $value = call_user_func_array([$this->repository, $method], $arguments);

        if(! $value instanceof RepositoryBase)
        {
            $this->resetScope();

            return $value;
        }

        return $this;
    }

    /**
     * Clears cache from entries specific to the repository.
     * Note: You can use cache tags.
     * @return mixed
     */
    public function forget()
    {
        $this->cache->tags($this->getCacheTags())->flush();
    }

    /**
     * Gets a unique key for the to-be cached value.
     *
     * @param $prefix
     * @return mixed
     */
    public function generateCacheKey($prefix)
    {
        $keyValue = $prefix . '.' . implode('.', $this->cacheKeywords);

        return md5($keyValue);
    }

    /**
     * Adds a keyword to the current cache keywords array.
     *
     * @param $keyword
     */
    public function addKeyword($keyword)
    {
        if($keyword && ! in_array($keyword, $this->cacheKeywords))
        {
            $this->cacheKeywords[] = $keyword;
        }
    }

    /**
     * Gets all records for the model, ignoring scopes and constrains. caches the results.
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $key = md5('all');

        $result = $this->cache->tags($this->getCacheTag())->remember($key, $this->duration, function () use($columns)
        {
            return $this->repository->all();
        });

        $this->resetScope();

        return $result;
    }

    /**
     * Gets the query records and caches them.
     *
     * @param array $columns
     * @return mixed
     */
    public function get($columns = array('*'))
    {
        $key = $this->generateCacheKey('get');

        $result = $this->cache->tags($this->getCacheTag())->remember($key, $this->duration, function () use($columns)
        {
            return $this->repository->get($columns);
        });

        $this->resetScope();

        return $result;
    }

    /**
     * Gets and caches a paginate for the resource.
     *
     * @param int $perPage
     * @param int $page
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $page = 1, $columns = array('*'))
    {
        $key = $this->generateCacheKey('paginate.' . $perPage . '.' . ($page > 1 ? $page : 1));

        $result = $this->cache->tags($this->getCacheTag())->remember($key, $this->duration, function () use($perPage, $columns, $page)
        {
            return $this->repository->paginate($perPage, $page, $columns);
        });

        $this->resetScope();

        return $result;
    }

    /**
     * Creates a new record.
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $model = $this->repository->create($attributes);
        $this->forget();

        return $model;
    }

    /**
     * updates an existing record.
     *
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function update($id, array $attributes)
    {
        $updated = $this->repository->update($id, $attributes);
        $this->forget();

        return $updated;
    }

    /**
     * Deletes a record.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $deleted = $this->repository->delete($id);
        $this->forget();

        return $deleted;
    }

    /**
     * Returns the primary cache key for this repository.
     *
     * @return mixed
     */
    abstract public function getCacheTag();

    /**
     * Returns the cache tags to be forgotten.
     *
     * @return mixed
     */
    abstract public function getForgetTags();

    /**
     * Resets the query scope.
     */
    public function resetScope()
    {
        $this->cacheKeywords = [];
        $this->repository->resetScope();
    }
}