<?php namespace Push\MagicRepository;

interface RepositoryInterface
{
    /**
     * Create a new instance.
     *
     * @param  array $attributes Attributes to instantiate the model with
     * @return mixed             The model object
     */
    public function make(array $attributes = []);

    /**
     * Find an existing or create a new instance.
     *
     * @param  array $attributes Attributes to query/instantiate the model with
     * @return mixed             The model object
     */
    public function firstOrMake(array $attributes = []);

    /**
     * Query for the first (find) or all (get) instances.
     *
     * @param  array|integer $options The qualifier or an array of the equality and the qualifier
     * @param  array|null    $order   The column to order by
     * @param  array|null    $columns The columns to retrieve
     * @return mixed|null
     *
     * Example of possible methods:
     *
     * findLatestBy*($value, array $order = null, array $columns = null);
     * findOldestBy*($value, array $order = null, array $columns = null);
     * getLatest#By*($value, array $order = null, array $columns = null);
     * getOldest#By*($value, array $order = null, array $columns = null);
     */

    /**
     * Magic Method for handling dynamic functions.
     *
     * Handle the construction of dynamic where clauses
     *
     * @param string $method     The method that was called
     * @param array  $parameters The parameters passed to the method
     */
    public function __call($method, $parameters);

    /**
     * Persist an instance.
     *
     * @param  mixed   $model The model to persist
     * @return boolean        True if the instance is successfully persisted into the database
     */
    public function save($model);

    /**
     * Persist data.
     *
     * @param  array   $fields The fields of a table to persist
     * @return boolean         True if the data is successfully persisted into the database
     */
    public function insert(array $fields);

    /**
     * Delete (via finding) an instance.
     *
     * @param  mixed|array|integer $ids The model/an array of ids/an id to delete
     * @return integer|boolean          The number of instances deleted or true if the specified instance was deleted
     */
    public function delete($options);

    /**
     * Associate multiple instances to an object and save into the database.
     *
     * Iterates through the relations and associates each object with the model
     *
     * @param  mied    $model     The model object to associate
     * @param  array   $relations The instances to be associated
     * @return boolean            True if the model is successfully persisted into the database
     */
    public function relate($model, $relations = []);

    /**
     * Cache buster.
     *
     * Called after the data store is mutated to clear all cached queries results of this repository
     * Always bust untagged caches if a repository is untagged
     * Call this function when manually inserting
     *
     * @param  string|null $key Key to reference cache
     * @return void
     */
    public function cacheBust($key = null);
}
