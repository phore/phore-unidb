<?php


namespace Test\Entities;


use Phore\UniDb\Attribute\UniDbEntity;
use Phore\UniDb\Helper\IdGenerator;

#[UniDbEntity(pk: "entity_id")]
class TestEntity1
{
    public function __construct(
        public string $entity_id
    ){}

}