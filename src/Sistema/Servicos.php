<?php

/** 
 * Essa classe disponibiliza um meio capaz de listar os serviços disponíveis no
 * Sistema Operacional e consequentemente uma forma de manipular tais serviços
 * 
 * @package     crphp
 * @subpackage  wmi
 * @author      Fábio J L Ferreira <contato@fabiojanio.com>
 * @license     MIT (consulte o arquivo license disponibilizado com este pacote)
 * @copyright   (c) 2016, Fábio J L Ferreira
 */

namespace Crphp\Wmi\Sistema;

use Crphp\Core\Sistema\Conector;
use Crphp\Core\Interfaces\Sistema\ServicosInterface;

class Servicos implements ServicosInterface
{
    /**
     * Armazena uma instância de Win32_Service
     *
     * @var object
     */
    private $servico;
    
    /**
     * O valor armazenado nesta variável representa um possível nome de serviço,
     * desta forma a consulta realizada no sistema será direcionada para este filtro
     *
     * @var string|null 
     */
    private $criterio;
    
    /**
     * Consulta os dados referentes aos serviços instalados no SO
     * 
     * @param   \Crphp\Wmi\Conectores\Conector $conexao
     * @param   string|null $servico
     * @return  null
     */
    function __construct(Conector $conexao, $servico = null)
    {        
        $this->criterio = (is_string($servico) && !empty($servico)) ? "WHERE Name='{$servico}'" : null;

        $this->servico = $conexao->executar(
                                                "SELECT
                                                    Name,
                                                    Caption,
                                                    State,
                                                    StartMode
                                                FROM Win32_Service {$this->criterio}"
                                           );
    }
        
    /**
     * Retorna o status do comando stop
     * 
     * @return string|null
     */
    public function stopServico()
    {
        $code = [
                    '0' => 'Comando enviado',
                    '2' => 'O usuário não tem o acesso necessário',
                    '3' => 'O serviço não pode ser interrompido porque outros serviços que estão sendo executados são dependentes deste',
                    '5' => 'O serviço já está parado'
                ];
        
        if(is_string($this->criterio) && !empty($this->criterio)) {
            
            foreach ($this->servico as $s) {
                $retorno = $s->StopService();
            }
        }

        return (isset($retorno)) ? strtr($retorno, $code) : null;
    }
    
    /**
     * Retorna o status do comando de start
     * 
     * @return string|null
     */
    public function startServico()
    {
        $code = [
                    '0'  => 'Comando enviado',
                    '2'  => 'O usuário não tem o acesso necessário',
                    '6'  => 'Não foi possível iniciar o serviço',
                    '8'  => 'Falha desconhecida ao iniciar o serviço',
                    '10' => 'O serviço já está iniciado'
                ];
        
        if(is_string($this->criterio) && !empty($this->criterio)) {
            
            foreach ($this->servico as $s) {
                $retorno = $s->StartService();
            }
        }
        
        return (isset($retorno)) ? strtr($retorno, $code) : null;
    }
    
    /**
     * Retorna uma visão detalhada a respeito do(s) serviço(s)
     * 
     * @return array|null
     */
    public function detalhes()
    {
        $langStatus = [
                            'Stopped' => 'Parado',
                            'Start Pending' => 'Iniciando',
                            'Stop Pending' => 'Parando',
                            'Running' => 'Iniciado',
                            'Continue Pending' => 'Ação Pendente',
                            'Pause Pending' => 'Pendente',
                            'Paused' => 'Em Pausa',
                            'Unknown' => 'Desconhecido'
                      ];
        
        $langMode =   [
                            'Auto' => 'Automático',
                            'Disabled' => 'Desativado'
                      ];
        
        foreach ($this->servico as $s)
        {
            $status = (array_key_exists($s->State, $langStatus)) ? $langStatus[$s->State] : $s->State;
            $statusMode = (array_key_exists($s->StartMode, $langMode)) ? $langMode[$s->StartMode] : $s->StartMode;
            
            $servico[] = [
                            'nomeDoServico' => utf8_encode($s->Name),
                            'nomeParaExibicao' => utf8_encode($s->Caption),
                            'status' => $status,
                            'inicializacao' => $statusMode
                         ];
            
        }
        
        return (isset($servico)) ? $servico : null;
    }
    
    /* em desenvolvimento
    public function listarServicoDependente()
    {
        if($this->filtro == null)
        {
            return null;
        }
        foreach ($this->servico as $s)
        {
            $pai[$s->Name] = array('nomeParaExibicao' => utf8_encode($s->Name),
                                'nomeDoServico' => utf8_encode($s->Caption));
            
            foreach (Windows::executar("Associators of {Win32_Service.Name='$s->Name'} WHERE Role = Antecedent") as $sf)
            {
                $dependente[$sf->Name] = array('nomeParaExibicao' => utf8_encode($sf->Name),
                                                'nomeDoServico' => utf8_encode($sf->Caption));
            
            $pai[$s->Name]['dependente'] = $dependente;
            }
        }
    }
     */
}