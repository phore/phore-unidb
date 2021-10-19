<?php


namespace Phore\UniDb\Schema;


class Schema
{

    private array $classToTableName = [];

    public function __construct (
        /**
         * @var TableSchema[]
         */
        public $schema = []
    ){
        foreach ($this->schema as $key => $value) {
            if (isset ($value["class"]))
                $this->classToTableName[$value["class"]] = $key;
        }
    }


    public function getSchema(string $table = null, string $class = null) : TableSchema
    {
        if ($class !== null)
            $table = $this->classToTableName[$class] ?? throw new \InvalidArgumentException("No table schema mapped to class '$class'");
        return new TableSchema($table, $this->schema[$table]) ?? throw new \InvalidArgumentException("No schema defined for table '$table'");
    }

    public function getTableNames () : array
    {
        return array_keys($this->schema);
    }

}