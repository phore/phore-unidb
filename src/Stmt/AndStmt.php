<?php


namespace Phore\UniDb\Stmt;


class AndStmt extends Stmt
{
  public function parseSql(\PDO $pdo) : string
    {
        return implode(" AND ", $this->buildSql($pdo));
    }
}