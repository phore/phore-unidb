<?php


namespace Phore\UniDb\Schema;


use mysql_xdevapi\Table;

class Schema
{

    /**
     * @var TableSchema[]
     */
    private array $tableSchemas = [];

    public function __construct (
        public array $schema = []
    ){
        foreach ($this->schema as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $this->tableSchemas[$value] = null;
                continue;
            }
            if (is_string($key) && is_array($value)) {
                $this->tableSchemas[$key] = new TableSchema(
                    $key,
                    $value["pk_cols"] ?? [],
                    $value["class"] ?? null,
                    $value["indexes"] ?? [],
                    $value["columns"] ?? [],
                    $value["constraints"] ?? [],
                    $value["json_data_col"] ?? null
                );
                continue;
            }
            throw new \InvalidArgumentException("Invalid schema on key: $key data: '" . print_r($value, true). "'");
        }
    }


    public function isDefined(string $tableOrClass) : bool
    {
        return array_key_exists($tableOrClass, $this->tableSchemas);
    }

    public function getSchema(string $tableOrClass) : TableSchema
    {
        if ( ! array_key_exists($tableOrClass, $this->tableSchemas))
            throw new \InvalidArgumentException("No table schema mapped to class '$tableOrClass'");
        if ($this->tableSchemas[$tableOrClass] === null)
            $this->tableSchemas[$tableOrClass] = new EntitySchema($tableOrClass);
        return $this->tableSchemas[$tableOrClass];
    }

    /**
     * @return TableSchema[]|EntitySchema[]
     */
    public function getAllSchemas () : array
    {
        $ret = [];
        foreach ($this->tableSchemas as $key => $curSchema) {
            $ret[] = $this->getSchema($key);
        }
        return $ret;
    }

    /**
     * @return string[]
     */
    public function getSchemaKeys() : array
    {
        return array_keys($this->tableSchemas);
    }

}