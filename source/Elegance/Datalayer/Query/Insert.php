<?php

namespace Elegance\Datalayer\Query;

class Insert extends BaseQuery
{
    protected array $columns = [];
    protected array $values = [];

    /** Array de Query para execução */
    function query(): array
    {
        $this->check(['table']);

        $values = [];

        if (empty($this->columns)) {
            $query = 'INSERT INTO [#table] VALUES (null)';
        } else {
            $query = 'INSERT INTO [#table] [#column] VALUES [#values];';
        }

        $query = prepare($query, [
            'table'  => $this->mountTable(),
            'column'  => $this->mountColumn(),
            'values'  => $this->mountValues(),
        ]);
        foreach ($this->values as $pos => $value) {
            foreach ($this->columns as $field) {
                if (isset($value[$field])) {
                    $values["${field}_${pos}"] = $value[$field];
                }
            }
        }

        return [$query, $values];
    }

    /** Executa a query */
    function run(?string $datalayer = null): bool|int
    {
        return parent::run($datalayer);
    }


    /** Define os registros para inserção */
    function values(): self
    {
        $this->columns = [];
        $this->values = [];
        foreach (func_get_args() as $register) {
            $insert = [];
            foreach ($register as $field => $value) {
                if (!is_numeric($field)) {
                    $insert[$field] = $value;
                    $this->columns[$field] = true;
                }
            }
            $this->values[] = $insert;
        }
        $this->columns = array_keys($this->columns);

        return $this;
    }

    protected function mountColumn(): string
    {
        return '(`' . implode('`, `', $this->columns) . '`)';
    }

    protected function mountValues(): string
    {
        $inserts = [];
        foreach ($this->values as $pos => $value) {
            $insert = [];
            foreach ($this->columns as  $field) {
                if (!array_key_exists($field, $value)) {
                    $insert[] = 'NULL'; //'DEFAULT';
                } else if (is_null($value[$field])) {
                    $insert[] = 'NULL';
                } else {
                    $insert[] = ":${field}_${pos}";
                }
            }
            $inserts[] = '(' . implode(', ', $insert) . ')';
        }
        return implode(', ', $inserts);
    }
}