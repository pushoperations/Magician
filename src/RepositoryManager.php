<?php namespace Push\MagicRepository;

interface RepositoryManager extends RepositoryInterface
{
    /**
     * Set the managed model.
     *
     * @param  string $name      The namespaced name of the model the manager will be managing
     * @return RepositoryManager The manager instance
     */
    public function set($name);
}
