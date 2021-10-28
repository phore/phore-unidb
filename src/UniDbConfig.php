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
            if ( ! in_array("sqlite", \PDO::getAvailableDrivers()))
                throw new \RuntimeException("Sqlite driver not installed. Make sure php has sqlite extension enabled.");
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

    private static $strategies = [];

    public static function defineIO(array $strategies, string $exportFile = null, $importFile=null, string $alias="default")
    {
        self::$strategies[$alias] = [
            "importFile" => $importFile,
            "exportFile" => $exportFile,
            "strategies" => $strategies
        ];
    }

    public static function getStrategy(string $alias="default") : array|null
    {
        if ( ! isset (self::$strategies[$alias]))
            return null;
        return self::$strategies[$alias];
    }

}
