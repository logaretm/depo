<?php


namespace Logaretm\Depo\Repositories;


use Illuminate\Database\Eloquent\Model;

class CachingRepository extends CachingRepositoryBase
{
    /**
     * @var
     */
    protected $primaryCacheTag;

    /**
     * @var
     */
    protected $forgetCacheTags;

    /**
     * CachingRepository constructor.
     * @param RepositoryBase|Model $repository
     * @param int $duration
     * @param $cache
     * @param array $options
     */
    public function __construct($repository, $duration, $cache, array $options = [])
    {
        // If the "repository" is actually a model, create an underlying repository object and use it.
        if($repository instanceof Model)
        {
            $repository = new Repository($repository);
        }

        parent::__construct($repository, $duration, $cache);
        $this->applyOptions($options);
    }

    /**
     * Applies the options passed to the constructor.
     *
     * @param array $options
     */
    protected function applyOptions(array $options)
    {
        if(array_key_exists('primaryTag', $options) && $options['primaryTag'])
        {
            $this->primaryCacheTag = $options['primaryTag'];
        }

        else
        {
            $this->applyDefaultPrimaryTag();
        }

        if(array_key_exists('forgetTags', $options) && $options['forgetTags'])
        {
            $this->forgetCacheTags = $options['forgetTags'];
        }

        else
        {
            $this->applyDefaultForgets();
        }
    }

    /**
     * Applies sensible defaults to the primary cache key.
     */
    protected function applyDefaultPrimaryTag()
    {
        $this->primaryCacheTag = str_slug($this->repository->getRepositoryModel());
    }

    /**
     * Applies sensible default to the forgets cache keys.
     */
    protected function applyDefaultForgets()
    {
        $this->forgetCacheTags = [
            str_slug($this->repository->getRepositoryModel())
        ];
    }

    /**
     * Gets the primary cache key for this repository entries.
     *
     * @return mixed
     */
    public function getCacheTag()
    {
        return $this->repository->getRepositoryModel();
    }

    /**
     * Returns the cache tags to be forgotten.
     *
     * @return mixed
     */
    public function getForgetTags()
    {
        return $this->forgetCacheTags;
    }
}