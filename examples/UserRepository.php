<?php namespace App\Repositories;

use Models\User;
use Magician\Repository;

class UserRepository extends Repository implements UserRepositoryInterface
{
    /**
     * The cache tag for this repository.
     *
     * @var string
     */
    protected $cacheTag = 'user-repository';

    /**
     * Let DI set the managed model.
     *
     * @param  Models\User $model The model the repository will be managing
     * @return void
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * A complex query.
     *
     * @param  array $params Query parameters
     * @return Model|mixed   The query results
     */
    public function complexQuery($params)
    {
        // Do query here and return the results
    }
}
