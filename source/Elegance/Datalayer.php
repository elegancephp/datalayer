<?php

namespace Elegance;

use Elegance\Datalayer\Connection;
use Error;

abstract class Datalayer
{
    /** @var Connection[] */
    protected static $instance = [];

    protected static $type = [];

    /** Retorna um objeto datalayer */
    static function &get(?string $datalayer): Connection
    {
        $datalayer = self::name($datalayer);

        if (!isset(self::$instance[$datalayer]))
            self::register($datalayer);

        return self::$instance[$datalayer];
    }

    /** Registra um datalayer */
    static function register(string $datalayer, array $data = []): void
    {
        $datalayer = self::name($datalayer);

        $data['type'] = $data['type'] ?? env(strtoupper("DB_${datalayer}_TYPE"));

        if (!boolval($data['type'] ?? false))
            throw new Error("$datalayer datalayer type required");

        $data['type'] = strtoupper($data['type']);

        if (!isset(self::$type[$data['type']]))
            throw new Error('connection type not registred');

        $connection = self::$type[$data['type']];

        self::$instance[$datalayer] = new $connection($datalayer, $data);
    }

    /** Registra uma classe para responder por um tipo de conexão */
    static function registerType(string $type, string $connectionClass): void
    {
        if (class_exists($connectionClass))
            self::$type[strtoupper($type)] = $connectionClass;
        else
            throw new Error('connection class not found');
    }

    /** Retorna o nome formatado do datalayer */
    static function name(?string $datalayer): string
    {
        $datalayer = $datalayer ?? env('DATALAYER_DEFAULT');

        if (!$datalayer)
            throw new Error("datalayer name required");

        $datalayer = ucfirst($datalayer);
        return $datalayer;
    }
}