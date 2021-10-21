<?php


namespace Phore\UniDb\Schema;


class Index implements Constraint
{

    const TYPE_INDEX = "INDEX";
    const TYPE_UNIQUE = "UNIQUE";


    public function __construct(
        public ?string $name = null,
        public array $cols,
        public string $type = self::TYPE_INDEX)
    {
        if ($this->name === null)
            $this->name = "idx_" . implode(
                "_",
                array_map(
                    fn($key, $val) => is_int($key) ? $val : $key,
                    array_keys($this->cols),
                    $this->cols
                )
                );
    }
}