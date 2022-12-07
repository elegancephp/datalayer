<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Code;
use Elegance\Datalayer\Driver\Field;
use Elegance\Datalayer\Driver\Record;

/** Armazena um ID de referencia para uma tabela */
class FieldIdx extends Field
{
    /** @var Record */
    protected $RECORD = false;
    protected $DATALAYER;
    protected $TABLE;

    /** Define a conexão referenciada pelo campo */
    function datalayer($datalayer)
    {
        $this->DATALAYER = $datalayer;
        return $this;
    }

    /** Define a tabela referenciada pelo campo */
    function table($table)
    {
        $this->TABLE = $table;
        return $this;
    }

    protected function getValue()
    {
        return is_null($this->VALUE) ? null : intval($this->VALUE);
    }

    protected function setValue($value)
    {
        $this->VALUE  = is_null($value) ? null : intval($value);
        $this->RECORD = false;
    }

    #==| Metodos do IDX |==#

    /** Verifica se o objeto representado pelo IDX foi carregado */
    function _checkLoad()
    {
        return boolval($this->RECORD);
    }

    /** Retorna o registro referenciado pelo objeto */
    function _record(): Record
    {
        if (!$this->_checkLoad()) {
            $datalayer = ucfirst($this->DATALAYER);
            $datalayer = "\\Model\\Db$datalayer\\Db$datalayer";
            if ($this->_checkSave()) {
                $this->RECORD = $datalayer::{$this->TABLE}($this->get());
            } else {
                $this->RECORD = $datalayer::{$this->TABLE}(null);
            }
        }
        return $this->RECORD;
    }

    #==| Metodos com tratamento extra |==#

    /** Salva as alterações do registro no banco de dados */
    function _save()
    {
        $this->_record()->_save();
        $this->VALUE = $this->_record()->id;
    }

    #==| Metodos respondidos automáticamente |==#

    /** Retorna a chave de identificação numerica do registro */
    function id()
    {
        return $this->_checkLoad() ? $this->RECORD->id() : $this->getValue();
    }

    /** Retorna a chave de identificação codificada do registro  */
    function idKey()
    {
        if (!$this->_checkInDb()) {
            return null;
        }
        if ($this->_checkLoad()) {
            return $this->_record()->idKey();
        }
        return Code::on(Code::on($this->TABLE) . $this->VALUE);
    }

    /** Verifica se o registro pode ser salvo no banco de dados */
    function _checkSave()
    {
        return $this->_checkLoad() ? $this->_record()->_checkSave() : !is_null($this->get());
    }

    /** Verifica se o registro existe no banco de dados */
    function _checkInDb()
    {
        return $this->_checkSave() ? $this->_record()->_checkInDb() : false;
    }

    #==| Metodos de encaminhamento para RECORD |==#

    function __get($name)
    {
        return $this->_record()->$name;
    }

    function __call($name, $arguments)
    {
        return $this->_record()->$name(...$arguments);
    }
}
