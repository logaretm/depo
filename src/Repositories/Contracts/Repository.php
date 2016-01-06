<?php

namespace Logaretm\Depo\Repositories\Contracts;


interface Repository
{
    /**
     * Gets all the model records from the database.
     *
     * @param array $attributes
     * @return mixed
     */
    public function all($attributes = array('*'));

    /**
     * Gets the current query records from the database.
     *
     * @param array $attributes
     * @return mixed
     */
    public function get($attributes = array('*'));

    /**
     * Paginates the current query records from the database.
     *
     * @param int $perPage
     * @param int $page
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $page = 1, $columns = array ('*'));

    /**
     * Creates a new model and saves it to the database.
     *
     * @param $attributes
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * Fetches the model by primary key and updates its attributes. if a model was provided it is updated directly.
     *
     * @param $id
     * @param $attributes
     * @return mixed
     */
    public function update($id, array $attributes);

    /**
     * Deletes a model using its primary key. if a model was provided deletes it directly.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id);
}