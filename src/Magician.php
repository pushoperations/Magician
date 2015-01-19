<?php namespace Magician;

use App;

class Magician extends Repository
{
    /**
     * The cache tag for this repository.
     *
     * @var string
     */
    protected $cacheTag = 'magician-repository';

    /**
     * Set the managed model.
     *
     * @param  string $name The namespaced name of the model the manager will be managing
     * @return Magician     The Magician instance itself
     */
    public function set($name)
    {
        $this->model = App::make($name);

        return $this;
    }
}
