<?php namespace Push\MagicRepository;

use Cache;

abstract class EloquentMagicRepository implements RepositoryInterface
{
    private $untagged = 'untagged-repositories';

    protected $cacheTag = null;

    protected $cacheDuration = 60;

    /**
     * Create a new instance.
     *
     * @param  array $attributes Attributes to instantiate the model with
     * @return \Illuminate\Database\Eloquent\Model The model object
     */
    public function make(array $attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    /**
     * Find an existing or create a new instance.
     *
     * @param  array $attributes Attributes to query/instantiate the model with
     * @return \Illuminate\Database\Eloquent\Model The model object
     */
    public function firstOrMake(array $attributes = [])
    {
        return $this->model->firstOrNew($attributes);
    }

    /**
     * Magic Method for handling dynamic functions.
     *
     * Handle the construction of dynamic where clauses
     *
     * @param string $method     The name of the method called
     * @param array  $parameters The parameters passed to the method
     */
    public function __call($method, $parameters)
    {
        $pattern = '/^(find|get)(Latest|Oldest)?(\d*?)By(.*)/';
        $matches = null;

        if (preg_match($pattern, $method, $matches)) {
            return $this->dynamicFind($matches, $parameters);
        }

        return self::__call($method, $parameters);
    }

    /**
     * Persist an instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model The model to persist
     * @return boolean True if the instance is successfully persisted into the database
     */
    public function save($model)
    {
        return $model->save() && $this->cacheBust();
    }

    /**
     * Persist data.
     *
     * @param  array   $fields The fields of a table to persist
     * @return boolean         True if the data is successfully persisted into the database
     */
    public function insert(array $fields)
    {
        return $this->model->insert($fields) && $this->cacheBust();
    }

    /**
     * Delete (via finding) an instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array|integer $ids The model/an array of ids/an id to delete
     * @return integer|boolean The number of instances deleted or true if the specified instance was deleted
     */
    public function delete($ids)
    {
        if (is_array($ids)) {
            $success = $this->model->destroy($ids);
        } elseif (is_int($ids)) {
            $success = $query->find($ids)->delete();
        } else {
            $success = $ids->delete();
        }

        return $success && $this->cacheBust();
    }

    /**
     * Associate multiple instances to an object and save into the database.
     *
     * Iterates through the relations and associates each object with the model
     *
     * @param  \Illuminate\Database\Eloquent\Model $model The model object to associate
     * @param  array   $relations The instances to be associated
     * @return boolean            True if the model is successfully persisted into the database
     */
    public function relate($model, $relations = [])
    {
        foreach ($relations as $relate => $object) {
            if (isset($object)) {
                $model->$relate()->associate($object);
            }
        }

        $model->push();

        return $model->save() && $this->cacheBust();
    }

    /**
     * Cache buster.
     *
     * Called after the data store is mutated to clear all cached queries results of this repository
     * Always bust untagged caches if a repository is untagged
     * Call this function when manually inserting
     *
     * @param  string|null  $key Key to reference cache
     * @return boolean           True
     */
    public function cacheBust($key = null)
    {
        Cache::tags($this->cacheTag ?: $this->untagged)->flush();

        if ($key) {
            Cache::forget($key);
        }

        return true;
    }

    /**
     * Query execution function called by getters.
     *
     * All repository getters should call this for integrated caching at the repository level
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array       $columns The columns to retrieve
     * @param  boolean     $first   Limit to one result or not
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    protected function executeQuery($query, $first)
    {
        $tag = $this->cacheTag ?: $this->untagged;

        if ($first) {
            return $query->cacheTags(['repositories', $tag])->remember($this->cacheDuration)->first();
        } else {
            return $query->cacheTags(['repositories', $tag])->remember($this->cacheDuration)->get();
        }

    }

    /**
     * Return a new query instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQueryBuilder()
    {
        return $this->model->newQuery();
    }

    /**
     * Parse options for a query object.
     *
     * Attach parameters passed as a nested array to a query
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $options Query option nested by column-value pairs ['=' => ['key' => 'value']];
     * @param  array $order   ['key' => 'direction'];
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function parseOptions($query, array $where = [], array $order = [])
    {
        $whereScope = ['=', '>', '<', '>=', '<='];
        $orderScope = ['asc', 'desc'];

        foreach ($where as $option => $values) {
            if (in_array($option, $whereScope)) {
                foreach ($values as $key => $value) {
                    // TODO: make sure multiple wheres work
                    $query->where($key, $option, $value);
                }
            }
        }

        foreach ($order as $field => $value) {
            if (in_array(strtolower($value, $orderScope))) {
                $query->orderBy($field, $value);
            }
        }

        return $query;
    }

    /**
     * Magic finder resolution.
     *
     * Dynamic queries are caught by the __call magic method and parsed here
     *
     * @param  string $term       The specific type of getter
     * @param  string $method     The name of the method called
     * @param  array  $parameters The parameters passed to the method
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    private function dynamicFind(array $method, $parameters)
    {
        $term      = $method[1];
        $direction = $method[2] ?: null;
        $count     = $method[3] ?: null;

        // The column to look in
        $finder = snake_case($method[4]);

        // The parameter to use
        $qualifier = isset($parameters[0]) ? $parameters[0] : null;

        if (!$qualifier) {
            return null;
        } elseif (is_array($qualifier)) {
            $equality = array_shift($qualifier);
            $qualifier = array_shift($qualifier);
        } else {
            $equality = '=';
        }

        // The order to list results
        $order = isset($parameters[1]) ? $parameters[1] : null;

        // The columns to select
        $columns = isset($parameters[2]) ? $parameters[2] : ['*'];

        $query = $this->getQueryBuilder();
        $query->where($finder, $equality, $qualifier);
        $query->select($columns);

        if ($order) {
            $orderKey = array_shift($order) ?: null;
            $orderDir = array_shift($order) ?: null;
            $query->orderBy($orderKey, $orderDir);
        }

        if ($direction == 'Latest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($direction == 'Oldest') {
            $query->orderBy('created_at', 'asc');
        }

        if ($term != 'find' && $count) {
            $query->take($count);
        }

        if ($term == 'find') {
            return $this->executeQuery($query, true);
        } else {
            return $this->executeQuery($query, false);
        }
    }
}