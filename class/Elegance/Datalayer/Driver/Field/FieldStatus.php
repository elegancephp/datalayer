<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena um numero inteiro entre -9 e 1 */
class FieldStatus extends Field
{
    protected $label = [];

    protected function getValue()
    {
        return num_interval($this->VALUE, -9, 9);
    }

    /** Adiciona uma Legenda para os valores de status */
    function setLabel($labelArray)
    {
        foreach ($labelArray as $value => $label) {
            $this->label[$value] = $label;
        }
        return $this;
    }

    function getLabel()
    {
        $value = $this->get();
        return $this->label[$value] ?? '';
    }
}
