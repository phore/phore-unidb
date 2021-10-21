<?php


namespace Phore\UniDb;


use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;

class UniDbConfig
{

    private static $con = [];


    public static function factory($connection, $schema, bool $autocreate_schema = false) : UniDb
    {
        if (str_starts_with($connection, "sqlite:")) {
            $driver = new SqliteDriver(new \PDO($connection));
        } else {
            throw new \InvalidArgumentException("No driver available for $connection");
        }


        $unidb = new UniDb($driver, new Schema($schema));
        if ($autocreate_schema)
            $unidb->createSchema();
        return $unidb;
    }


    public static function define($connection, $schema, string $alias = "default", bool $autocreate_schema=false, bool $lazy_connect=true)
    {
        self::$con[$alias] = [
            "connection" => $connection,
            "schema" => $schema,
            "autocreate_schema" => $autocreate_schema,
            "instance" => $lazy_connect === false ? self::factory($connection, $schema, $autocreate_schema) : null
        ];
    }


    public static function get(string $alias = "default") : UniDb
    {
        if ( ! isset (self::$con[$alias]))
            throw new \InvalidArgumentException("Alias '$alias' not defined");
        $con = self::$con[$alias];
        if ($con["instance"] === null)
            $con["instance"] = self::factory($con["connection"], $con["schema"], $con["autocreate_schema"]);
        return $con["instance"];
    }

}