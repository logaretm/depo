<?php

namespace Logaretm\Depo\Repositories\Contracts;


interface CachingRepository extends Repository
{
    /**
     * Clears cache from entries specific to the repository.
     *
     * @return mixed
     */
    public function forget();

    /**
     * Gets a unique key for the to-be cached value. You can add another string to use in key generation.
     *
     * @param $additionalKey
     * @return mixed
     */
    public function generateCacheKey($additionalKey = null);
}