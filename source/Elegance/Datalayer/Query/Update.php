<?php

namespace Elegance\Datalayer\Query;

class Update extends BaseQuery
{
    protected array $values = [];
    protected $where = [];

    /** Array de Query para execução */
    function query(): array
    {
        $this->check(['table', 'where', 'values']);

        $query = 'UPDATE [#table] SET [#values] [#where];';

        $query = prepare($query, [
            'table'   => $this->mountTable(),
            'values' => $this->mountValues(),
            'where'   => $this->mountWhere(),
        ]);

        $values = [];
        $count  = 0;

        foreach ($this->where as $where) {
            if (count($where) > 1 && !is_null($where[1])) {
                array_shift($where);
                foreach ($where as $v) {
                    $values['where_' . ($count++)] = $v;
                }
            }
        }

        foreach ($this->values as $name => $value) {
            if (!is_numeric($name) && !is_null($value)) {
                $values["value_$name"] = $value;
            }
        }

        return [$query, $values];
    }

    /** Executa a query */
    function run(?string $datalayer = null): bool
    {
        return parent::run($datalayer);
    }

    /** Define os campos que devem ser alterados com base em um array */
    function values(array $array): self
    {
        foreach ($array as $field => $value) {
            $this->values[$field] = $value;
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
        $this->where($status ? "$campo is null" : "$campo is not null");
        return $this;
    }

    protected function mountValues(): string
    {
        $change     = [];
        foreach ($this->values as $name => $value) {
            if (is_numeric($name)) {
                $value = substr_count($value, '(') ? $value : "`$value`";
                $change[] = "$value = NULL"; //"$value = DEFAULT";
            } else if (is_null($value)) {
                $name = substr_count($name, '(') ? $name : "`$name`";
                $change[] =  "$name = NULL";
            } else {
                $fname = substr_count($name, '(') ? $name : "`$name`";
                $change[] = "$fname = :value_$name";
            }
        }
        return implode(', ', $change);
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