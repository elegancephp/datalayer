<?php

namespace Terminal;

use Elegance\Datalayer;
use Elegance\Dir;
use Elegance\File;
use Elegance\Import;
use Elegance\MxCmd;

abstract class MxDbdriver extends MxCmd
{
    protected static string $datalayer = '';
    protected static string $path = '';
    protected static array $map = [];

    protected static function execute($datalayer = null)
    {
        self::$datalayer = Datalayer::name($datalayer);
        self::$map = Datalayer::get($datalayer)->map();
        self::$path = "./class/Model/Db" . self::$datalayer;
        MxCmd::show("Criando drivers para [[#]]", self::$datalayer);

        MxCmd::show("--------------------");

        Dir::remove(self::$path . "/Driver", true);

        self::createDriver_database();
        self::createClass_database();

        foreach (array_keys(self::$map) as $table) {
            self::createDriver_table($table);
            self::createDriver_record($table);

            self::createClass_table($table);
            self::createClass_record($table);

            MxCmd::show("Tabela $table [OK]");
        }

        MxCmd::show("--------------------");
        MxCmd::show("Driver instalados");
    }

    protected static function createDriver_database(): void
    {
        $fileName = "DriverDb" . self::$datalayer;

        $start = [];
        $method = [];
        $varTable = [];

        foreach (self::$map as $table => $map) {
            $data = [
                'className' => $fileName,
                'tableName' => $table,
                'comment' => $map['comment'],
                'tableClassName' => ucfirst($table)
            ];
            $start[] = self::template('driver/main/start', $data);
            $method[] = self::template('driver/main/method', $data);
            $varTable[] = self::template('driver/main/varTable', $data);
        }

        $data = [
            'className' => $fileName,
            'start' => implode('', $start),
            'method' => implode('', $method),
            'varTable' => implode('', $varTable),
        ];

        $content = self::template('driver/main/class', $data);

        File::create(self::$path . "/Driver/$fileName.php", $content, true);
    }

    protected static function createDriver_table(string $table): void
    {
        $fileName = "DriverTable" . ucfirst($table);

        $map = self::$map[$table];

        $data = [
            'tableName' => $table,
            'tableClassName' => ucfirst($table),
            'useMetaField' => isset($map['fields']['_meta']) ? 'true' : 'false',
            'useSmartControl' => isset($map['fields']['_create']) ? 'true' : 'false',
        ];

        $data['smartControl'] =
            fn () => isset($map['fields']['_create'])
                ? self::template('driver/table/smartControl', $data)
                : '';

        $content = self::template('driver/table/class', $data);

        File::create(self::$path . "/Driver/$fileName.php", $content, true);
    }

    protected static function createDriver_record(string $table): void
    {
        $fileName = "DriverRecord" . ucfirst($table);

        $autocomplete = [];
        $nameFields = [];
        $createFields = [];

        foreach (self::$map[$table]['fields'] as $field => $map) {
            $nameFields[] = "'$field'";

            if (!str_starts_with($field, '_')) {

                $value = 'null';

                if (!is_null($map['default'])) {
                    if (is_string($map['default'])) {
                        $value = $map['default'] == "''" ? $map['default'] : "'$map[default]'";
                    } else if (is_numeric($map['default'])) {
                        $value = $map['default'];
                    }
                }

                $data = [
                    'name' => $field,
                    'comment' => $map['comment'],
                    'type' => ucfirst($map['type']),
                    'value' => $value,
                    'extras' => match ($map['type']) {
                        'string', 'text' => $map['size'] ? "->size($map[size])" : '',
                        'idx', 'ids' => prepare(
                            "->datalayer('[#config.datalayer]')->table('[#config.table]')",
                            $map
                        ),
                        default => ''
                    }
                ];

                if ($map['type'] == 'idx') {
                    $data['fieldDatalayer'] = 'Db' . ucfirst($map['config']['datalayer']);
                    $data['fieldTable'] = ucfirst($map['config']['table']);
                    $autocomplete[] = self::template('driver/record/autocomplete_dinamicId', $data);
                } else {
                    $autocomplete[] = self::template('driver/record/autocomplete', $data);
                }
                $createFields[] = self::template("driver/record/createFields", $data);
            }
        }

        $data = [
            'tableName' => $table,
            'tableClassName' => ucfirst($table),
            'autocomplete' => implode("\n * ", $autocomplete),
            'createFields' => implode('', $createFields),
            'nameFields' => implode(',', $nameFields)
        ];

        $data['smartControl'] =
            fn () => isset(self::$map[$table]['fields']['_create'])
                ? self::template('driver/record/smartControl', $data)
                : '';

        $content = self::template('driver/record/class', $data);

        File::create(self::$path . "/Driver/$fileName.php", $content, true);
    }

    protected static function createClass_database(): void
    {
        $fileName = "Db" . self::$datalayer;

        $data = [
            'className' => $fileName
        ];

        $content = self::template('class/main/class', $data);

        File::create(self::$path . "/$fileName.php", $content);
    }

    protected static function createClass_table(string $table): void
    {
        $fileName = "Table" . ucfirst($table);

        $map = self::$map[$table];

        $data = [
            'comment' => empty($map['comment']) ? '' : "\n/** $map[comment] */",
            'tableName' => $table,
            'tableClassName' => ucfirst($table)
        ];

        $content = self::template('class/table/class', $data);

        File::create(self::$path . "/Table/$fileName.php", $content);
    }

    protected static function createClass_record(string $table): void
    {
        $fileName = "Record" . ucfirst($table);

        $map = self::$map[$table];

        $data = [
            'tableName' => $table,
            'tableClassName' => ucfirst($table)
        ];

        $data['smartMeta'] = fn () => isset($map['fields']['_meta']) ? self::template('class/record/smartMeta', $data) : '';

        $content = self::template('class/record/class', $data);

        File::create(self::$path . "/Record/$fileName.php", $content);
    }

    /** Retrona um teplate de driver */
    protected static function template(string $file, array $data = []): string
    {
        $file = dirname(__DIR__, 2) . "/library/template/dbdriver/$file.txt";

        $data['PHP'] = '<?php';
        $data['datalayer'] = self::$datalayer;
        $data['namespace'] = "Model\Db" . self::$datalayer;

        $template = Import::output($file, $data);

        return prepare($template, $data);
    }
}