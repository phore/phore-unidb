<?php


namespace Phore\UniDb\Driver\PDO;


use Phore\UniDb\Driver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Schema\TableSchema;
use Phore\UniDb\Stmt\Stmt;

class PdoDriver implements Driver
{

    protected Schema $schema;

    public $lastQuery;

    public function __construct(
        public \PDO $PDO
    ){}


    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
    }


    public function createSchema() :string
    {
        throw new \InvalidArgumentException("PdoDriver is not capable of createSchema(). Use dedicated driver instead.");
    }


    private function buildKeyValueArr(TableSchema $schema, array $data) : array
    {
        $keys = [];
        $values = [];

        $keys[] = $schema->getPkCol();
        $values[] = $this->PDO->quote($data[$schema->getPkCol()]);

        foreach ($schema->getIndexes() as $indexCol) {
            $keys[] = $indexCol;
            $values[] = $this->PDO->quote($data[$indexCol]);
        }

        $keys[] = $schema->getDataCol();
        $values[] = $this->PDO->quote(
            json_encode($data,  JSON_PRESERVE_ZERO_FRACTION|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE)
        );
        return [$keys, $values];
    }


    public function insert ($table, $data)
    {
        $schema = $this->schema->getSchema($table);

        [$keys, $values] = $this->buildKeyValueArr($schema, (array)$data);

        $sqlStmt = "INSERT INTO " . $table . " (" . implode(", ", $keys) . ") VALUES (" . implode(", ",
                $values) . ");";

        $this->lastQuery = $sqlStmt;
        $this->PDO->exec($sqlStmt);

    }

    public function update ($table, $data)
    {
        $schema = $this->schema->getSchema($table);

        [$keys, $values] = $this->buildKeyValueArr($schema, (array)$data);

        $sqlStmt = "REPLACE INTO " . $table . " (" . implode(", ", $keys) . ") VALUES (" . implode(", ",
                $values) . ");";

        $this->lastQuery = $sqlStmt;
        $this->PDO->exec($sqlStmt);
    }

    public function delete($table, $stmt = null, string $pk = null, $data = null)
    {
        $schema = $this->schema->getSchema($table);
        if ($schema->getPkCol() !== null) {
            $sqlStmt = "DELETE FROM $table  WHERE {$schema->getPkCol()}=" . $this->PDO->quote($s);
        }
    }

    public function query(
        string $table, $stmt = null, ?int $page = null, ?int $limit = null,
        ?string $orderBy = null, string $orderType="ASC", array $select = null, bool $pkOnly = false
    ) : PdoDriverResult
    {
        $tableSchema = $this->schema->getSchema($table);

        $stmtSql = "";
        if ($stmt !== null) {
            if ( ! $stmt instanceof Stmt)
                $stmt = new Stmt($stmt);
            $stmtSql = " WHERE " . $stmt->parseSql($this->PDO);
        }

        $pagesTotal = null;
        $datasetsTotal = null;
        $limitSql = "";
        if ($limit !== null && $limit > 0) {
            $sql = "SELECT COUNT(*) FROM {$table} {$stmtSql}" ;
            $this->lastQuery = $sql;
            try {
                $query = $this->PDO->query($sql);
            } catch (\PDOException $e) {
                throw new \InvalidArgumentException("Query failed: '$sql' error: {$e->getMessage()}", (int)$e->getCode());
            }
            $datasetsTotal = $query->fetch()[0];

            $pagesTotal = ceil($datasetsTotal / $limit);

            $limitSql = " LIMIT " . (($page - 1) * $limit) . ",$limit";
        }

        $selectSql = "*";
        if (is_array($select))
            $selectSql = implode(",", $select);
        if ($pkOnly === true)
            $selectSql = $tableSchema->getPkCol();


        $sql = "SELECT {$selectSql} FROM $table $stmtSql $limitSql";
        $this->lastQuery = $sql;



        try {
            $query = $this->PDO->query($sql);
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Query failed: '$stmt' error: {$e->getMessage()}", (int)$e->getCode());
        }

        return new PdoDriverResult($query, $tableSchema, $page, $limit, $pagesTotal, $datasetsTotal, $pkOnly);
    }




}