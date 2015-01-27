<?php namespace App\Repositories;

use Magician\RepositoryInterface;

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
