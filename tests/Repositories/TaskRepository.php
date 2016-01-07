<?php

namespace Logaretm\Depo\Tests\Repositories;

use Logaretm\Depo\Repositories\RepositoryBase;
use Logaretm\Depo\Tests\Models\Task;

class TaskRepository extends RepositoryBase
{
    /**
     * Yep. that is it.
     *
     * @return mixed
     */
    public function getRepositoryModel()
    {
        return Task::class;
    }
}