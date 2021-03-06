<?php


namespace Phore\UniDb;


use Phore\UniDb\Ex\EmptyResultException;
use Phore\UniDb\Helper\Archive\Archive;
use Phore\UniDb\Helper\Archive\FilesystemArchive;
use Phore\UniDb\Helper\Exporter;
use Phore\UniDb\Helper\IdGenerator;
use Phore\UniDb\Helper\ImportReport;
use Phore\UniDb\Helper\IOStrategy\IOStrategy;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Stmt\Stmt;

class UniDb
{

    public ?UnidbResult $result = null;

    public ?string $preselectedTable = null;

    public function __construct(
        public Driver $driver,
        public Schema $schema
    ){
        $this->driver->setSchema($this->schema);
    }


    /**
     * Select a clone of unidb instance where tablename is already set
     *
     * Shortcut
     *
     * <example>
     * $unidb->with(\Some\Class1::class)->query(stmt: ...);
     * </example>
     *
     * @param string $table Table or ClassName to select as default
     * @return $this
     */
    public function with(string $tableOrClass) : self
    {
        if ( ! $this->schema->isDefined($tableOrClass))
            throw new \InvalidArgumentException("Undefined in schema: Table or class '$tableOrClass'.");
        $unidb = clone($this);
        $unidb->preselectedTable = $tableOrClass;
        return $unidb;
    }

    /**
     * Delete the Database and all data
     *
     * @return void
     */
    public function destroySchema()
    {
        $this->driver->destroySchema();
    }


    public function createSchema() : string
    {
        return $this->driver->createSchema();
    }

    protected function getTableName(string $table = null, $data = null) : string
    {
        if ($table === null && $this->preselectedTable !== null)
            $table = $this->preselectedTable;
        if ($table === null && is_object($data) && $data !== null) {
            $table = $data::class;
        }
        if ($table === null)
            throw new \InvalidArgumentException("No table selected.");
        return $table;
    }

    public function insert(object|array $data, string $table = null, bool $updateExisting = false) : object|array
    {
        $tableName = $this->getTableName($table, $data);

        $pkCols = $this->schema->getSchema($tableName)->getPkCols();
        if (count($pkCols) === 1) {
            $pkName = $pkCols[0];
            if (is_object($data)) {
                if ($data->$pkName === null)
                    $data->$pkName = IdGenerator::guidv4();
            } else {
                if ($data[$pkName] === null)
                    $data[$pkName] = IdGenerator::guidv4();
            }
        }

        $this->driver->insert(
            $tableName,
            $data,
            $updateExisting
        );
        return $data;
    }

    public function update(object|array $data, string $table = null)
    {
        $this->driver->update(
            $this->getTableName($table),
            $data
        );
    }

    public function delete(object|array $data = null, string $table = null, $stmt = null, int $limit = null)
    {
        $this->driver->delete(
            $this->getTableName($table),
            $data, $stmt, $limit
        );
    }

    /**
     *
     * @template T
     * @param null $stmt
     * @param class-string<T>|null $table
     * @param int|null $page
     * @param int|null $limit
     * @param string|null $orderBy
     * @param string $orderType
     * @param string|bool $cast
     * @param string[]|null $select
     * @param bool $pkOnly Return only the plain PrimaryKey
     * @param int|null $count
     * @return \Generator<T>
     */
    public function query(
        $stmt = null, string $table = null, ?int $page = null, ?int $limit = null,
        ?string $orderBy = null, string $orderType="ASC", string|bool $cast = false, array $select = null,
        bool $pkOnly = false, ?int &$count = -1, string|null $groupBy=null) : \Generator
    {
        if ($page !== null && $limit === null)
            throw new \InvalidArgumentException("If 'limit' argument is required if 'page' argument is set.");

        if ( ! in_array($orderType, ["ASC", "DESC"]))
            throw new \InvalidArgumentException("Argument orderType '$orderType' invalid: ASC|DESC");

        if ($stmt !== null) {
            if ( ! $stmt instanceof Stmt)
                $stmt = new Stmt($stmt);
        }

        $this->result = $this->driver->query(
            $this->getTableName($table),
            $stmt, $page, $limit, $orderBy, $orderType, $select, $pkOnly, $count, $groupBy
        );
        return $this->result->each($cast);
    }


    /**
     * Return the first matching result or throw NotFound
     *
     * @template T
     * @param $stmt
     * @param class-string<T>|string|null $table
     * @param bool $cast
     * @return T|array
     * @throws EmptyResultException
     */
    public function select(array|Stmt $stmt=null, string|object|array $byPrimaryKey=null, array $byKeyValue=null, string $table=null, bool $cast = false)
    {
        $tableSchema = $this->schema->getSchema($this->getTableName($table));

        if ($stmt === null) {
            $stmt = new Stmt();
        } else if (is_array($stmt)) {
            $stmt = new Stmt(...$stmt);
        }
        if ($byPrimaryKey !== null) {
            $pkNames = $tableSchema->getPkCols();
            if (count ($pkNames) === 0)
                throw new \InvalidArgumentException("Cannot select byPrimaryKey. No Primary Key defined");

            if (is_string($byPrimaryKey)) {
                if (count($pkNames) > 1)
                    throw new \InvalidArgumentException("Argument byPrimaryKey is string but primary key of table '$table' has multiple pk columns.");
                $byPrimaryKey = [$pkNames[0] => $byPrimaryKey];
            }
            if (is_object($byPrimaryKey))
                $byPrimaryKey = (array)$byPrimaryKey;
            if ( ! is_array($byPrimaryKey))
                throw new \InvalidArgumentException("Argument byPrimaryKey must be of type array on multi column primary keys");
            foreach ($pkNames as $pkName) {
                if ( ! isset ($byPrimaryKey[$pkName]))
                    throw new \InvalidArgumentException("Argument byPrimaryKey is missing key '$pkName'");
                $stmt->append([$pkName, "=", $byPrimaryKey[$pkName]]);
            }
        }

        if ($byKeyValue !== null) {
            foreach ($byKeyValue as $key => $value) {
                if ( ! is_string($key))
                    throw new \InvalidArgumentException("Argument byKeyValue must be map.");
                $stmt->append([$key, "=", $value]);
            }
        }



        $result = $this->query($stmt, table: $table, cast: $cast);
        foreach ($result as $cur)
            return $cur;
        throw new EmptyResultException("Empty result. Query: '" . print_r($stmt, true). "'");
    }


    private function getArchive($zipfile, $path) : Archive
    {
        if ($path !== null)
            return new FilesystemArchive($path);
    }

    /**
     * @param string|null $zipfile
     * @param string|null $path
     * @param array $strategies
     * @param string $alias
     * @return ImportReport[]
     */
    public function export(string $zipfile=null, string $path = null, array $strategies = null, string $alias="default") : array
    {
        if ($strategies === null) {
            $preset = UniDbConfig::getStrategy($alias);
            if ($preset !== null) {
                $strategies = $preset["strategies"];
            }
        }
        $ret = [];
        foreach ($strategies as $tableName =>$strategy) {
            $strategy instanceof IOStrategy ?? throw new \InvalidArgumentException("Invalid strategy object for table '$tableName'.");

            $ret[$tableName] = $strategy->export($this, $tableName, $this->getArchive($zipfile, $path));

        }
        return $ret;
    }

    /**
     * @param string|null $zipfile
     * @param string|null $path
     * @param array $strategies
     * @param string $alias
     * @return ImportReport[]
     */
    public function import(
        string $zipfile = null,
        string $path = null,
        array $strategies = null,
        string $alias = "default"
    ) : array
    {
        if ($strategies === null) {
            $preset = UniDbConfig::getStrategy($alias);
            if ($preset !== null) {
                $strategies = $preset["strategies"];
            }
        }

        $ret = [];
        foreach ($strategies as $tableName => $strategy) {
            $strategy instanceof IOStrategy ?? throw new \InvalidArgumentException("Invalid strategy object for table '$tableName'.");
            $ret[$tableName] = $strategy->import($this, $tableName, $this->getArchive($zipfile, $path));
        }
        return $ret;
    }


    public function getLastQuery() : array
    {
        return $this->driver->getLastQuery();
    }

}
