# Querying frontend ready result-sets

[Back to Main docs](../README.md)


## Code

```php
$odb->query(table: "User", limit: 10);
print_r ($odb->result->getResult());
```

## Output

```
Array
(
    [page] => 1
    [limit] => 1
    [pages_total] => 2
    [datasets_total] => 2
    [offset_from] => 1
    [offset_to] => 1
    [data] => Array
        (
            [0] => stdClass Object
                (
                    [tbl1_id] => wurst
                    [data] => abc
                )

        )

)
```