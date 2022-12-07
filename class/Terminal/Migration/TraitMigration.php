<?php

namespace Terminal\Migration;

use Exception;
use Elegance\Datalayer;
use Elegance\Dir;
use Elegance\File;
use Elegance\Import;
use Elegance\MxCmd;

trait TraitMigration
{
    protected static $datalayer;
    protected static $path;

    protected static function loadDatalayer($datalayer)
    {
        self::$datalayer = Datalayer::name($datalayer);

        Datalayer::get($datalayer);

        self::$path = path(env('PATH_MIGRATION'), self::$datalayer);
    }

    /** Retorna a lista de arquivos de migration */
    protected static function getFiles(): array
    {
        $files = [];

        foreach (Dir::seek_for_file(self::$path) as $file)
            if (substr($file, -4) == '.php')
                $files[substr($file, 0, 10)] = self::$path . "/$file";

        return $files;
    }

    /** Retorna/Altera o ID da ultima migration executada */
    protected static function lastId(?int $id = null): int
    {
        $executed = Datalayer::get(self::$datalayer)
            ->config('Elegance_executedMigration');

        $executed = is_json($executed) ? json_decode($executed, true) : [];

        if (!is_null($id)) {

            if ($id > 0) {
                $executed[] = $id;
            } else {
                $executed = array_slice($executed, 0, $id);
            }

            Datalayer::get(self::$datalayer)
                ->config('Elegance_executedMigration', json_encode($executed));
        }
        return array_pop($executed) ?? 0;
    }

    /** Executa um arquivo de migration */
    protected static function executeMigration(string $file, bool $mode)
    {

        if ($mode) {
            MxCmd::show("Aplicando migration [#]", File::getOnly($file));
        } else {
            MxCmd::show("Revertendo migration [#]", File::getOnly($file));
        }

        Import::return($file, [
            'datalayer' => self::$datalayer,
            'mode' => $mode
        ]);
    }

    /** Executa o proximo arquivo da lista de migration */
    protected static function executeNext(): bool
    {
        $files = self::getFiles();
        $lasId = self::lastId();

        foreach ($files as $id => $file) {
            if ($id > $lasId) {
                self::executeMigration($file, true);
                self::lastId($id);
                return true;
            }
        }

        return  false;
    }

    /** Reverte o ultimo arquivo executado da lista de migration */
    protected static function executePrev()
    {
        $lasId = self::lastId();

        if ($lasId) {
            $files = self::getFiles();

            if (isset($files[$lasId])) {
                self::executeMigration($files[$lasId], false);
                self::lastId(-1);
                return true;
            } else {
                throw new Exception("Arquivo [$lasId] não encotrado na lista de migrations");
            }
        }

        return  false;
    }
}
