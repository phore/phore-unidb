<?php


namespace Test\Entities;


use Phore\UniDb\Attribute\UniDbEntity;

#[UniDbEntity(pk: "entity_id")]
class TestEntity2
{
    public function __construct(
        public string $entity_id,
        public string $data1
    ){}

}