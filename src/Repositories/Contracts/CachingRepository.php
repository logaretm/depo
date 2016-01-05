<?php
/**
 * Created by PhpStorm.
 * User: logar
 * Date: 1/4/2016
 * Time: 06:13
 */

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
     * Gets a unique key for the to-be cached value.
     *
     * @param $seed
     * @return mixed
     */
    public function generateCacheKey($seed);
}