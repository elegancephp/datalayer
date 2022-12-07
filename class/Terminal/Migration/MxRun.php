<?php

namespace Terminal\Migration;

use Elegance\MxCmd;

abstract class MxRun extends MxCmd
{
    use TraitMigration;

    protected static function execute($datalayer = null)
    {
        self::loadDatalayer($datalayer);

        while (MxCmd::run("migration.up " . self::$datalayer));
    }
}
