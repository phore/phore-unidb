<?php


namespace Phore\UniDb\Helper;


use Phore\UniDb\UniDb;

class Exporter
{

    public function __construct(
        private UniDb $udb
    ){}


    public function export(Writer $writer)
    {
        foreach ($this->udb->schema->getSchemaKeys() as $id) {
            $schema = $this->udb->schema->getSchema($id);
            foreach ($this->udb->with($id)->query(count: $count) as $i => $entity) {
                echo "\n$i / $count...";
                $writer->write($schema, $entity);
            }
        }
    }


}