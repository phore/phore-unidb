<?php

return [
    "Tbl1" => [
        "columns" => [
            "col1" => "int"
        ],
        "constraints" => [
            new \Phore\UniDb\Schema\Index(cols: ["col1" => "ASC"]),
            new \Phore\UniDb\Schema\Index(cols: ["col1"]),
            new \Phore\UniDb\Schema\Index(cols: ["col1"], type: \Phore\UniDb\Schema\Index::TYPE_UNIQUE),
        ]
    ]
];