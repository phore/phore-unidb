<?php


namespace Phore\UniDb\Driver\Sqlite;


use Phore\UniDb\Schema\TableSchema;
use Phore\UniDb\UniDbResult;

class SqliteDriverResult implements UniDbResult
{

    public function __construct(
        public \PDOStatement $statement,
        public TableSchema $tableSchema,
        public ?int $page,
        public ?int $limit,
        public ?int $pagesTotal,
        public ?int $datasetsTotal
    ){}





    public function each(bool $cast = false) : \Generator
    {
        while ($data = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = json_decode($data[$this->tableSchema->getDataCol()]);
            if ($cast === true) {
                if ( ! function_exists("phore_hydrate"))
                    throw new \InvalidArgumentException("Library 'phore/hydrator' is required for object casting.");
                $entity = phore_hydrate($entity, $data);
            }
            yield $entity;
        }
    }


    public function getResult() : array
    {
        $result = [
            "page" => $this->page,
            "limit" => $this->limit,
            "pages_total" => $this->pagesTotal,
            "datasets_total" => $this->datasetsTotal,
            "offset_from" => $this->limit * $this->page,
            "offset_to" => $this->limit * $this->page + $this->limit - 1,
            "data" => []
        ];
        foreach ($this->each(false) as $data) {
            $result["data"][] = $data;
        }
        return $result;
    }

}