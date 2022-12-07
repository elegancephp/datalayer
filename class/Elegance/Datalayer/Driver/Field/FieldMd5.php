<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena um hash MD5 */
class FieldMd5 extends Field
{
    protected function getValue()
    {
        return is_null($this->VALUE) ? null : md5($this->VALUE);
    }

    /** Verifica se uma variavel corrresponde ao valor do objeto */
    function check($value)
    {
        if (is_null($this->get())) {
            return is_null($value);
        } else {
            $value = is_md5($value) ? $value : md5($value);
            return boolval($this->get() == $value);
        }
    }
}
