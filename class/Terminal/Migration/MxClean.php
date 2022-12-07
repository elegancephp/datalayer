<?php

namespace Terminal\Migration;

use Elegance\MxCmd;

abstract class MxClean extends MxCmd
{
    use TraitMigration;

    protected static function execute($datalayer = null)
    {
        self::loadDatalayer($datalayer);

        while (MxCmd::run("migration.down " . self::$datalayer));
    }
}
