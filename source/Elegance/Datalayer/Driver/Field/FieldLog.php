<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena linhas de Log em forma de JSON */
class FieldLog extends Field
{
    protected $VALUE = [];

    protected function getValue()
    {
        return json_encode($this->VALUE);
    }

    protected function setValue($value)
    {
        $value = $value ?? [];
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        $this->VALUE = $value;
    }

    /** Adiciona uma linha ao log */
    function add($texto, $prepare = [])
    {
        $this->VALUE[] = [microtime(true), prepare($texto, $prepare)];
        return $this;
    }

    /** Retorna os valores do objeto em forma de array */
    function getArray()
    {
        return $this->VALUE;
    }
}
