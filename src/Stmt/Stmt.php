<?php


namespace Phore\UniDb\Stmt;


class Stmt
{

    protected array $stmts;


    public function __construct(...$stmts)
    {
        $this->stmts = $stmts;
    }

    public function hasStmts() : bool
    {
        return count($this->stmts) > 0;
    }

    protected function buildSql(\PDO $pdo) : array
    {
        $stmts = [];
        foreach($this->stmts as $curStmt) {
            if ($curStmt instanceof Stmt) {
                $stmts[] = "(" . $curStmt->parseSql($pdo) . ")";
                continue;
            }
            if (is_array($curStmt) && count($curStmt) === 3) {
                if ( ! in_array($curStmt[1], ["=", "<>", "<", ">", "~"]))
                    throw new \InvalidArgumentException("Invalid operator '{$curStmt[1]}' in stmt ". print_r($curStmt, true));
                if ($curStmt[1] === "~")
                    $curStmt[1] = " LIKE ";
                $stmts[] = $curStmt[0] . $curStmt[1] . $pdo->quote($curStmt[2]);
                continue;
            }
            throw new \InvalidArgumentException("Invalid stmt: '".print_r($curStmt, true)."'");

        }
        return $stmts;
    }

    public function append($stmt)
    {
        $this->stmts[] = $stmt;
    }

    public function parseSql(\PDO $pdo) : string
    {
        return (new AndStmt(...$this->stmts))->parseSql($pdo);
    }

}
