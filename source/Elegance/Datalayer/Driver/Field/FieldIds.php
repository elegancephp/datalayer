<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;
use Elegance\Datalayer\Query;
use Elegance\Datalayer\Query\Select;

/** Armazena IDs de referencia para uma tabela */
class FieldIds extends Field
{
    protected $VALUE = [];
    protected $DATALAYER;
    protected $TABLE;

    protected function getValue()
    {
        $value = implode(',', $this->VALUE);
        return empty($value) ? ',' : ",$value,";
    }

    protected function setValue($value)
    {
        $this->VALUE = [];
        $this->add($value);
    }

    /** Define a datalayer que contem o campo referenciado */
    function datalayer($dbName)
    {
        $this->DATALAYER = $dbName;
        return $this;
    }

    /** Define a tabela que contem o campo referenciado */
    function table($table)
    {
        $this->TABLE = $table;
        return $this;
    }

    /** Adiciona itens ao objeto */
    function add($value)
    {
        $value = $this->formatValueArray($value);
        foreach ($value as $item) {
            if ($item) {
                $this->VALUE[md5($item)] = $item;
            }
        }
        return $this;
    }

    /** Remove um item do objeto */
    function remove($value)
    {
        $value = $this->formatValueArray($value);
        foreach ($value as $item) {
            if (isset($this->VALUE[md5($item)])) {
                unset($this->VALUE[md5($item)]);
            }
        }
        return $this;
    }

    /** Verifica se um item existe */
    function check($value)
    {
        $value = intval($value);
        return $value > 0 && isset($this->VALUE[md5($value)]);
    }

    /** Retorna os valores do objeto em forma de array */
    function getArray()
    {
        return $this->formatValueArray($this->get());
    }

    /** Retorna um SELECT buscando todos os registros representados no objeto */
    function getQuerySelect(): Select
    {
        return Query::select($this->TABLE)
            ->datalayer($this->DATALAYER)
            ->where("? like concat('%,',id,',%')", $this->get());
    }

    #==| Funcionamento |==#

    /** Formata um array de valores */
    protected function formatValueArray($value)
    {
        $return = [];
        $value = is_string($value) ? explode(',', $value) : $value;
        $value = is_array($value) ? $value : [$value];
        foreach ($value ?? [] as $item) {
            if ($item) {
                $item = intval($item);
                if ($value > 0) {
                    $return[] = $item;
                }
            }
        }
        return $return;
    }
}