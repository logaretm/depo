<?php


namespace Logaretm\Depo\Tests\Repositories;


use Logaretm\Depo\Repositories\CachingRepositoryBase;

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