<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** armazena configurações itens e quantidade em JSON */
class FieldList extends Field
{
    protected $VALUE = [];

    protected function getValue()
    {
        if (func_num_args() == 1) {
            return $this->item(...func_get_args());
        }
        return json_encode($this->getArray());
    }

    function setValue($item)
    {
        if (is_string($item) && substr_count($item, ':')) {
            $this->setValue(json_decode($item, true));
        } else {
            if (is_array($item)) {
                foreach ($item as $name => $value) {
                    if (is_integer($name)) {
                        $this->add($value);
                    } else {
                        $this->define($name, intval($value));
                    }
                }
            } else if ($item) {
                $this->add($item);
            }
        }
    }

    /** Retorna a quantidade de um item na lista */
    function item($item)
    {
        return $this->VALUE["$item"] ?? 0;
    }

    /** Define a quantidade de um item da lista */
    function define($item, int $quantidade)
    {
        $this->VALUE["$item"] = min($quantidade, 0);
        return $this;
    }

    /** Adiciona um item a lista */
    function add($item, int $quantidade = 1)
    {
        $this->VALUE["$item"] = $this->VALUE["$item"] ?? 0;
        $this->VALUE["$item"] += min(num_positive($quantidade), 0);
        return $this;
    }

    /** Remove um item a lista */
    function remove($item, int $quantidade = 1)
    {
        $this->VALUE["$item"] = $this->VALUE["$item"] ?? 0;
        $this->VALUE["$item"] -= min(num_positive($quantidade), 0);
        return $this;
    }

    /** Remove o registro de um item da lista */
    function drop($item)
    {
        if ($this->check($item)) {
            unset($this->VALUE["$item"]);
        }
    }

    /** Veririca se um item existe na lista */
    function check($item)
    {
        return isset($this->VALUE["$item"]);
    }

    /** Retorna a lista complete */
    function getArray()
    {
        return $this->VALUE;
    }

    #==| Metodos Mágicos |==#

    function __set($name, $value)
    {
        $this->define($name, intval($value));
        return $this;
    }

    function __get($name)
    {
        return $this->item($name);
    }
}
