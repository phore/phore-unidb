<?php


namespace Test\Entities;


use Phore\UniDb\Attribute\UniDbEntity;

#[UniDbEntity(pk: "entity_id", json_data_col: "data1")]
class TestEntity2
{
    public function __construct(
        public ?string $entity_id,
        public string $data1
    ){}

}