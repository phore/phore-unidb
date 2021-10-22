<?php


namespace Phore\UniDb;


use Phore\UniDb\Helper\Exporter;
use Phore\UniDb\Helper\IdGenerator;
use Phore\UniDb\Schema\Schema;

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

    public function insert(object|array $data, string $table = null) : object|array
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
            $data
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
     * @param null $stmt
     * @param string|null $table
     * @param int|null $page
     * @param int|null $limit
     * @param string|null $orderBy
     * @param string $orderType
     * @param string|bool $cast
     * @param string[]|null $select
     * @param bool $pkOnly      Return only the plain PrimaryKey
     * @return \Generator
     */
    public function query(
        $stmt = null, string $table = null, ?int $page = null, ?int $limit = null,
        ?string $orderBy = null, string $orderType="ASC", string|bool $cast = false, array $select = null,
        bool $pkOnly = false, ?int &$count = -1) : \Generator
    {
        if ($page !== null && $limit === null)
            throw new \InvalidArgumentException("If 'limit' argument is required if 'page' argument is set.");

        if ( ! in_array($orderType, ["ASC", "DESC"]))
            throw new \InvalidArgumentException("Argument orderType '$orderType' invalid: ASC|DESC");

        $this->result = $this->driver->query(
            $this->getTableName($table),
            $stmt, $page, $limit, $orderBy, $orderType, $select, $pkOnly, $count
        );
        return $this->result->each($cast);
    }


    public function exporter() : Exporter
    {
        return new Exporter($this);
    }

    public function import(
        string $zipfile = null,
        string $path = null,
        array $strategy = []
    )
    {

    }

}