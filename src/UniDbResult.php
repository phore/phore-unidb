<?php


namespace Phore\UniDb;


interface UniDbResult
{

    /**
     * Return the current page queried
     *
     * Only available if queried with was called with 'limit' argument
     *
     * @return int|null
     */
    public function getPage() : ?int;


    public function getLimit() : ?int;
    public function getPagesTotal() : ?int;
    public function getCount() : ?int;

    public function each(string|bool $cast = false) : \Generator;

    public function getResult() : array;
}