<?php


namespace Phore\UniDb\Helper;


use Phore\UniDb\Schema\TableSchema;

interface Writer
{
    public function write(TableSchema $schema, $data);
}