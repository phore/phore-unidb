<?php


namespace Phore\UniDb\Driver\Sqlite;


use Phore\UniDb\Driver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Schema\TableSchema;
use Phore\UniDb\Stmt\Stmt;

class SqliteDriver implements Driver
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


    public function createSchema()
    {

        foreach ($this->schema->getTableNames() as $tableName) {
            $schema = $this->schema->getSchema($tableName);

            $stmt = "CREATE TABLE IF NOT EXISTS $tableName (\n";
            $defs = [$schema->getPkCol() . " VARCHAR PRIMARY KEY ASC"];
            foreach ($schema->getIndexes() as $index) {
                $defs[] = $index . " VARCHAR UNIQUE";
            }
            $defs[] = $schema->getDataCol() . " TEXT";

            $stmt .= implode(", ", $defs);
            $stmt .= ");";
            $this->PDO->exec($stmt);
        }


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
        ?string $orderBy = null, string $orderType="ASC"
    ) : SqliteDriverResult
    {
        if ( ! $stmt instanceof Stmt)
            $stmt = new Stmt($stmt);

        $tableSchema = $this->schema->getSchema($table);

        $pagesTotal = null;
        $datasetsTotal = null;
        $limitSql = "";
        if ($limit !== null && $limit > 0) {
            $sql = "SELECT COUNT(*) FROM $table WHERE " . $stmt->parseSql($this->PDO);
            $this->lastQuery = $sql;
            try {
                $query = $this->PDO->query($sql);
            } catch (\PDOException $e) {
                throw new \InvalidArgumentException("Query failed: '$stmt' error: {$e->getMessage()}", (int)$e->getCode());
            }
            $datasetsTotal = $query->fetch()[0];

            $pagesTotal = ceil($datasetsTotal / $limit);

            $limitSql = " LIMIT " . (($page - 1) * $limit) . ",$limit";
        }


        $sql = "SELECT * FROM $table WHERE " . $stmt->parseSql($this->PDO) . $limitSql;
        $this->lastQuery = $sql;



        try {
            $query = $this->PDO->query($sql);
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Query failed: '$stmt' error: {$e->getMessage()}", (int)$e->getCode());
        }

        return new SqliteDriverResult($query, $tableSchema, $page, $limit, $pagesTotal, $datasetsTotal);
    }




}