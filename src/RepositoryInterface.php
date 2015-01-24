<?php namespace Magician;

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
     * Magic method for handling the construction of dynamic search functions.
     *
     * Query for the first (find) or all (get) instances.
     *
     * Parameters
     *
     *     array|integer $value   The qualifier or an array of the equality and the qualifier
     *     array|null    $order   The column to order by
     *     array|null    $columns The columns to retrieve
     *
     * Return Value
     *
     *     mixed|null             The results of the query
     *
     * Example of possible methods:
     *
     *     findLatestBy*($value, array $order = null, array $columns = null);
     *     findOldestBy*($value, array $order = null, array $columns = null);
     *     getLatest#By*($value, array $order = null, array $columns = null);
     *     getOldest#By*($value, array $order = null, array $columns = null);
     *
     * @param string $method     The name of the method called
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
     * @param  array|integer|mixed $options The model/an array of ids/an id to delete
     * @return integer|boolean              The number of instances deleted or true if the specified instance was deleted
     */
    public function delete($options);

    /**
     * Iterates through the relations, associates each object with the model, and save them into the database.
     *
     * @param  mixed   $model     The model object to associate
     * @param  array   $relations The instances to be associated
     * @return boolean            True if the model is successfully persisted into the database
     */
    public function relate($model, $relations = []);

    /**
     * Count the number of instances.
     *
     * @return int The number of the model stored in the database
     */
    public function count();

    /**
     * Cache buster.
     * Called after the data store is mutated to clear all cached queries results of this repository.
     * Always bust untagged caches if a repository is untagged.
     * Call this function when manually inserting.
     *
     * @param  string|null $key Key to reference cache
     * @return boolean           True
     */
    public function cacheBust($key = null);
}
