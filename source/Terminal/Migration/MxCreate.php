<?php

namespace Terminal\Migration;

use Elegance\MxCmd;

abstract class MxCreate extends MxCmd
{
    protected static function execute()
    {
        $params = implode(' ', func_get_args());
        MxCmd::run("create.migration $params");
    }
}
