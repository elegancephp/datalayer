<?php

namespace Elegance\Datalayer\Query;

class Select extends BaseQuery
{
    protected array $fields = [];
    protected int $limit = 0;
    protected array $order = [];
    protected array $where = [];

    /** Array de Query para execução */
    function query(): array
    {
        $this->check(['table']);

        $query = 'SELECT [#fields] FROM [#table] [#where][#order][#limit];';

        $query = prepare($query, [
            'fields' => $this->mountFields(),
            'table'  => $this->mountTable(),
            'where'  => $this->mountWhere(),
            'limit'  => $this->mountLimit(),
            'order'  => $this->mountOrder(),
        ]);

        $values = [];

        foreach ($this->where as $where) {
            if (count($where) > 1 && !is_null($where[1])) {
                array_shift($where);
                foreach ($where as $v) {
                    $values['where_' . count($values)] = $v;
                }
            }
        }

        return [$query, $values];
    }

    /** Executa a query */
    function run(?string $datalayer = null): bool|array
    {
        return parent::run($datalayer);
    }

    /** Define os campos que devem ser retornados no select, NULL ou * retorna todos os campos */
    function fields(null|string|array $fields): self
    {
        if (is_null($fields) || $fields == '*') {
            $this->fields = [];
        } else if (func_num_args() > 1) {
            foreach (func_get_args() as $field) {
                $this->fields($field);
            }
        } else {
            $fields = is_array($fields) ? $fields : [$fields];
            foreach ($fields as $name => $value) {
                if (is_numeric($name)) {
                    $this->fields[$value] = null;
                } else {
                    $this->fields[$name] = $value;
                }
            }
        }
        return $this;
    }

    /** Define a quantidade maxima de valores removidos */
    function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /** Define a ordem da query */
    function order(string|array $fields, bool $asc = true): self
    {
        $fields = is_array($fields) ? $fields : [$fields];
        foreach ($fields as $field) {
            $this->order[] = $asc ? "$field ASC" : "$field DESC";
        }
        return $this;
    }

    /** Adiciona um WHERE ao select */
    function where(): self
    {
        if (func_num_args()) {
            $this->where[] = func_get_args();
        }
        return $this;
    }

    /** Adiciona um WHERE para ser utilizado na query verificando se um campo é nulo */
    function whereNull(string $campo, bool $status = true): self
    {
        $campo = substr_count($campo, '(') ? $campo : "`$campo`";
        $this->where($status ? "$campo is null" : "$campo is not null");
        return $this;
    }

    protected function mountFields(): string
    {
        $fields = [];
        foreach ($this->fields as $name => $alias) {
            if (!is_numeric($name)) {
                $name = substr_count($name, '(') ? $name : "`$name`";
                $fields[] = $alias ? "$name as $alias" : $name;
            }
        }
        return empty($fields) ? '*' : implode(', ', $fields);
    }

    protected function mountLimit(): string
    {
        return $this->limit ? " LIMIT $this->limit" : '';
    }

    protected function mountOrder(): string
    {
        return empty($this->order) ? '' : ' ORDER BY ' . implode(', ', $this->order);
    }

    protected function mountWhere(): string
    {
        $return     = [];
        $parametros = 0;
        foreach ($this->where as $where) {
            if (count($where) == 1 || is_null($where[1])) {
                $return[] = $where[0];
            } else {
                $igualdade = array_shift($where);
                if (!substr_count($igualdade, ' ') && !substr_count($igualdade, '?')) {
                    $igualdade = "$igualdade = ?";
                }

                foreach ($where as $v) {
                    $igualdade = str_replace(["'?'", '"?"'], '?', $igualdade);
                    $igualdade = preg_replace("/\?/", ":where_" . ($parametros++), $igualdade, 1);
                }
                $return[] = $igualdade;
            }
        }

        $return = array_filter($return);

        return empty($return) ? '' : 'WHERE (' . implode(') AND (', $return) . ')';
    }
}