<?php


namespace Phore\UniDb\Driver\PDO;


use Phore\UniDb\Driver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Schema\TableSchema;
use Phore\UniDb\Stmt\Stmt;

class PdoDriver implements Driver
{

    protected Schema $schema;

    protected ?string $connectionString;
    public $lastQuery;



    public function __construct(
        public \PDO|string $PDO
    ){
        if (is_string($this->PDO)) {
            $this->connectionString = $this->PDO;
            $this->PDO = new \PDO($this->connectionString);
        }
    }


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

        foreach ($schema->getPkColsList() as $pkCol) {
            $keys[] = $pkCol;
            $values[] = ":$pkCol";
        }

        $colums = array_keys($schema->getColums());



        if ($schema->getJsonDataCol() !== null) {
            $keys[] = $schema->getJsonDataCol();
            $values[] = ":" . $schema->getJsonDataCol();
        } else {
            foreach ($colums as $indexCol) {
                $keys[] = $indexCol;
                $values[] = ":" . $indexCol;
            }
        }

        return [$keys, $values];
    }


    /**
     * @var \PDOStatement[]
     */
    private $stmtCache_Insert = [];

    public function insert ($table, $data, bool $replaceExisting = false)
    {
        if ( ! isset ($this->stmtCache_Insert[$table . $replaceExisting])) {
            $schema = $this->schema->getSchema($table);

            [$keys, $values] = $this->buildKeyValueArr($schema, (array)$data);

            $replaceSql = "";
            if ($replaceExisting)
                $replaceSql = "OR REPLACE ";

            $sqlStmt = "INSERT {$replaceSql}INTO {$schema->getTableName()} (" . implode(", ", $keys) . ") VALUES (" .
                implode(", ", $values) . ");";
            echo $sqlStmt;
            $this->stmtCache_Insert[$table . $replaceExisting] = $this->PDO->prepare($sqlStmt);
        }

        $stmt = $this->stmtCache_Insert[$table . $replaceExisting];
        $this->lastQuery = $stmt->queryString;
        $stmt->execute((array)$data);
    }

    /**
     * @var \PDOStatement[]
     */
    private $stmtCache_Update = [];
    public function update ($table, $data)
    {
        if ( ! isset ($this->stmtCache_Update[$table])) {
            $schema = $this->schema->getSchema($table);

            [$keys, $values] = $this->buildKeyValueArr($schema, (array)$data);

            $sqlStmt = "REPLACE INTO {$schema->getTableName()} (" . implode(", ", $keys) . ") VALUES (" . implode(", ",
                    $values) . ");";
            $this->stmtCache_Update[$table] = $this->PDO->prepare($sqlStmt);
        }

        $stmt = $this->stmtCache_Update[$table];
        $this->lastQuery = $stmt->queryString;
        $stmt->execute((array)$data);
    }

    public function delete($table, $stmt = null, string $pk = null, $data = null)
    {
        $schema = $this->schema->getSchema($table);
        if ($schema->getPkCol() !== null) {
            $sqlStmt = "DELETE FROM $table  WHERE {$schema->getPkCol()}=" . $this->PDO->quote($s);
        }
    }

    public function query(
        string $table, Stmt $stmt = null, ?int $page = null, ?int $limit = null,
        ?string $orderBy = null, string $orderType="ASC", array $select = null, bool $pkOnly = false,
        ?int &$count = -1
    ) : PdoDriverResult
    {
        $tableSchema = $this->schema->getSchema($table);

        $stmtSql = "";
        if ($stmt !== null && $stmt->hasStmts()) {
            $stmtSql = " WHERE " . $stmt->parseSql($this->PDO);
        }

        $pagesTotal = null;
        $datasetsTotal = null;
        $limitSql = "";
        if (($limit !== null && $limit > 0) || $count !== -1) {
            $sql = "SELECT COUNT(*) FROM {$tableSchema->getTableName()} {$stmtSql}" ;
            $this->lastQuery = $sql;
            try {
                $query = $this->PDO->query($sql);
            } catch (\PDOException $e) {
                throw new \InvalidArgumentException("Query failed: '$sql' error: {$e->getMessage()}", (int)$e->getCode());
            }
            $count = $datasetsTotal = $query->fetch()[0];

            if ($limit > 0) {
                $pagesTotal = ceil($datasetsTotal / $limit);
                $limitSql = " LIMIT " . (($page - 1) * $limit) . ",$limit";
            }
        }

        $selectSql = "*";
        if (is_array($select))
            $selectSql = implode(",", $select);

        $selectFirstElement = false;
        if ($pkOnly === true) {
            $pkCols = $tableSchema->getPkCols();
            if (count ($pkCols) === 0)
                throw new \InvalidArgumentException("pkOnly select not possible: Table '$table' has not Primary Key defined.");
            if (count ($pkCols) === 1)
                $selectFirstElement = true;

            $selectSql = implode(", ", $tableSchema->getPkCols());
        }



        $sql = "SELECT {$selectSql} FROM {$tableSchema->getTableName()} $stmtSql $limitSql";
        $this->lastQuery = $sql;

        try {
            $query = $this->PDO->query($sql);
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Query failed: '$sql' error: {$e->getMessage()}", (int)$e->getCode());
        }

        return new PdoDriverResult($query, $tableSchema, $page, $limit, $pagesTotal, $datasetsTotal, $selectFirstElement);
    }


    public function destroySchema()
    {
        throw new \BadMethodCallException("Cannot destroySchema() on PDODriver. Not implemented.");
    }
}
