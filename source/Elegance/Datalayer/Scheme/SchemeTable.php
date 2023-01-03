<?php

namespace Elegance\Datalayer\Scheme;

class SchemeTable
{

    protected $name;
    protected $map;

    /** @var SchemeField[] */
    protected $fields = [];

    protected $isDroped = false;

    protected $metaFields = false;
    protected $smartControl = false;

    function __construct(string $name, array $map = [], ?array $realMap = null)
    {
        $realMap = $realMap ?? SchemeMap::BASE_TABLE_MAP;

        $this->metaFields = isset($realMap['fields']['_meta']);
        $this->smartControl = isset($realMap['fields']['_create']);

        $map['comment'] = $map['comment'] ?? $realMap['comment'];
        $map['fields'] = $map['fields'] ?? $realMap['fields'];

        $this->name = $name;
        $this->map = $map;
    }

    /** Marca/Desmarca o campo para a remoção */
    function drop(bool $drop = true): self
    {
        $this->isDroped = boolval($drop);
        return $this;
    }

    #==| Alterações |==#

    /** Define o comentário do campo */
    function comment(string $comment): self
    {
        $this->map['comment'] = $comment;
        return $this;
    }

    /** Defini/Altera varios campos da tabela */
    function fields(): self
    {
        foreach (func_get_args() as $field) {
            if (is_array($field)) {
                $this->fields(...array_values($field));
            } else if (is_class($field, SchemeField::class)) {
                $this->fields[$field->getName()] = $field;
            } else {
                $this->field($field);
            }
        }
        return $this;
    }

    /** Define se a tabela utiliza metaFields */
    function metaFields(string $metaFields): self
    {
        $this->metaFields = boolval($metaFields);
        return $this;
    }

    /** Define se a tabela utiliza smartControl */
    function smartControl(string $smartControl): self
    {
        $this->smartControl = boolval($smartControl);
        return $this;
    }

    #==| Recuperar de valores |==#

    /** Retorna um objeto de campo da tabela */
    function &field(string $fieldName): SchemeField
    {
        if (!isset($this->fields[$fieldName])) {
            $this->fields[$fieldName] = new SchemeField(
                $fieldName,
                $this->map['fields'][$fieldName] ?? []
            );
        }
        return $this->fields[$fieldName];
    }

    /** Retorna o mapa de alteração da tabela */
    function getTableAlterMap(): bool|array
    {
        if ($this->isDroped) {
            return false;
        }

        $fields = [];
        foreach ($this->fields as $name => $field) {
            $field = $field->getFildMap();
            if ($field || isset($this->map['fields'][$name])) {
                $fields[$name] = $field;
            }
        }

        if ($this->metaFields) {
            $fields['_meta'] = (new SchemeField('_meta', ['type' => 'meta']))->null(true)->getFildMap();
        } else {
            $fields['_meta'] = false;
        }

        if ($this->smartControl) {
            $fields['_create'] = (new SchemeField('_create', ['type' => 'time']))->getFildMap();
            $fields['_update'] = (new SchemeField('_update', ['type' => 'time']))->getFildMap();
            $fields['_delete'] = (new SchemeField('_delete', ['type' => 'time']))->getFildMap();
        } else {
            $fields['_create'] = false;
            $fields['_update'] = false;
            $fields['_delete'] = false;
        }

        return [
            'comment' => $this->map['comment'],
            'fields' => $fields,
        ];
    }
}