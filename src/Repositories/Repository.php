<?php


namespace Logaretm\Depo\Repositories;


use Illuminate\Database\Eloquent\Model;
use Logaretm\Depo\Repositories\Exceptions\RepositoryException;

class Repository extends RepositoryBase
{
    /**
     * Override the parent constructor, ensuring $model is required.
     *
     * Repository constructor.
     * @param null $model
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /**
     * Assigns and checks for the model provided.
     *
     * @param $model
     * @throws RepositoryException
     */
    protected function makeModel($model)
    {
        // Make sure the provided model object is an instance of Eloquent Model.
        if(! $model instanceof Model)
        {
            throw new RepositoryException("model is not of type " . Model::class);
        }

        $this->model = $model;
        $this->query = $model->query();
    }

    /**
     * Gets the repository model class name.
     *
     * @return mixed
     */
    public function getRepositoryModel()
    {
        return get_class($this->model);
    }
}