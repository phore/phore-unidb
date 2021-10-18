<?php


namespace Phore\UniDb\Schema;


class Schema
{

    public function __construct (
        /**
         * @var TableSchema[]
         */
        public $schema = []
    ){}


    public function getSchema(string $table) : TableSchema
    {
        return new TableSchema($table, $this->schema[$table]) ?? throw new \InvalidArgumentException("No schema defined for table '$table'");
    }

    public function getTableNames () : array
    {
        return array_keys($this->schema);
    }

}