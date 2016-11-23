<?php

/**
 * Classe utilizada para formatar valores, tais como unidades de medida, data,
 * hora, etc
 * 
 * @package     crphp
 * @subpackage  wmi
 * @author      Fábio J L Ferreira <contato@fabiojanio.com>
 * @license     MIT (consulte o arquivo license disponibilizado com este pacote)
 * @copyright   (c) 2016, Fábio J L Ferreira
 */

namespace Crphp\Wmi\Auxiliares;

use Crphp\Core\Auxiliares\Capacidade;
use Crphp\Core\Interfaces\Auxiliares\ConverterTempo;

class Transformar extends Capacidade implements ConverterTempo
{
    /**
     * Método responsável por realizar a conversão do timestamp para um formato
     * de data/hora compreensivo
     * 
     * @param   string $timestamp
     * @param   string $formato
     * @return  string
     */
    public static function converterTimestamp($timestamp, $formato = "d/m/Y H:i:s")
    {
        // Captura a primeira parte/posição do timestamp
        $tempo = substr($timestamp, 0, strpos($timestamp, '.'));
        
        $dia = substr($tempo, 6, 2);
        $mes = substr($tempo, 4, 2);
        $ano = substr($tempo, 0, 4);
        
        $hora = substr($tempo, 8, 2);
        $minuto = substr($tempo, 10, 2);
        $segundo = substr($tempo, 12, 2);        
        
        $date = new \DateTime("{$dia}-{$mes}-{$ano} {$hora}:{$minuto}:{$segundo}");
        
        return $date->format($formato);
    }
}