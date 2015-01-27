<?php namespace Magician\Utils;

class Parser
{
    /**
     * Add where to a query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query A query
     * @param  string $string The where statement to parse
     * @return \Illuminate\Database\Eloquent\Builder        The query
     */
    public function where($query, $string)
    {
        $data = preg_split('/\s+/', $string);

        if (count($data) == 2) {
            $query->where($data[0], $data[1]);
        } else {
            $query->where($data[0], $data[1], $data[2]);
        }

        return $query;
    }
}
