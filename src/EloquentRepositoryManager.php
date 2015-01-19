<?php namespace Push\MagicRepository;

use App;

class EloquentRepositoryManager extends EloquentMagicRepository implements RepositoryManager
{
    /**
     * The cache tag for this repository.
     *
     * @var string
     */
    protected $cacheTag = 'repository-manager';

    /**
     * Set the managed model.
     *
     * @param  string $name      The namespaced name of the model the manager will be managing
     * @return RepositoryManager The manager instance
     */
    public function set($name)
    {
        $this->model = App::make($name);

        return $this;
    }
}
