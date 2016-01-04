<?php


namespace App\Repositories;

use Logaretm\Repositories\Contracts\Repository as RepositoryContract;
use Logaretm\Repositories\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryContract
{

    /**
     * Repository Model.
     *
     * @var Model
     */
    protected $model;

    /**
     * Used to keep track of the current query scope.
     *
     * @var Builder
     */
    protected $query;

    /**
     * Repository constructor.
     *
     * @param $model
     */
    public function __construct($model)
    {
        $this->makeModel($model);
    }

    /**
     * Used to 'delegate' any undefined called methods to the query builder.
     *
     * @param $method
     * @param $arguments
     * @return $this
     */
    public function __call($method, $arguments)
    {
        // Call the undefined method on the current query.
        $value = call_user_func_array([$this->query, $method], $arguments);

        // If it has a "not query" value, reset the scope and return the value.
        if(! $value instanceof Builder)
        {
            $this->resetScope();

            return $value;
        }

        return $this;
    }

    /**
     * Assigns and checks for the model provided.
     *
     * @param $model
     * @throws RepositoryException
     */
    protected function makeModel($model)
    {
        // Make sure the provided model object is an instance of Model.
        if(! $model instanceof Model)
        {
            throw new RepositoryException("{$model} is not of type " . Model::class);
        }

        // Make sure the class name of the provided model is the supported repository model.
        if(get_class($model) !== $this->getRepositoryModel())
        {
            throw new RepositoryException("{$model} is not supported by this repository, supported model type is " . $this->getRepositoryModel());
        }


        $this->model = $model;
        $this->query = $model->query();
    }

    /**
     * Gets the repository model class name.
     *
     * @return mixed
     */
    abstract function getRepositoryModel();

    /**
     * Gets all repository model data ignoring query scopes.
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array ('*'))
    {
        $this->model->get($columns);
    }

    /**
     * Gets the query result.
     *
     * @param $columns
     * @return mixed
     */
    public function get($columns = array('*'))
    {
        $collection = $this->query->get($columns);
        $this->resetScope();

        return $collection;
    }

    /**
     * Gets a paginate for the resource.
     *
     * @param int $perPage
     * @param int $page
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $page = 1, $columns = array ('*'))
    {
        $paginator = $this->model->paginate($perPage, $columns, 'page', $page);
        $this->resetScope();

        return $paginator;
    }

    /**
     * Resets the query scope.
     *
     * @return mixed
     */
    public function resetScope()
    {
        $this->query = $this->model->newQuery();
    }

    /**
     * Creates a new record.
     *
     * @param $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $this->model->create($attributes);
    }

    /**
     * Updates an existing record. $id can be a model instance.
     *
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function update($id, array $attributes)
    {
        if($id instanceof Model)
        {
            return $id->update($attributes);
        }

        return $this->model->findOrFail($id)->update($attributes);
    }

    /**
     * Deletes a record, $id can be a model instance.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if ($id instanceof Model)
        {
            return $id->delete();
        }

        return $this->model->destroy($id);
    }
}