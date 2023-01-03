<?php

namespace Elegance\Datalayer;

use Elegance\Datalayer;
use Elegance\Datalayer\Query\BaseQuery;
use Error;

abstract class Connection
{
    protected string $datalayer;

    protected ?array $config = null;

    final function __construct(string $datalayer, protected array $data = [])
    {
        $this->datalayer = Datalayer::name($datalayer);

        $this->load();

        foreach ($this->data as $var => $value)
            if (is_null($value))
                throw new Error("parameter [$var] required in " . $this->data['type'] . " datalayer");
    }


    /** Inicializa a conexão */
    abstract protected function load();


    /** Retorna uma ou todas as informações utilizadas para a conexão do datalayer */
    function data(): mixed
    {
        if (func_num_args())
            return $this->data[func_get_arg(0)] ?? null;
        return $this->data;
    }


    /** Executa uma query */
    abstract function executeQuery(mixed $queryString, array $queryData = []): mixed;

    /** Executa uma lista de querys */
    abstract function executeQueryList(array $queryList = []): array;

    /** Retorna o array da query para execução */
    protected function getQueryArray(mixed $queryArgs): array
    {
        if (!is_array($queryArgs))
            $queryArgs = [$queryArgs, []];

        if (is_class($queryArgs[0], BaseQuery::class))
            $queryArgs = $queryArgs[0]->query();

        $queryArgs[1] = $queryArgs[1] ?? [];

        return [...$queryArgs];
    }


    /** Executa uma lista de querys de esquema */
    abstract function executeSchemeQuery(array $schemeQueryList): void;


    /** Define/Retorna uma configuraçao do banco de dados */
    function config(?string $name = null): string|array
    {
        if (is_null($this->config))
            $this->loadConfig();

        if (!is_null($name)) {
            if (func_num_args() > 1)
                $this->setConfig(...func_get_args());
            return $this->getConfig($name);
        } else {
            return $this->config;
        }
    }

    /** Retorna uma configuração do banco de dados */
    abstract protected function getConfig(string $name): string;

    /** Define uma configuração do banco de dados */
    abstract protected function setConfig(string $name, string $value): void;

    /** Carrega as configurações do banco de dados para o cache */
    abstract protected function loadConfig(): void;



    /** Retorna o mapa do banco de dados */
    function map(?bool $realMap = null): array
    {
        if (is_null($realMap)) {
            $realMap = $this->loadRealMap();
            $registredMap = $this->loadRegistredMap();

            $map = [];

            foreach ($realMap as $tableName => $dataTable) {
                $jsonTable = $registredMap[$tableName] ?? ['comment' => '', 'filelds' => []];
                $map[$tableName] = [
                    'comment' => $dataTable['comment'] ?? $jsonTable['comment'] ?? '',
                    'fields' => []
                ];
                foreach ($dataTable['fields'] as $fieldName => $dataField) {
                    $jsonField = $jsonTable['fields'][$fieldName] ?? [];

                    $map[$tableName]['fields'][$fieldName] = [
                        'type' => $jsonField['type'] ?? $this->mapFieldPrefix($fieldName, $dataField['type']),
                        'comment' => $dataField['comment'] ?? $jsonField['comment'] ?? '',
                        'default' => $jsonField['default'] ?? $dataField['default'] ?? null,
                        'size' => $dataField['size'] ?? $jsonField['size'] ?? null,
                        'null' => $dataField['null'] ?? $jsonField['null'] ?? null,
                        'config' => $jsonField['config'] ?? [],
                    ];
                }
            }
        } else {
            $map = $realMap ? $this->loadRealMap() : $this->loadRegistredMap();
        }

        foreach (array_keys($map) as $table)
            if (str_starts_with($table, '_'))
                unset($map[$table]);

        return $map;
    }

    /** Retorna o mapa real do banco de dados */
    abstract protected function loadRealMap(): array;

    /** Retorna o mapa registrado no banco de dados */
    protected function loadRegistredMap(): array
    {
        $map = $this->config('Elegance_map');
        $map = is_json($map) ? json_decode($map, true) : [];
        return $map;
    }

    /** Retorna o prefixo de um campo do mapa */
    protected function mapFieldPrefix(string $name, string $dbType): string
    {
        $prefix = strtolower(explode('_', $name)[0]);
        return match ($prefix) {
            'id', 'idx', 'ids', 'tag', 'log', 'list', 'code', 'email' => $prefix,
            'tm', 'dt', 'time', 'date' => 'time',
            'sts', 'status' => 'status',
            'if', 'is' => 'boolean',
            'cnf', 'config' => 'config',
            'md5', 'key' => 'md5',
            '' => $name,
            default => match ($dbType) {
                'int', 'text', 'float' => $dbType,
                'varchar' => 'string'
            }
        };
    }
}