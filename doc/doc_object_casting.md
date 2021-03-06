# UniDb Object Casting


## Example

Define a entity class:
```php
class UserEntity {

    public function __construct(
        /**
         * @var string
         */
        public $user_id,

        /**
         * @var string
         */
        public $user_name
    ){}

}
```


```php
$udb = new \Phore\UniDb\UniDb(
    new \Phore\UniDb\Driver\Sqlite\SqliteDriver(new \PDO("sqlite::memory:")),
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