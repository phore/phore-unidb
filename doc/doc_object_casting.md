# UniDb Object Casting

## Example

```php
$udb = new UniDb(
    new SqliteDriver(new \PDO("sqlite::memory:")),
    new Schema(
        [
            "User" => [
                "class" => UserEntity::class,
                "indexes" => ["user_name"]
            ]
        ]
    )
);

$udb->createSchema();

$entity = new UserEntity();
$entity->user_id = "user1";
$entity->user_name = "Bob";

$udb->insert($entity); // No need to specify the table

foreach ($udb->with(UserEntity::class)->query(stmt: ["user_name", "=", "Bob"], cast: true) as $user) {
    $user instanceof UserEntity ?? throw new \InvalidArgumentException();
    echo $user->user_name;
}
```