<?php


namespace Phore\UniDb\Schema;


class TableSchema
{

    public function __construct(
        public string $tableName,
        public array $tableSchema
    ){}


    public function getPkCol() : string
    {
        return $this->tableSchema["pk_col"] ?? lcfirst($this->tableName) . "_id";
    }

    public function getIndexes() : array
    {
        return $this->tableSchema["indexes"] ?? [];
    }

    public function getDataCol() : string
    {
        return $this->tableSchema["data_col"] ?? "_" . lcfirst($this->tableName) . "_data";
    }

}