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
     * Add a where clause to a query.
     *
     * @param  string $string The where statement to parse
     * @return \Illuminate\Database\Eloquent\Builder        The query
     */
    public function where($string)
    {
        $data = preg_split('/\s+/', $string);

        if (count($data) == 2) {
            $this->query->where($data[0], $data[1]);
        } else {
            $this->query->where($data[0], $data[1], $data[2]);
        }

        return $this;
    }

    /**
     * Add ordering to a query.
     *
     * @param  array|null $order The column to order by
     * @return \Illuminate\Database\Eloquent\Builder        The query
     */
    public function order(array $order)
    {
        if ($order) {
            $orderKey = array_shift($order) ?: null;
            $orderDir = array_shift($order) ?: null;
            $this->query->orderBy($orderKey, $orderDir);
        }

        return $this;
    }

    // Magic finder parsing

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

    public function direction($direction)
    {
        if ($direction == 'Latest') {
            $this->query->orderBy('created_at', 'desc');
        } elseif ($direction == 'Oldest') {
            $this->query->orderBy('created_at', 'asc');
        }

        return $this;
    }
}
