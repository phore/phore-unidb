<?php
namespace Docs;

use Phore\UniDb\Attribute\UniDbColumn;
use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\UniDb;

/**
 * Class UserEntity
 * @internal
 */
class UserEntity {

    public function __construct(
        /**
         * @var string
         */
        #[UniDbColumn(primaryKey: true)]
        public $user_id,

        /**
         * @var string
         */
        #[UniDbColumn(type: "VARCHAR(255)")]
        public $user_name
    ){}

}


$udb = new UniDb(
    new SqliteDriver(new \PDO("sqlite::memory:")),
    new Schema(
        [
            UserEntity::class
        ]
    )
);

$udb->createSchema();

$entity = new UserEntity(user_id: "user1", user_name: "Bob");

$udb->insert($entity); // No need to specify the table

foreach ($udb->with(UserEntity::class)->query(stmt: ["user_name", "=", "Bob"], cast: true) as $user) {
    $user instanceof UserEntity ?? throw new \InvalidArgumentException();
    echo $user->user_name;
}
