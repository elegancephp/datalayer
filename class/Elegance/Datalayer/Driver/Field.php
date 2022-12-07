<?php

namespace Elegance\Datalayer\Driver;

abstract class Field
{
    protected $VALUE;

    final function __construct($value = null)
    {
        $this->set($value);
    }

    /** Retorna o valor do objeto */
    final function get()
    {
        return $this->getValue(...func_get_args());
    }

    /** Define um valor para o objeto */
    final function set($value)
    {
        $this->setValue(...func_get_args());
        return $this;
    }

    protected function getValue()
    {
        return $this->VALUE;
    }

    protected function setValue($value)
    {
        $this->VALUE = $value;
    }
}