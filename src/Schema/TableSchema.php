<?php


namespace Phore\UniDb\Schema;


use Phore\UniDb\Attribute\UniDbEntity;

class TableSchema
{


    public function __construct(
        protected string $tableName,
        protected array|string $pk_col,
        protected ?string $class = null,
        protected array $indexCols = [],
        protected array $columns = [],
        protected array $constraints = [],
        protected ?string $jsonDataCol = null
    ){
        if ( ! is_array($this->pk_col))
            $this->pk_col = [$this->pk_col];
    }

    public function getTableName() : string
    {
        return $this->tableName;
    }


    public function getPkCols() : array
    {
        return $this->pk_col;
    }

    public function getClass() : ?string
    {
        return $this->class;
    }

    /**
     * @return string[]
     */
    public function getIndexCols() : array
    {
        return $this->indexCols;
    }

    public function getColums() : array
    {
        return $this->columns;
    }

    /**
     * @return Index[]|Constraint[]
     */
    public function getConstraints() : array
    {
        return $this->constraints;
    }

    public function getJsonDataCol() : ?string
    {
        return $this->jsonDataCol;
    }

    public function hydrateEntity(array $input) : object|array
    {
        return phore_hydrate($input, $this->class);
    }
}