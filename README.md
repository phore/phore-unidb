# phore-unidb
Unified DB Access

Features:
- Easy to use thanks to [PHP8 Named Arguments](https://www.php.net/manual/en/functions.arguments.php) features
- 

## Basic example

```php
// Setup table structure and driver
$udb = new UniDb(
    new SqliteDriver(new \PDO("sqlite::memory:")),
    new Schema(
        [
            "User" => [
                "indexes" => ["user_name"]
            ]
        ]
    )
);

// Create the schema (if it does not already exist)
echo $udb->createSchema();

// Select the 'User' Table
$userTbl = $udb->with("User");

// Insert two entities
$userTbl->insert(["user_id"=>"user1", "user_name" => "Bob"]);
$userTbl->insert(["user_id"=>"user2", "user_name" => "Alice"]);

// Query all datasets with user_name='Bob' OR user_name='Alice'
foreach ($userTbl->query(stmt: new OrStmt(["user_id", "=", "Bob"], ["user_id", "=", "Alice"])) as $data) {
    print_R ($data);
}
```


## Installation

```bash
composer require phore/unidb
```

## Defining the schema

UniDb requires basic information about the schema to run queries against.

## Querying data

```php
public  UniDb::query(
    $stmt = null, 
    string $table = null, 
    int $page = null, 
    int $limit = null,
    string $orderBy = null, 
    string $orderType="ASC",
    bool $cast = false
) : \Generator
```

| Named Argument  | Description                                                    |
|-----------------|----------------------------------------------------------------|
| `stmt`          | The Statement: either class `AndStmt` or `OrStmt`. If null, all data will be queried  |
| `table`         | Override the default table setting defined using `UniDb::with()`                      |
| `cast`          | If `true` it will cast the structure into the Object defined                          |

***Accessing the data using generators***

The easies way to access the data is to use generators:

```php
foreach ($odb->query(table: "User") as $user) {
    print_r ($user); // Will output
}
```

***Accessing full Result Set / Limit results / Page offsets***

```php
$odb->query(table: "User", limit: 10, page: 1);
print_r ($odb->result->getResult());
```

[See output / full example](doc/doc_result_set.md)


***Using Object Casting / Entities***

UniDb can work with Objects and therefor uses [phore/hydrator](https://github.com/phore/phore-hydrator) to 
cast the result set into objects. Activate this feature by specifying `cast: SomeClass::class` in Argument list.

> To use casting functionality you have to add package `phore/hydrator` to your composer.json requirements

```php 
foreach ($odb->query(table: "User", cast: User::class) as $obj) {
    print_r ($obj); // Instance of User class
}
```

[See details manual page for Object casting]()


***Quering all data of a table***

```
$odb->query(table: "User")
```

***Sorting the data***




## CRUD Operations


