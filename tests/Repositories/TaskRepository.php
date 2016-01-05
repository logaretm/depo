<?php

namespace Logaretm\Depo\Tests\Repositories;

use Logaretm\Depo\Repositories\Repository;
use Logaretm\Depo\Tests\Models\Task;

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