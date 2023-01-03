<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena um array de elementos uncios */
class FieldTag extends Field
{
    protected $VALUE = [];

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
        $value = "$value";
        return !empty($value) && isset($this->VALUE[md5($value)]);
    }

    /** Retorna os valores do objeto em forma de array */
    function getArray()
    {
        return $this->formatValueArray($this->get());
    }

    #==| Funcionamento |==#

    /** Formata um array de valores */
    protected function formatValueArray($value)
    {
        $return = [];
        $value = is_string($value) ? explode(',', $value) : $value;
        $value = is_array($value) ? $value : [$value];
        foreach ($value as $item) {
            $item = "$item";
            if (!empty($item)) {
                $return[] = $item;
            }
        }
        return $return;
    }
}
