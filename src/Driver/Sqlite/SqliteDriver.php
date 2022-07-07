<?php


namespace Phore\UniDb\Driver\Sqlite;


use Phore\UniDb\Driver;
use Phore\UniDb\Schema\Index;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Schema\TableSchema;
use Phore\UniDb\Stmt\Stmt;

class SqliteDriver extends Driver\PDO\PdoDriver
{

    const TYPEMAP = [
        "int" => "INTEGER",
        "float" => "FLOAT",
        "string" => "TEXT"
    ];


    private static function _buildColumList(array $colsToCreate, TableSchema $tableSchema) : array
    {
        $ret = [];

        foreach ($colsToCreate as $colName => $type) {
            $type = str_replace("?", "", $type);
            if (isset (self::TYPEMAP[$type]))
                $type = self::TYPEMAP[$type];
            $ret[] = "  {$colName} $type";
        }
        if (count($ret) === 0)
            throw new \InvalidArgumentException("Table '{$tableSchema->getTableName()}' has no columns defined.");

        return $ret;
    }

    private static function _buildPkList($pkCols)
    {
        if (count ($pkCols) === 0)
            return [];
        return [
            "  PRIMARY KEY(" . implode(", ", array_map(fn($in) => $in . " ASC", $pkCols)) . ")"
        ];
    }

    private static function _buildConstraints(array $constraints, TableSchema $tableSchema) : array
    {
        $ret = [];

        foreach ($constraints as $constraint) {
            if ($constraint instanceof Index) {
                $coldef = [];
                foreach ($constraint->cols as $key => $val) {
                    $coldef[] = is_int($key) ? "$val ASC" : "$key $val";
                }
                $coldef = implode(", ", $coldef);

                $unique = $constraint->type === Index::TYPE_UNIQUE ? "UNIQUE " : "";
                $ret[] = "CREATE {$unique}INDEX IF NOT EXISTS {$constraint->name} ON {$tableSchema->getTableName()} ({$coldef});";
                continue;
            }
            throw new \InvalidArgumentException("Cannot parse constraint: " . print_r($constraint, true));
        }
        return $ret;
    }


    public static function buildCreateTableStmt(TableSchema $tableSchema) : string
    {
        $stmt = "CREATE TABLE IF NOT EXISTS {$tableSchema->getTableName()} (\n";

        $cols = array_unique(array_merge(
            self::_buildColumList($tableSchema->getColums(), $tableSchema),
            self::_buildPkList($tableSchema->getPkCols())
        ));
        $stmt .= implode(",\n", $cols) . "\n);\n";
        $stmt .= implode("\n", self::_buildConstraints($tableSchema->getConstraints(), $tableSchema));
        echo $stmt;
        return $stmt;
    }

    public function createSchema() :string
    {
        $stmts = "\n-- Autogenerated schema\n";
        foreach ($this->schema->getAllSchemas() as $tableSchema) {
            $stmts .= "\n" . self::buildCreateTableStmt($tableSchema);
        }

        if (preg_match("/^sqlite:(?<file>.*)$/", $this->connectionString, $matches)) {
            $dbFile = $matches["file"];
            $this->PDO->exec($stmts);
            if ($dbFile !== ":memory:") {
                chmod($dbFile, 0666);
            }
        } else {
            $this->PDO->exec($stmts);
        }

        return $stmts;
    }
}
