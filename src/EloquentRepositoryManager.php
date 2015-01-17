<?php namespace Push\MagicRepository;

use App;

class EloquentRepositoryManager extends EloquentMagicRepository implements RepositoryManager
{
    protected $cacheTag = 'repository-manager';

    public function set($name)
    {
        $this->model = App::make($name);

        return $this;
    }
}
