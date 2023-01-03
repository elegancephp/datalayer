<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena string de email */
class FieldEmail extends Field
{
    protected function getValue()
    {
        $value = $this->VALUE;
        if (!is_null($value)) {
            $value = trim($value);
            $value = remove_accents($value);
            $value = strtolower($value);
            $value = filter_var($value, FILTER_SANITIZE_EMAIL);
        }
        return $value;
    }
}
