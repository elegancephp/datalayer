<?php

namespace Elegance\Datalayer;

use Elegance\Datalayer;
use Elegance\Datalayer\Scheme\SchemeField;
use Elegance\Datalayer\Scheme\SchemeTable;

abstract class Migration
{
    protected Scheme $scheme;
    protected string $datalayer;
    protected array $queryList = [];

    final function __construct(string $datalayer, bool $mode)
    {
        $this->datalayer = Datalayer::name($datalayer);

        $this->scheme = new Scheme($datalayer);

        $mode ? $this->up() : $this->down();

        $this->scheme->apply();

        Datalayer::get($datalayer)
            ->executeQueryList($this->queryList);
    }

    abstract function up();

    abstract function down();

    /** Adiciona uma query a lista de execução pos scheme */
    protected function query($query)
    {
        $this->queryList[] = $query;
    }

    /** Retorna o objeto de uma tabela */
    function &table(string $table, ?string $comment = null): SchemeTable
    {
        return $this->scheme->table($table, $comment);
    }

    /** Retorna um objeto campo */
    private function field(string $type, string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => strtolower($type), 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Int */
    function fieldInt(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('int', $name, $comment);
    }

    /** Retorna um objeto campo do tipo String */
    function fieldString(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('string', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Text */
    function fieldText(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('text', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Float */
    function fieldFloat(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('float', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Idx */
    function fieldIDX(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, [
            'type' => 'idx',
            'comment' => $comment,
            'config' => [
                'datalayer' => $this->datalayer,
                'table' => substr(strtolower($name), 0, 4) == 'idx_' ? substr($name, 4) : $name
            ]
        ]);
    }

    /** Retorna um objeto campo do tipo IDs */
    function fieldIDS(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, [
            'type' => 'ids',
            'comment' => $comment,
            'config' => [
                'datalayer' => $this->datalayer,
                'table' => substr(strtolower($name), 0, 4) == 'ids_' ? substr($name, 4) : $name
            ]
        ]);
    }

    /** Retorna um objeto campo do tipo Boolean */
    function fieldBoolean(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('boolean', $name, $comment);
    }

    /** Retorna um objeto campo do tipo MD5 */
    function fieldMD5(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('md5', $name, $comment);
    }

    /** Retorna um objeto campo do tipo CODE */
    function fieldCode(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('code', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Email */
    function fieldEmail(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('email', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Log */
    function fieldLog(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('log', $name, $comment);
    }

    /** Retorna um objeto campo do tipo List */
    function fieldList(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('list', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Tag */
    function fieldTag(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('tag', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Config */
    function fieldConfig(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('config', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Status */
    function fieldStatus(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('status', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Time */
    function fieldTime(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('time', $name, $comment);
    }
}