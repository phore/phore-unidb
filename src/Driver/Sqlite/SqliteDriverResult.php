<?php


namespace Phore\UniDb\Driver\Sqlite;


use Phore\UniDb\Schema\TableSchema;
use Phore\UniDb\UniDbResult;

class SqliteDriverResult implements UniDbResult
{

    public function __construct(
        private \PDOStatement $statement,
        private TableSchema $tableSchema,
        private ?int $page,
        private ?int $limit,
        private ?int $pagesTotal,
        private ?int $datasetsTotal
    ){}





    public function each(string|bool $cast = false) : \Generator
    {
        while ($data = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = json_decode($data[$this->tableSchema->getDataCol()]);
            if ($cast !== false) {
                if ( ! function_exists("phore_hydrate"))
                    throw new \InvalidArgumentException("Library 'phore/hydrator' is required for object casting.");
                $castClass = $this->tableSchema["class"] ?? null;
                if (is_string($cast))
                    $castClass = $cast;
                $entity = phore_hydrate($entity, $castClass);
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

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getPagesTotal(): ?int
    {
        return $this->pagesTotal;
    }

    public function getCount(): ?int
    {
        return $this->datasetsTotal;
    }
}