<?php


namespace Phore\UniDb;


use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Stmt\Stmt;

interface Driver
{

    public function setSchema(Schema $schema);

    /**
     * Create the schema and apply it to the database
     *
     * @return string The generated schema
     */
    public function createSchema() : string;

    public function insert ($table, $data, bool $replaceExisting = false);
    public function update ($table, $data);

    public function query(
        string $table, Stmt $stmt = null, ?int $page = null, ?int $limit = null,
        ?string $orderBy = null, string $orderType="ASC"
    ) : UniDbResult;

}