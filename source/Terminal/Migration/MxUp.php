<?php

namespace Terminal\Migration;

use Elegance\MxCmd;

abstract class MxUp extends MxCmd
{
    use TraitMigration;

    protected static function execute($datalayer = null)
    {
        self::loadDatalayer($datalayer);

        $result = self::executeNext();
        if (!$result)
            MxCmd::show('Todas as mudanças foram aplicadas');
        return $result;
    }
}
