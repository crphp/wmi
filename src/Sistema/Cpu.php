<?php

/** 
 * Classe utilizada para recuperar informações referentes a CPU da máquina
 * 
 * @package     crphp
 * @subpackage  wmi
 * @author      Fábio J L Ferreira <contato@fabiojanio.com>
 * @license     MIT (consulte o arquivo license disponibilizado com este pacote)
 * @copyright   (c) 2016, Fábio J L Ferreira
 */

namespace Crphp\Wmi\Sistema;

use Crphp\Core\Sistema\Conector;
use Crphp\Core\Interfaces\Sistema\CpuInterface;

class Cpu implements CpuInterface
{
    /**
     * Armazena uma lista de informações referentes ao processador (CPU)
     *
     * @var object
     */
    private $cpu;

    /**
     * Consulta as informações da CPU(s) reconhecida(s) pelo host remoto
     * 
     * @param \Crphp\Wmi\Conectores\Conector $conexao
     * @return null
     */
    public function __construct(Conector $conexao)
    {
        $this->cpu = $conexao->executar(
                                          "SELECT
                                              Caption,
                                              DeviceID,
                                              LoadPercentage,
                                              CurrentClockSpeed,
                                              Name,
                                              NumberOfCores,
                                              DataWidth,
                                              NumberOfLogicalProcessors
                                          FROM Win32_Processor"
                                       );
    }
        
    /**
     * Retorna visão geral referente a CPU
     * 
     * @return array
     */
    public function detalhes()
    {
        foreach ($this->cpu as $c) {
            
            $cpu[$c->DeviceID] = [
                                    'nome' => $c->Name,
                                    'arquitetura' => $c->DataWidth,
                                    'mhz' => $c->CurrentClockSpeed,
                                    'nucleos' => $c->NumberOfCores,
                                    'processadoresLogicos' => $c->NumberOfLogicalProcessors,
                                    'cargaDoProcessador' => $c->LoadPercentage
                                 ];
        }
        
        return $cpu;
    }
}