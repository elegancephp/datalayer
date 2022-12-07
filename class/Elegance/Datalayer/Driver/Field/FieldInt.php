<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena numeros inteiros */
class FieldInt extends Field
{
    protected $MIN, $MAX;
    protected $ROUND = 0;

    protected function getValue()
    {
        $value = $this->VALUE;
        $min = $this->MIN ?? $value;
        $max = $this->MAX ?? $value;
        $value = num_interval($value, $min, $max);
        return (int) num_format($value, 0, $this->ROUND);
    }

    protected function setValue($value)
    {
        $this->VALUE = intval($value);
    }

    /** Determina como os numeros devem ser arredondados */
    function round($round)
    {
        $this->ROUND = num_interval(intval($round), -1, 1);
        return $this;
    }

    /** Define o valor maximo do objeto */
    function max($min)
    {
        $this->MAX = $min;
        return $this;
    }

    /** Define o valor minimo do objeto */
    function min($max)
    {
        $this->MIN = $max;
        return $this;
    }

    /** Soma um numero ao valor do objeto */
    function sum($value)
    {
        $this->set($this->get() + $value);
        return $this;
    }
}
