<?php


namespace Phore\UniDb;


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
    public function with(string $table) : self
    {
        $unidb = clone($this);
        if (strpos("\\", $table) !== false)
            $table = $this->schema->getSchema(class: $table)->tableName;
        $unidb->preselectedTable = $table;
        return $unidb;
    }

    public function createSchema()
    {
        $this->driver->createSchema();
    }

    protected function getTableName(string $table = null, $data = null) : string
    {
        if ($table === null)
            $table = $this->preselectedTable;
        if ($table === null && is_object($data) && $data !== null)
            $table = $this->schema->getSchema(class: get_class($data))->tableName;
        if ($table === null)
            throw new \InvalidArgumentException("No table selected.");
        return $table;
    }

    public function insert($data, string $table = null)
    {
        $this->driver->insert(
            $this->getTableName($table, $data),
            $data
        );
    }

    public function update($data, string $table = null)
    {
        $this->driver->update(
            $this->getTableName($table),
            $data
        );
    }

    public function delete($data = null, string $table = null, $stmt = null, int $limit = null)
    {
        $this->driver->delete(
            $this->getTableName($table),
            $data, $stmt, $limit
        );
    }

    public function query(
        $stmt = null, string $table = null, ?int $page = null, ?int $limit = null,
        ?string $orderBy = null, string $orderType="ASC") : \Generator
    {
        if ($page !== null && $limit === null)
            throw new \InvalidArgumentException("If 'limit' argument is required if 'page' argument is set.");

        if ( ! in_array($orderType, ["ASC", "DESC"]))
            throw new \InvalidArgumentException("Argument orderType '$orderType' invalid: ASC|DESC");

        $this->result = $this->driver->query(
            $this->getTableName($table),
            $stmt, $page, $limit, $orderBy, $orderType
        );
        return $this->result->each();
    }



}