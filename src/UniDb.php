<?php


namespace Phore\UniDb;


use Phore\UniDb\Schema\Schema;

class UniDb
{


    public function __construct(
        public Driver $driver,
        public Schema $schema
    ){
        $this->driver->setSchema($this->schema);
    }


    public function createSchema()
    {
        $this->driver->createSchema();
    }

    public function insert(string $table, $data)
    {
        $this->driver->insert($table, $data);
    }

    public function update(string $table, $data)
    {
        $this->driver->update($table, $data);
    }

    public function query(string $table, $stmt) : \Generator
    {
        return $this->driver->query($table, $stmt);
    }



}