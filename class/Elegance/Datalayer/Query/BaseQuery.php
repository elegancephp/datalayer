<?php

namespace Elegance\Datalayer\Query;

use Elegance\Datalayer;
use Error;

abstract class BaseQuery
{
    protected array $data = [];
    protected ?string $datalayer = null;
    protected ?string $table = null;

    function __construct(?string $table)
    {
        $this->table($table);
    }

    /** Verifica se os dados estão completos */
    protected function check(array $dataCheck = []): void
    {
        foreach ($dataCheck as $check)
            if (empty($this->$check))
                throw new Error("Define um valor de [$check] para a query");
    }

    /** Executa a query */
    function run(?string $datalayer = null): mixed
    {
        return Datalayer::get($this->datalayer ?? $datalayer)->executeQuery($this);
    }

    /** Define a conexão datalayer que deve receber a query */
    function datalayer(?string $datalayer): static
    {
        if ($datalayer)
            $datalayer = Datalayer::name($datalayer);

        $this->datalayer = $datalayer;
        return $this;
    }

    /** Define uma tabela para ser utilizada na query */
    function table(?string $table): static
    {
        $this->table = $table;
        return $this;
    }

    protected function mountTable(): string
    {
        if ($this->table)
            return substr_count($this->table, '.') ? $this->table : "`$this->table`";
        return '';
    }
}