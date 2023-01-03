<?php

namespace Elegance\Datalayer\Scheme;

class SchemeField
{
    protected $name;
    protected $map;

    protected $isDroped = false;

    function __construct(string $name, array $map = [], ?array $realMap = null)
    {
        $realMap = $realMap ?? SchemeMap::BASE_FIELD_MAP;

        $map['type'] = $map['type'] ?? $realMap['type'];
        $map['comment'] = $map['comment'] ?? $realMap['comment'];
        $map['default'] = $map['default'] ?? $realMap['default'];
        $map['size'] = $map['size'] ?? $realMap['size'];
        $map['null'] = $map['null'] ?? $realMap['null'];
        $map['config'] = $map['config'] ?? $realMap['config'];

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

    /** Define o valor padrão do campo */
    function default(mixed $default): self
    {
        $this->map['default'] = $default;
        return $this;
    }

    /** Define o tamanho maximo */
    function size(int $size): self
    {
        $this->map['size'] = max(0, intval($size));
        return $this;
    }

    /** Define se o campo aceita valores nulos */
    function null(bool $null): self
    {
        $this->map['null'] = boolval($null);
        return $this;
    }

    /** Define as configurações extras do campo */
    function config(string $config, mixed $value = null): self
    {
        if (is_null($config)) {
            $this->map['config'] = [];
        } else if (is_null($value)) {
            if (isset($this->map['config'][$config])) {
                unset($this->map['config'][$config]);
            }
        } else {
            $this->map['config'][$config] = $value;
        }
        return $this;
    }

    #==| Recuperar de valores |==#

    /** Retorna o nome do campo */
    function getName(): string
    {
        return $this->name;
    }

    /** Retorna o mapa do campo */
    function getFildMap(): bool|array
    {
        if ($this->isDroped) {
            return false;
        }

        $map = $this->map;

        switch ($map['type']) {
            case 'id':
            case 'idx':
                $map['size'] = 10;
                break;

            case 'int':
            case 'float':
                $map['size'] = $map['size'] ?? 10;
                break;

            case 'tinyint':
                $map['size'] = $map['size'] ?? 3;
                break;

            case 'boolean':
                $map['size'] = 1;
                $map['default'] = boolval($map['default']) ? 1 : 0;
                break;

            case 'status':
                $map['size'] = 1;
                $map['default'] = num_interval(intval($map['default'] ?? 0), -9, 9);
                break;

            case 'email':
                $map['size'] = 200;
                break;

            case 'md5':
                $map['size'] = 32;
                $map['default'] = null;
                break;

            case 'code':
                $map['size'] = 34;
                $map['default'] = null;
                break;

            case 'text':
                $map['size'] = null;
                break;

            case 'ids':
            case 'tag':
            case 'log':
            case 'meta':
            case 'list':
            case 'json':
            case 'config':
                $map['size'] = null;
                $map['null'] = false;
                break;

            case 'time':
                $map['size'] = 11;
                $map['null'] = false;
                $map['default'] = 0;
                break;

            case 'string':
            default:
                $map['type'] = 'string';
                $map['size'] = $map['size'] ?? 50;
                break;
        }
        return $map;
    }
}