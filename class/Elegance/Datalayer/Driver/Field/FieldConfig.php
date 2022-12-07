<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** armazena configurações e seus valores em forma de JSON */
class FieldConfig extends Field
{
    protected $VALUE = [];

    protected function getValue()
    {
        if (func_num_args() == 1) {
            list($config) = func_get_args();
            return $this->cnf($config);
        }
        return json_encode($this->getArray());
    }

    protected function setValue($value)
    {
        if (func_num_args() == 2) {
            $this->setCnf(...func_get_args());
        } else {
            $value = is_string($value) ? json_decode($value, true) : $value;
            $this->VALUE = $value ?? [];
        }
    }

    /** Captura/Define o valor de uma configuração do objeto */
    function cnf()
    {
        if (func_num_args() == 1) {
            return $this->getCnf(...func_get_args());
        } else if (func_get_args() == 2) {
            $this->setCnf(...func_get_args());
        }
        return $this;
    }

    /** Captura o valor de uma configuração */
    function getCnf($name)
    {
        return $this->VALUE[$name] ?? null;
    }

    /** Define o valor de uma configuração */
    function setCnf($name, $value)
    {
        $this->VALUE[$name] = $value;
        return $this;
    }

    /** Retorna os valores do objeto em forma de array */
    function getArray()
    {
        $return = [];
        foreach ($this->VALUE as $p => $v) {
            if (!is_null($v)) {
                $return[$p] = $v;
            }
        }
        return $return;
    }

    /** Verifica se a configuração existe no objeto */
    function check($name)
    {
        return isset($this->VALUE[$name]);
    }

    /** Remove uma configuração do objeto */
    function remove($name)
    {
        if ($this->check($name)) {
            unset($this->VALUE[$name]);
        }
        return $this;
    }

    #==| Metodos Mágicos |==#

    function __set($name, $value)
    {
        $this->setCnf(...func_get_args());
        return $this;
    }

    function __get($name)
    {
        return $this->getCnf($name);
    }
}
