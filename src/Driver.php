<?php


namespace Phore\UniDb;


use Phore\UniDb\Schema\Schema;

interface Driver
{

    public function setSchema(Schema $schema);

    public function createSchema();

    public function insert ($table, $data);
    public function update ($table, $data);

    public function query(string $table, $stmt);

}