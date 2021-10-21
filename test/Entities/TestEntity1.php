<?php


namespace Test\Entities;


use Phore\UniDb\Attribute\UniDbEntity;

#[UniDbEntity(pk: "entity_id")]
class TestEntity1
{

    public string $entity_id;
}