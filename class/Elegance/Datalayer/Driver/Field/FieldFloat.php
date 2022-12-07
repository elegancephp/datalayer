<?php

namespace Elegance\Datalayer\Driver\Field;

/** Armazena numeros com casas decimais */
class FieldFloat extends FieldInt
{
    protected $DECIMAL = 2;

    protected function getValue()
    {
        $value = $this->VALUE;
        $min = $this->MIN ?? $value;
        $max = $this->MAX ?? $value;
        $value = num_interval($value, $min, $max);
        return num_format($value, $this->DECIMAL, $this->ROUND);
    }

    protected function setValue($value)
    {
        $this->VALUE = floatval($value);
    }

    /** Define o numero de casas decimais do valor do objeto */
    function decimal($decimal)
    {
        $this->DECIMAL = $decimal;
        return $this;
    }
}
