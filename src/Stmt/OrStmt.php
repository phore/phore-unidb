<?php


namespace Phore\UniDb\Stmt;


class OrStmt extends Stmt
{


    public function __construct (...$stmts)
    {
        $this->stmts = $stmts;
    }


    public function parseSql(\PDO $pdo) : string
    {
        return implode(" OR ", $this->buildSql($pdo));
    }


}