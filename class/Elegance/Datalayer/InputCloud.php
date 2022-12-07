<?php

namespace Elegance\Datalayer;

use Elegance\Datalayer;

class InputCloud
{
    function __construct(?string $datalayer)
    {
        $this->datalayer = Datalayer::name($datalayer);
    }

    /** Executa um comando no datalayer */
    function run(string $action, string|array $data = []): array
    {
        $data = is_json($data) ? json_decode($data, true) : $data;
        $data = is_array($data) ? $data : [$data];

        $datalayer = Datalayer::get($this->datalayer);

        if (is_callable([$datalayer, $action])) {
            $response = [$datalayer->{$action}(...$data)];
            return $response;
        }

        return [false];
    }
}