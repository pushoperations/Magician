<?php namespace App\Repositories;

use Push\Magician\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * A complex query.
     *
     * @param  array $params Query parameters
     * @return mixed         The query results
     */
    public function complexQuery($params);
}
