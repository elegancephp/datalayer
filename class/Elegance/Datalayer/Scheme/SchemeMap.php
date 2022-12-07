<?php

namespace Elegance\Datalayer\Scheme;

use Elegance\Datalayer;

class SchemeMap
{
    final const BASE_TABLE_MAP = ['comment' => null, 'fields' => []];
    final const BASE_FIELD_MAP = ['type' => 'string', 'comment' => '', 'default' => null, 'size' => null, 'null' => true, 'config' => []];

    protected array $map;
    protected array $realMap;
    protected string $datalayer;

    function __construct(string $datalayer)
    {
        $this->datalayer = Datalayer::name($datalayer);
        $this->map = Datalayer::get($this->datalayer)->map();
        $this->realMap = Datalayer::get($this->datalayer)->map(true);
    }

    /** Retorna o mapa */
    function get(bool $realMap = false): array
    {
        return $realMap ? $this->realMap : $this->map;
    }

    /** Salva as alteraçãos do mapa */
    function save(): void
    {
        Datalayer::get($this->datalayer)->config('Elegance_map', json_encode($this->map));
        $this->realMap = $this->map;
    }

    /** Adiciona uma tabela */
    function addTable(string $tableName, ?string $comment = null): void
    {
        $mapTable = $this->getTable($tableName);

        $mapTable['comment'] = $comment ?? $mapTable['comment'];

        $this->map[$tableName] = $mapTable;
    }

    /** Adiciona uma campo em uma tabela */
    function addField(string $tableName, string $fieldName, array $fieldMap = []): void
    {
        $this->addTable($tableName);

        $currentFieldMap = $this->getField($tableName, $fieldName);

        $fieldMap['type'] = $fieldMap['type'] ?? $currentFieldMap['type'];
        $fieldMap['comment'] = $fieldMap['comment'] ?? $currentFieldMap['comment'];
        $fieldMap['default'] = $fieldMap['default'] ?? $currentFieldMap['default'];
        $fieldMap['size'] = $fieldMap['size'] ?? $currentFieldMap['size'];
        $fieldMap['null'] = $fieldMap['null'] ?? $currentFieldMap['null'];
        $fieldMap['config'] = $fieldMap['config'] ?? $currentFieldMap['config'];

        $this->map[$tableName]['fields'][$fieldName] = $fieldMap;
    }

    /** Remove uma tabela */
    function dropTable(string $tableName): void
    {
        if ($this->checkTable($tableName))
            unset($this->map[$tableName]);
    }

    /** Remove uma campo de uma tabela */
    function dropField(string $tableName, string $fieldName): void
    {
        if ($this->checkField($tableName, $fieldName))
            unset($this->map[$tableName]['fields'][$fieldName]);
    }

    /** Retorna o mapa de uma tabela */
    function getTable(string $tableName, bool $inRealMap = false): array
    {
        return $this->get($inRealMap)[$tableName] ?? self::BASE_TABLE_MAP;
    }

    /** Retorna o mapa de um campo de uma tabela */
    function getField(string $tableName, string $fieldName, bool $inRealMap = false): array
    {
        return $this->getTable($tableName, $inRealMap)['fields'][$fieldName] ?? self::BASE_FIELD_MAP;
    }

    /** Verifica se uma tabela existe */
    function checkTable(string $tableName, bool $inRealMap = false): bool
    {
        return isset($this->get($inRealMap)[$tableName]);
    }

    /** Verifica se um campo de uma tabela existe */
    function checkField(string $tableName, string $fieldName, bool $inRealMap = false): bool
    {
        return isset($this->getTable($tableName, $inRealMap)['fields'][$fieldName]);
    }
}