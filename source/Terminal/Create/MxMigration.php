<?php

namespace Terminal\Create;

use Elegance\Datalayer;
use Elegance\File;
use Elegance\Import;
use Elegance\MxCmd;

abstract class MxMigration extends MxCmd
{
    protected static function execute($datalayer = null, $name = null)
    {
        $datalayer = Datalayer::name($datalayer);

        $path = path(env('PATH_MIGRATION'), $datalayer);

        $time = time();

        $name = $name ? "${time}_${name}" : $time;

        $template = dirname(__DIR__, 3) . "/library/template/create/migration.txt";

        $data = [
            'PHP' => '<?php',
            'time' => "$time",
            'name' => $name
        ];

        $template = Import::output($template, $data);
        $template = prepare($template, $data);

        File::create("$path/$name.php", $template);

        MxCmd::show("Arquivo de migration [[#]] criado", "$name");
    }
}
