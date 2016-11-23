<?php

/** 
 * Classe utilizada para recuperar, iniciar e finalizar processos no Sistema
 * Operacional
 * 
 * @package     crphp
 * @subpackage  wmi
 * @author      Fábio J L Ferreira <contato@fabiojanio.com>
 * @license     MIT (consulte o arquivo license disponibilizado com este pacote)
 * @copyright   (c) 2016, Fábio J L Ferreira
 */

namespace Crphp\Wmi\Sistema;

use Crphp\Core\Sistema\Conector;
use Crphp\Wmi\Auxiliares\Transformar;
use Crphp\Core\Interfaces\Sistema\ProcessosInterface;

class Processos implements ProcessosInterface
{
    /**
     * Lista de processos ativos no SO
     *
     * @var object
     */
    private $instancia;
    
    /**
     * Critério de busca
     *
     * @var string|int|null
     */
    private $criterio;
    
    /**
     * Consulta os dados referentes a processos ativos no SO
     * 
     * @param \Crphp\Wmi\Conectores\Conector $conexao
     * @param string|int|null                $filtro
     */
    function __construct(Conector $conexao, $filtro = null)
    {
        if(is_string($filtro)) {
            $this->criterio = "and Name='{$filtro}'";
        } elseif (is_int($filtro)) {
            $this->criterio = "and ProcessID={$filtro}";
        } else {
            $this->criterio = null;
        }
        
        /*
         * Analisar troca de win32_process que passa uma visão de processo
         * para Win32_PerfFormattedData_PerfProc_Process ou ﻿Win32_PerfRawData_PerfProc_Process
         * que mostra uma visão de SO proxima ao task manager
         */
        $this->instancia = $conexao->executar(
                                                "select
                                                    Name,
                                                    ProcessID,
                                                    Priority,
                                                    WorkingSetSize,
                                                    CreationDate,
                                                    ExecutablePath
                                                  from Win32_Process
                                                  where processid <> 0 {$this->criterio}"
                                             );
    }
    
    /**
     * Encerra o(s) processo(s)
     * 
     * @return int|null
     */
    public function killProcesso()
    {
        if($this->criterio != null) {
            
            foreach ($this->instancia as $s) {
                $retorno[] = $s->Terminate();
            }
        }
        
        // retorna o total de processos encerrados ou null caso nãh haja criterio de busca
        return (isset($retorno)) ? count($retorno) : null;
    }
    
    
    /**
     * Altera a prioridade do processo
     * 
     * @param string $prioridade
     * @return array|null
     */
    public function alterarPrioridade($prioridade = null)
    {
        $resultado = [
                        0  => 'Alteração efetuada com sucesso',
                        2  => 'Acesso negado',
                        3  => 'Privilégio insuficiente',
                        8  => 'Erro desconhecido',
                        9  => 'Caminho não encontrado',
                        21 => 'Parâmetro inválido',
                        '22-4294967295' => 'Retorno desconhecido'
                     ];
        
        /*
         *  @see https://msdn.microsoft.com/en-us/library/aa393587(v=vs.85).aspx
         * 
         * Nível de prioridade:
         * 64     => Abaixo do normal
         * 16384  => Baixa
         * 32     => Normal
         * 32768  => Aciam do normal
         * 128    => Prioridade alta
         * 256    => Tempo real
         */
        $prioridadeExiste = in_array($prioridade, ['64', '16384', '32', '32768', '128', '256']);
                
        if($this->criterio != null && $prioridadeExiste) {
                        
            foreach ($this->instancia as $s) {
                $retorno = [$s->setPriority($prioridade) => strtr($s->setPriority($prioridade), $resultado)];
            }
        }
        
        return (isset($retorno)) ? $retorno : null;
    }
    
    /**
     * Retorna uma visão detalhada dos processos ativos no SO
     * 
     * @return array|null
     */
    public function detalhes()
    {
        foreach ($this->instancia as $p) {
            
            $processo[$p->ProcessId] = [
                                            "nome" => $p->Name,
                                            "Priority" => $p->Priority,
                                            "memoriaTotal" => Transformar::converterBytes($p->WorkingSetSize),
                                            "inicioDoProcesso" => Transformar::converterTimestamp($p->CreationDate),
                                            "path" => $p->ExecutablePath
                                       ];
        }
        
        return (isset($processo)) ? $processo : null;
    }
}