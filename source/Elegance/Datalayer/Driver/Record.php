<?php

namespace Elegance\Datalayer\Driver;

use Elegance\Code;
use Elegance\Datalayer\Driver\Field\FieldMeta;
use Elegance\Datalayer\Driver\Field\FieldString;
use Elegance\Datalayer\Driver\Field\FieldTime;
use Elegance\Datalayer\Query;
use Error;
use Elegance\Datalayer\Driver\Field\FieldIdx;

abstract class Record
{

    protected $DROPED;
    protected $ID, $IDKEY;
    protected $DATALAYER, $TABLE;
    protected $NAME_FIELDS = [];
    protected $FIELD = [], $ORIGINAL_VALUE = [];
    protected $ACTIONSAVE = 0;

    function __construct($scheme)
    {
        $this->__driver__();

        $scheme = $scheme ?? ['id' => null];

        if ($this->_checkSmartMeta()) {
            $this->_smartMeta();
            $this->__meta__();
        }

        foreach ($scheme as $name => $value) {
            switch ($name) {
                case 'id':
                    $this->ID = $value;
                    break;
                case '_meta':
                    $this->_smartMeta()->set($value);
                    $this->ORIGINAL_VALUE['_meta'] = $value;
                    break;
                case '_create':
                case '_update':
                case '_delete':
                    $this->_smartControl($name)->set($value);
                    $this->ORIGINAL_VALUE[$name] = $value;
                    break;
                default:
                    $this->$name->set($value);
                    $this->ORIGINAL_VALUE[$name] = $value;
                    break;
            }
        }

        $this->__customize__();
    }

    /** Define automaticamente varios valores do registro com base em um array */
    function _setArray($array)
    {
        $array = is_array($array) ? $array : [$array];
        foreach ($array as $name => $value) {
            if ($name != 'id' && $name != 'idKey' && substr($name, 0, 1) != '_') {
                if (in_array($name, $this->NAME_FIELDS)) {
                    $this->$name($value);
                }
            }
        }
        return $this;
    }

    #==| Salvamento |==#

    /** Salva as alterações do registro no banco de dados */
    function _save()
    {
        if ($this->_checkSave()) {
            #Salvamento automatico do IDX

            if ($this->DROPED) {
                #==| Remover o registro |==#
                if ($this->_checkInDB()) {
                    Query::delete($this->TABLE)
                        ->where('id', $this->ID)
                        ->run($this->DATALAYER);

                    $this->_saveAction(-1);
                }
                $this->ID = null;
            } else {
                #==| Executando autoSave IDX |==#

                foreach ($this->FIELD as $name => &$obj) {
                    if (is_class($obj, FieldIdx::class)) {
                        if ($obj->_checkLoad() && $obj->_checkSave()) {
                            if ((get_class($obj->_record()) != get_class($this)) || (!$obj->id || $obj->id != $this->id)) {
                                $obj->_save();
                            }
                        }
                    }
                }

                if (!$this->_checkInDB()) {
                    #==| Criando um novo registro |==#

                    if ($this->_checkSmartControl()) {
                        $this->_smartControl('_create')->set(time());
                    }

                    $dif = $this->_arrayInsert();

                    $this->ID = Query::insert($this->TABLE)
                        ->values($dif)
                        ->run($this->DATALAYER);

                    $this->_saveAction(0);
                } else {
                    #==| Atualizando um registro |==#

                    $dif = $this->_arrayInsert();

                    foreach ($dif as $name => $value) {
                        if (isset($this->ORIGINAL_VALUE[$name])) {
                            if ($this->ORIGINAL_VALUE[$name] == $value) {
                                unset($dif[$name]);
                            }
                        }
                    }

                    if (!empty($dif)) {
                        if ($this->_checkSmartControl()) {
                            $time = time();
                            if ($time != $this->_smartControl('_create')->get()) {
                                $this->_smartControl('_update')->set($time);
                                $dif['_update'] = $this->_smartControl('_update')->get();
                            }
                        }

                        Query::update($this->TABLE)
                            ->where('id', $this->ID)
                            ->values($dif)
                            ->run($this->DATALAYER);

                        if ($this->_checkSmartControl() && $this->_smartControl('_delete')->get()) {
                            $this->_saveAction(-1);
                        } else {
                            $this->_saveAction(1);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /** Executa uma ação do save */
    protected function _saveAction($action)
    {
        if (!$this->ACTIONSAVE) {
            $this->ACTIONSAVE++;

            match ($action) {
                -1 => $this->_onDrop(),
                0 => $this->_onCreate(),
                1 => $this->_onUpdate()
            };

            $this->ACTIONSAVE--;
        }
    }

    #==| Remoção |==#

    /** Marca ou desmarca o registro para remoção do banco */
    function _drop($drop = true)
    {
        $this->DROPED = boolval($drop);
        return $this;
    }

    #==| Chave de registro |==#

    /** Retorna o identificador numerico do registro no banco */
    function id()
    {
        return $this->ID === null ? null : intval($this->ID);
    }

    /** Retorna o identificador codificado do registro no banco */
    function idKey()
    {
        if (!$this->_checkInDB()) {
            return null;
        }
        $this->IDKEY = $this->IDKEY ?? Code::on(Code::on($this->TABLE) . $this->ID);
        return $this->IDKEY;
    }

    #==| Captura de Array |==#

    /** Retorna um array com os valores do campo em forma de array */
    function _array()
    {
        $return = [];
        if (func_get_args()[0] ?? false) {
            $return['id'] = $this->ID;
        }

        foreach ($this->FIELD as $name => $obj) {
            $return[$name] = $obj->get();
        }

        return $return;
    }

    /** Retorna um array com os valores do campo em forma de array tratando os campos de controle e _meta */
    function _arrayValues()
    {
        $return = $this->_array(...func_get_args());

        if (isset($return['_meta'])) {
            foreach ($return['_meta'] as $name => $value) {
                $return[$name] = $value;
            }
            unset($return['_meta']);
        }

        if ($this->_checkSmartControl()) {
            unset($return['_create']);
            unset($return['_update']);
            unset($return['_delete']);
        }

        return $return;
    }

    /** Retorna um array com os valores prontos para serem inseridos no banco de dados */
    function _arrayInsert()
    {
        $return = $this->_array(...func_get_args());
        foreach ($return as $name => $field) {
            if (is_array($field)) {
                $return[$name] = json_encode($field, JSON_UNESCAPED_UNICODE);
            }
            if (is_null($field)) {
                unset($return[$name]);
            }
        }
        return $return;
    }

    #==| Verificação |==#

    /** Verifica se o registro pode ser salvo no banco de dados */
    function _checkSave()
    {
        return !is_null($this->ID) && $this->ID >= 0;
    }

    /** Verifica se o registro existe no banco de dados */
    function _checkInDB()
    {
        return !is_null($this->ID) && $this->ID > 0;
    }

    #==| MetaFields |==#

    protected function _checkSmartMeta()
    {
        return in_array('_meta', $this->NAME_FIELDS);
    }

    /** @return FieldMeta */
    protected function _smartMeta()
    {
        if ($this->_checkSmartMeta()) {
            $this->FIELD['_meta'] = $this->FIELD['_meta'] ?? new FieldMeta();
            return $this->FIELD['_meta'];
        } else {
            throw new Error("O registro da tabela [$this->TABLE] não utiliza metaFields");
        }
    }

    #==| SmartControl |==#

    protected function  _checkSmartControl()
    {
        return in_array('_create', $this->NAME_FIELDS);
    }

    /** @return FieldTime */
    protected function _smartControl($nameField)
    {
        if ($this->_checkSmartControl()) {
            $this->FIELD['_create'] = $this->FIELD['_create'] ?? new FieldTime(0);
            $this->FIELD['_update'] = $this->FIELD['_update'] ?? new FieldTime(0);
            $this->FIELD['_delete'] = $this->FIELD['_delete'] ?? new FieldTime(0);
            if ($nameField == '_create' || $nameField == '_update' || $nameField == '_delete') {
                return $this->FIELD[$nameField];
            }
        } else {
            throw new Error("O registro da tabela [$this->TABLE] não utiliza smartControl");
        }
    }

    #==| Metodos magicos |==#

    function __set($name, $value)
    {
        if ($name == 'id' || $name == 'idKey' || substr($name, 0, 1) == '_') {
            return $this;
        }
        if (in_array($name, $this->NAME_FIELDS)) {
            if (!isset($this->FIELD[$name])) {
                $this->FIELD[$name] = $value;
            }
        } else {
            if ($this->_checkSmartMeta()) {
                $this->_smartMeta()->{$name} = $value;
            } else {
                throw new Error("Variavel [$name] não existe em [$this->TABLE]");
            }
        }
    }

    function __get($name)
    {
        if ($name == 'id' || $name == 'idKey') {
            return $this->{$name}();
        }

        if (substr($name, 0, 1) == '_') {
            return null;
        }

        if (in_array($name, $this->NAME_FIELDS)) {
            $this->FIELD[$name] = $this->FIELD[$name] ?? new FieldString();
            return $this->FIELD[$name];
        }

        if ($this->_checkSmartMeta()) {
            return $this->_smartMeta()->{$name};
        }

        throw new Error("Variavel [$name] não existe em [$this->TABLE]");
    }

    function __call($name, $arguments)
    {
        if (!count($arguments)) {
            return $this->$name->get();
        }
        $this->$name->set(...$arguments);
        return $this;
    }

    #==| Funcionamento IDX |==#

    /** Retorna o valor do objeto */
    final function get()
    {
        return $this->id();
    }

    /** Define um valor para o objeto */
    final function set($value)
    {
        return null;
    }

    #==| Para Implementação |==#

    abstract protected function __driver__();

    abstract protected function __customize__();

    protected function __meta__()
    {
    }

    /** Metodo executado ao inserir o objeto no banco */
    protected function _onCreate()
    {
    }

    /** Metodo executado ao atualizar o objeto no banco */
    protected function _onUpdate()
    {
    }

    /** Metodo executado ao remover o objeto do banco */
    protected function _onDrop()
    {
    }
}