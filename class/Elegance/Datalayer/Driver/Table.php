<?php

namespace Elegance\Datalayer\Driver;

use Elegance\Code;
use Elegance\Datalayer\Query;
use Error;
use Elegance\Datalayer\Query\Select;

abstract class Table
{
    protected $datalayer;
    protected $table;

    protected $metaField;
    protected $smartControl;

    protected $recordClass;

    protected $active;

    protected $cache = [];
    protected $cacheResult = [];

    /** Conta os resultados de uma pesquisa */
    function count(): int
    {
        $query = $this->getQuery(...func_get_args())->where($this->whereSmartcontrol());
        return count($query->fields(null, 'id')->run());
    }

    /** Define um registro como registro ativo */
    function active()
    {
        if (func_num_args()) {
            if (is_class(func_get_arg(0), $this->recordClass)) {
                $this->active = func_get_arg(0);
            } else {
                $this->active = $this->getAuto(...func_get_args());
            }
        }
        $this->active = $this->active ?? $this->getNull();
        return $this->active;
    }

    /** Busca um registro baseando-se os parametros fornecidos */
    function getAuto()
    {
        $parameter = func_get_args()[0] ?? null;

        if ($parameter === true) {
            return $this->active();
        }

        if (!count(func_get_args()) || $parameter === 0) {
            return $this->getNew();
        }

        if (is_null($parameter) || $parameter === false) {
            return $this->getNull();
        }

        return $this->getOne(...func_get_args());
    }

    /** Retorna um registro NOVO (ainda não existente no banco de dados) */
    function getNew()
    {
        return $this->object(['id' => 0]);
    }

    /** Retorna um registro NULO (não poderá ser salvo no banco de dados) */
    function getNull()
    {
        return $this->object(['id' => null]);
    }

    /** Busca um registro */
    function getOne()
    {
        if (func_num_args() && !func_get_args()[0]) {
            return $this->getNull();
        }

        $result = null;
        $queryType = $this->getQueryType(...func_get_args());
        if ($queryType == 2) {
            $result = $this->getCacheResult($this->idKey(...func_get_args()));
        } elseif ($queryType == 3) {
            $result = $this->getCacheResult(...func_get_args());
        }

        $result = $result ? [$result] : $result;
        $result = $result ?? $this->getQuery(...func_get_args())->limit(1)->where($this->whereSmartcontrol())->run();

        return empty($result) ? $this->getNull() : $this->object(array_shift($result));
    }

    /** Busca um registro baseado na IDKey */
    function getKey()
    {
        return $this->getQueryType(...func_get_args()) == 3 ? $this->getOne(...func_get_args()) : $this->getNull();
    }

    /** Busca varios registros */
    function get()
    {
        $result = $this->getQuery(...func_get_args())->where($this->whereSmartcontrol())->run();
        return $this->convert($result);
    }

    #==| Conversão de resultados para objetos |==#

    /** Converte um array de resultados em um array de objetos */
    function convert($result)
    {
        $return = [];
        while (count($result)) {
            $return[] = $this->object(array_shift($result));
        }
        return $return;
    }

    /** Retorna um o RECORD que represeta um resultado */
    protected function object($result)
    {
        $class = $this->recordClass;

        if (!$result['id'] ?? false) {
            return new $class($result);
        }

        $idkey = $result['id'];
        $idkey = $this->idKey($idkey);

        if (!isset($this->cacheResult[$idkey])) {
            $this->cacheResult[$idkey] = $result;
        }

        if (!isset($this->cache[$idkey])) {
            $this->cache[$idkey] = new $class($this->cacheResult[$idkey]);
        }

        return $this->cache[$idkey];
    }

    #==| Metodos da IDKEY |==#

    /** Retorna o idKey de um ID da tabela */
    function idKey($id)
    {
        return is_null($id) ? null : Code::on(Code::on($this->table) . $id);
    }

    /** Retroan a string de um Where com a idKey */
    function whereIdKey($check = null)
    {
        $check = Code::off($check);
        $key   = Code::on($this->table);
        return is_md5($check) ? "md5(concat('$key',id)) = '$check'" : "md5(concat('$key',id))";
    }

    #==| Criação de query |==#

    /** Retorna a where referente ao controle inteligente */
    protected function whereSmartcontrol()
    {
        return $this->smartControl ? '_delete = 0' : null;
    }

    /** @return Select */
    protected function getQuery()
    {
        $parameter = func_get_args();
        switch ($this->getQueryType(...$parameter)) {
            case 1; //Query Limpa
                $query = Query::select();
                break;
            case 2; //Busca por ID
                $query = Query::select();
                $query->where('id', $parameter[0]);
                break;
            case 3; //Busca por IDKey
                $query = Query::select();
                $query->where($this->whereIdKey($parameter[0]));
                break;
            case 4; //Busca por where informado
                $query = Query::select();
                $query->where($parameter[0], $parameter[1] ?? null);
                break;
            case 5; //Busca por where dinamico informado via array
                $query = Query::select();
                foreach ($parameter[0] as $parameter1 => $parameter2) {
                    $query->where(is_int($parameter1) ? 'id' : $parameter1, $parameter2);
                }
                break;
            case 6; //Busca utilizando select personalizado
                $query = $parameter[0];
                $query->fields(null)->table(null);
                break;
            default; //Impossivel definir
                throw new Error('Impossivel criar query com parametros fornecidos');
                break;
        }
        return $query->datalayer($this->datalayer)->table($this->table);
    }

    /** Define o tipo de query que deve ser montada com os parametros fornecidos */
    protected function getQueryType()
    {
        $parameter = func_get_args()[0] ?? null;

        if (is_null($parameter))
            return 1; //Query Limpa

        if (is_numeric($parameter) && intval($parameter) == $parameter)
            return 2; //Busca por ID

        if (is_string($parameter) && (is_md5($parameter) || Code::check($parameter)))
            return 3; //Busca por IDKey

        if (is_string($parameter))
            return 4; //Busca por where informado

        if (is_array($parameter))
            return 5; //Busca por where dinamico informado via array

        if (is_class($parameter, Select::class))
            return 6; //Busca utilizando select personalizado

        return 0; //Impossivel definir
    }

    #==| Controle de cache |==#

    /** Adiciona um result ao cache de consultas */
    protected function setCacheResult($idkey, $values)
    {
        $this->cacheResult[$idkey] = $values;
    }

    /** Retorna um result do cache de consultas */
    protected function getCacheResult($idkey)
    {
        return $this->cacheResult[$idkey] ?? null;
    }
}