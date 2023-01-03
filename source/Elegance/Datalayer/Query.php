<?php

namespace Elegance\Datalayer;

use Elegance\Datalayer\Query\Delete;
use Elegance\Datalayer\Query\Insert;
use Elegance\Datalayer\Query\Select;
use Elegance\Datalayer\Query\Update;

abstract class Query
{
    /** Retorna uma instancia de query do tipo Delete */
    static function delete(?string $table = null): Delete
    {
        return new Delete($table);
    }

    /** Retorna uma instancia de query do tipo Insert */
    static function insert(?string $table = null): Insert
    {
        return new Insert($table);
    }

    /** Retorna uma instancia de query do tipo Select */
    static function select(?string $table = null): Select
    {
        return new Select($table);
    }

    /** Retorna uma instancia de query do tipo Update */
    static function update(?string $table = null): Update
    {
        return new Update($table);
    }
}