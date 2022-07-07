<?php


namespace Phore\UniDb\Schema;


use Phore\UniDb\Attribute\UniDbColumn;
use Phore\UniDb\Attribute\UniDbEntity;
use Phore\UniDb\Attribute\UniDbIndex;

class EntitySchema extends TableSchema
{

    public UniDbEntity|null $entity = null;

    /**
     * @var UniDbColumn[]
     */
    public array $property = [];

    public array $propertyToColumnMap = [];
    public array $columnToPropertyMap = [];

    private function getAttribute(\ReflectionClass|\ReflectionProperty $reflection, $className, bool $multi=false) : null|object|array
    {
        $attrs = $reflection->getAttributes($className);

        if ($multi === true) {
            $ret = [];
            foreach ($attrs as $attr) {
                $ret[] = $attr->newInstance();
            }
            return $ret;
        }
        if (count ($attrs) === 0)
            return null;
        return $attrs[0]->newInstance();
    }


    public function __construct(string $className)
    {
        if ( ! class_exists($className))
            throw new \InvalidArgumentException("Entity class '$className' not existing.");

        $refClass = new \ReflectionClass($className);

        $this->entity = $this->getAttribute($refClass, UniDbEntity::class);
        if ($this->entity === null)
            $this->entity = new UniDbEntity(table: $refClass->getShortName());

        if ($this->entity->table === null)
            $this->entity->table = $refClass->getShortName();

        $columns = [];
        foreach ($refClass->getProperties() as $propertyRef) {
            $propAttr = $this->getAttribute($propertyRef, UniDbColumn::class);
            if ($propAttr === null) {
                $propAttr = new UniDbColumn(
                    type: (string)$propertyRef->getType(),
                    column: $propertyRef->name
                );
            }

            if ($propAttr->column === null)
                $propAttr->column = $propertyRef->getName();

            if ($propAttr->column !== $propertyRef->name) {
                $this->columnToPropertyMap[$propAttr->column] = $propertyRef->name;
                $this->propertyToColumnMap[$propertyRef->name] = $propAttr->column;
            }
            $this->property[$propertyRef->getName()] = $propAttr;
            $columns[$propAttr->column] = $propAttr->type;
        }

        $primaryKeyColums = [];
        if (is_string($this->entity->pk))
            $this->entity->pk = [$this->entity->pk];
        foreach ($this->entity->pk as $pkProperty) {
            if ( ! isset ($this->property[$pkProperty]))
                throw new \InvalidArgumentException("Property '$pkProperty' is defined as primary key of '$className' but does not exist.");
            $primaryKeyColums[] = $this->property[$pkProperty]->column;
        }

        $indexes = [];
        foreach ($this->getAttribute($refClass, UniDbIndex::class, true) as $cur) {
            $cur instanceof UniDbIndex ?? throw new \InvalidArgumentException();

            $indexes[] = new Index($cur->name, $cur->cols, $cur->type);
        }

        parent::__construct(
            tableName: $this->entity->table,
            pk_col: $primaryKeyColums,
            class: $className,
            columns: $columns,
            constraints: $indexes,
            jsonDataCol: $this->entity->json_data_col
        );
    }
}
