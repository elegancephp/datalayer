<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena um instante em forma de inteiro */
class FieldTime extends Field
{

    protected function setValue($value)
    {
        $value = ($value === true) ? time() : ($value ?? time());
        if (is_numeric($value)) {
            $this->VALUE = $value;
        } else if (is_string($value)) {
            $this->VALUE = strtotime(str_replace('/', '-', $value));
        }
    }

    /** Retorna o valor do objeto formatado */
    function getFormat($format = false)
    {
        return ($format) ? date($format, $this->VALUE) : $this->VALUE;
    }

    /**
     * Compara o objeto com outro time
     * @param int|FieldTime|null $time time que deve ser utilizado na comparação
     * @param int $status Um status para ser comparado (neste caso o metodo retorna BOOLEAN)
     * @return int O status do objeto em relação a comparação [-1 MENOR, 0 IGUAL, 1 MAIOR]
     */
    function check($time = null, $status = null)
    {
        if (!$time || !is_class($time, FieldTime::class)) {
            $time = new FieldTime($time);
        }
        $result = $this->get() <=> $time->get();
        return !is_null($status) ? $status == $result : $result;
    }

    /** Modifica $n segundos ao time do objeto */
    function sumSeconds($n)
    {
        $this->VALUE += $n;
        return $this;
    }

    /** Modifica $n minutos ao time do objeto */
    function sumMinutes($n)
    {
        return $this->sumSeconds(60 * $n);
    }

    /** Modifica $n horas ao time do objeto */
    function sumHours($n)
    {
        return $this->sumMinutes(60 * $n);
    }

    /** Modifica $n dias ao time do objeto */
    function sumDays($n)
    {
        return $this->sumHours(24 * $n);
    }

    /** Modifica $n semanas ao time do objeto */
    function sumWeeks($n)
    {
        return $this->sumDays(7 * $n);
    }

    /** Modifica $n meses ao time do objeto */
    function sumMonths($n)
    {
        $n             = intval($n);
        $this->VALUE = strtotime("$n months", $this->VALUE);
        return $this;
    }

    /** Modifica $n anos ao time do objeto */
    function sumYears($n)
    {
        $n             = intval($n);
        $this->VALUE = strtotime("$n year", $this->VALUE);
        return $this;
    }

    /**
     * Calcula o proximo dia que corresponde a referencia
     * @param string|int $ref Referencia do dia procurado
     * @param boolean $return Se o resultado deve ser retornado ao inves de atribuído ao objeto
     * @return $this|int
     */
    function next($ref = '', $return = false)
    {
        $ref = is_string($ref) ? mb_strtolower(substr($ref, 0, 3)) : $ref;
        if (is_string($ref) && isset(self::$REF[$ref])) {
            $time = strtotime("next " . self::$REF[$ref], $this->VALUE);
        } else if (is_numeric($ref) && intval($ref) <= 31) {
            $ref     = intval($ref);
            $timeDay = intval($this->get('d'));
            if ($ref <= 28) {
                if ($ref > $timeDay) {
                    $time = strtotime(date("Y-m-$ref H:i:s", $this->VALUE));
                } else {
                    $time = strtotime('+1 month', strtotime(date("Y-m-$ref H:i:s", $this->VALUE)));
                }
            } else {
                if ($ref > $timeDay) {
                    $time = strtotime(date("Y-m-$ref H:i:s", $this->VALUE));
                    if (intval(date('d', $time)) != $ref) {
                        $time = strtotime(date("Y-m-$ref H:i:s", $time));
                    }
                } else {
                    $time = strtotime('+1 month', strtotime(date("Y-m-$ref H:i:s", $this->VALUE)));
                }
                $day = intval(date('d', $time));
                while ($day != $ref) {
                    $time = strtotime('+1 month', strtotime(date("Y-m-$ref H:i:s", $time)));
                    $day  = intval(date('d', $time));
                }
            }
        } else {
            return $this;
        }
        if ($return) {
            return $time;
        }

        $this->VALUE = $time;
        return $this;
    }

    /**
     * Calcula o ultimo dia que corresponde a referencia
     * @param string|int $ref Referencia do dia procurado
     * @param boolean $return Se o resultado deve ser retornado ao inves de atribuído ao objeto
     * @return $this|int
     */
    function last($ref = '', $return = false)
    {
        $ref = is_string($ref) ? mb_strtolower($ref) : $ref;
        if (is_string($ref) && isset(self::$REF[$ref])) {
            $time = strtotime("last " . self::$REF[$ref], $this->VALUE);
        } else if (is_numeric($ref) && intval($ref) <= 31) {
            $ref     = intval($ref);
            $timeDay = intval($this->get('d'));
            if ($timeDay > $ref) {
                $time = strtotime(date("Y-m-$ref H:i:s", $this->VALUE));
            } else {
                $time = strtotime('-1 month', strtotime(date("Y-m-$ref H:i:s", $this->VALUE)));
                if ($ref > 28) {
                    $time = strtotime('-2 month', strtotime(date("Y-m-$ref H:i:s", $this->VALUE)));
                }
            }
        } else {
            return $this;
        }
        if ($return) {
            return $time;
        }

        $this->VALUE = $time;
        return $this;
    }


    protected static $REF = [
        'seg' => 'monday',
        'segunda' => 'monday',
        'ter' => 'tuesday',
        'terca' => 'tuesday',
        'qua' => 'wednesday',
        'quarta' => 'wednesday',
        'qui' => 'thursday',
        'quinta' => 'thursday',
        'sex' => 'friday',
        'sexta' => 'friday',
        'sab' => 'saturday',
        'sabado' => 'saturday',
        'dom' => 'sunday',
        'domingo' => 'sunday',
    ];
}