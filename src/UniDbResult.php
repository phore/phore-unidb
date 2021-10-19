<?php


namespace Phore\UniDb;


interface UniDbResult
{


    public function each(bool $cast = false) : \Generator;

    public function getResult() : array;
}