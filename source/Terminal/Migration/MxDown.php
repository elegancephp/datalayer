<?php

namespace Terminal\Migration;

use Elegance\MxCmd;

abstract class MxDown extends MxCmd
{
    use TraitMigration;

    protected static function execute($datalayer = null)
    {
        self::loadDatalayer($datalayer);

        $result = self::executePrev();
        if (!$result)
            MxCmd::show('Todas as mudanças foram revertidas');
        return $result;
    }
}
