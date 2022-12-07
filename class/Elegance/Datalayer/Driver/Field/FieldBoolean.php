<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena dados Booleanos 1 ou 0 */
class FieldBoolean extends Field
{
    protected function getValue()
    {
        return boolval($this->VALUE) ? 1 : 0;
    }
}