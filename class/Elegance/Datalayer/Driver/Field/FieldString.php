<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena uma variavel em forma de string */
class FieldString extends Field
{
    protected $SIZE = 0;

    protected function getValue()
    {
        return $this->SIZE ? substr($this->VALUE ?? '', 0, $this->SIZE) : strval($this->VALUE);
    }

    /** Define o tamanho maximo do campo */
    function size($size)
    {
        $this->SIZE = $size;
        return $this;
    }
}
