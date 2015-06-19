<?php namespace Push\Magician;

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
     * Create a new instance of the Magician repository.
     *
     * @param  string $name The namespaced name of the model the manager will be managing
     * @return void
     */
    public function __construct($name = null)
    {
        if ($name) {
            $this->set($name);
        }
    }

    /**
     * Set the managed model.
     *
     * @param  string $name The namespaced name of the model the manager will be managing
     * @return Magician     The Magician instance itself
     */
    public function set($name)
    {
        $this->model = App::make($name);
        $this->cacheTag = $this->tagify($name).'-magician-repository';

        return $this;
    }

    /**
     * Generate a formatted string from a given namespaced string.
     *
     * @param  string $string
     * @return string
     */
    private function tagify($string)
    {
        return strtolower(preg_replace('/\\\/', '-', $string));
    }
}
