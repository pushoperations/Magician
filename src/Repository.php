<?php namespace Magician;

use Cache;

abstract class Repository implements RepositoryInterface
{
    /**
     * The cache tag for repositories without a tag.
     *
     * @var string
     */
    private $untagged = 'untagged-repositories';

    /**
     * The cache tag for this repository.
     *
     * @var string
     */
    protected $cacheTag = null;

    /**
     * Number of minutes to cache the query results.
     *
     * @var integer
     */
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
        if (is_array($ids) || is_int($ids)) {
            $success = $this->model->destroy($ids);
        } else {
            $success = $ids->delete();
        }

        return $success && $this->cacheBust();
    }

    /**
     * Iterates through the relations, associates each object with the model, and save them into the database.
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
     * Count the number of instances.
     *
     * @return int The number of the model stored in the database
     */
    public function count()
    {
        $tag = $this->cacheTag ?: $this->untagged;

        return $this->getQueryBuilder()
            ->cacheTags(['repositories', 'count', $tag])
            ->remember($this->cacheDuration)
            ->count();
    }

    /**
     * Cache buster.
     * Called after the data store is mutated to clear all cached queries results of this repository.
     * Always bust untagged caches if a repository is untagged.
     * Call this function when manually inserting.
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
     * All repository getters should call this for integrated caching at the repository level.
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
     * Magic finder resolution.
     * Dynamic queries are caught by the __call magic method and parsed here.
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
