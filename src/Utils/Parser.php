<?php namespace Magician\Utils;

class Parser
{
    /**
     * The query.
     *
     * @var  \Illuminate\Database\Eloquent\Builder
     */
    private $query;

    /**
     * Create the parser.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query A query
     * @return void
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Get the query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Add a where clause to the query.
     *
     * @param  string $string The where statement to parse
     * @return Parser         The parser instance itself
     */
    public function where($string)
    {
        $data = preg_split('/\s+/', $string);

        if (count($data) == 2) {
            $this->query->where($data[0], $data[1]);
        } elseif (count($data) == 3) {
            $this->query->where($data[0], $data[1], $data[2]);
        }

        return $this;
    }

    /**
     * Add ordering to the query.
     *
     * @param  array|null $order The column to order by
     * @return Parser            The parser instance itself
     */
    public function order(array $order = null)
    {
        if ($order) {
            $orderKey = array_shift($order) ?: null;
            $orderDir = array_shift($order) ?: null;
            $this->query->orderBy($orderKey, $orderDir);
        }

        return $this;
    }

    // Magic finder parsing

    /**
     * Add the qualifying where statement to the query.
     *
     * @param  string       $finder The column to look in
     * @param  array|string $finder The comparison equality
     * @return Parser               The parser instance itself
     */
    public function qualify($finder, $qualifier)
    {
        if (is_array($qualifier)) {
            $equality = array_shift($qualifier);
            $qualifier = array_shift($qualifier);
        } else {
            $equality = '=';
        }

        $this->query->where($finder, $equality, $qualifier);

        return $this;
    }

    /**
     * Set the direction of the query.
     *
     * @param  string|null $direction The date direction to order by
     * @return Parser                 The parser instance itself
     */
    public function direction($direction = null)
    {
        if ($direction == 'Latest') {
            $this->query->orderBy('created_at', 'desc');
        } elseif ($direction == 'Oldest') {
            $this->query->orderBy('created_at', 'asc');
        }

        return $this;
    }
}
