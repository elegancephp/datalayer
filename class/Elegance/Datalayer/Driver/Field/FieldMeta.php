<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena metacampos em formato JSON */
class FieldMeta extends Field
{
    /** @var Field[] */
    protected $VALUE = [];

    protected function getValue()
    {
        return $this->getArray();
    }

    protected function setValue($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        $value  = $value ?? [];
        foreach ($value as $n => $v) {
            if (!is_null($v)) {
                $this->$n->set($v);
            }
        }
    }

    /** Retorna os valores do objeto em forma de array */
    function getArray()
    {
        $return = [];
        foreach ($this->VALUE as $name => $object) {
            $value = $object->get();
            if (!is_null($value)) {
                $return[$name] = $value;
            }
        }
        return $return;
    }

    /** Verifica se um objeto exisite no meta */
    function check($name)
    {
        return isset($this->VALUE[$name]);
    }

    #==| Metodos Mágicos |==#

    function __get($name)
    {
        $this->VALUE[$name] = $this->VALUE[$name] ?? new FieldString();
        return $this->VALUE[$name];
    }

    function __set($name, $value)
    {
        $this->VALUE[$name] = $value;
    }
}
