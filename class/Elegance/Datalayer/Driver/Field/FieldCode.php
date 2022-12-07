<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Code;
use Elegance\Datalayer\Driver\Field;

/** Armazena dados codificados via CODE */
class FieldCode extends Field
{
    protected function getValue()
    {
        return is_null($this->VALUE) ? null : Code::on($this->VALUE);
    }

    /** Verifica se uma variavel corrresponde ao valor do objeto */
    function check($value)
    {
        if (is_null($this->get())) {
            return is_null($value);
        } else {
            return Code::compare($this->get(), $value);
        }
    }
}
