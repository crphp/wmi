<?php

/** 
 * Classe utilizada para recuperar informações referentes a memória física da
 * máquina
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
use Crphp\Core\Interfaces\Sistema\MemoriaRamInterface;

class MemoriaRam implements MemoriaRamInterface
{
    /**
     * Memória Ram livre (em Bytes)
     *
     * @var object 
     */
    private $memoriaLivre;
    
    /**
     * Memória Ram total (em Bytes)
     *
     * @var object
     */
    private $memoriaTotal;
    
    /**
     * Consulta as informações referentes a memória física reconhecida(s) pelo host remoto
     * 
     * @param   \Crphp\Wmi\Conectores\Conector $conexao
     * @return  null
     */
    public function __construct(Conector $conexao)
    {
        $this->memoriaLivre = $conexao->executar("select AvailableBytes from Win32_PerfRawData_PerfOS_Memory");
        $this->memoriaTotal = $conexao->executar("Select TotalPhysicalMemory from Win32_ComputerSystem");
    }
    
    /**
     * Retorna o total de memória física livre e o percentual que este total representa
     * 
     * @param boolean $emBytes
     * @return array
     */
    public function memoriaLivre($emBytes = false)
    {
        foreach ($this->memoriaLivre as $ml) {
            $livre = ($emBytes) ? $ml->AvailableBytes : Transformar::converterBytes($ml->AvailableBytes);
        }
        
        return [
            'livre' => $livre,
            'percentualLivre' => sprintf("%0.2f%%", (100 * $livre / $this->memoriaTotal($emBytes)))
        ];
    }
    
    /**
     * Retorna o total de memória física utilizada e o percentual que este total representa
     * 
     * @return array
     */
    public function memoriaUtilizada()
    {
        $livre = $this->memoriaLivre(true);
        $total = $this->memoriaTotal(true);

        return [
                    'utilizado' => Transformar::converterBytes($total - $livre['livre']),
                    'percentualUtilizado' => sprintf("%0.2f%%", (($total - $livre['livre']) * 100 / $total))
               ];
    }

    /**
     * Retorna o total de memória física
     * 
     * @param boolean $emBytes
     * @return string
     */
    public function memoriaTotal($emBytes = false)
    {
        foreach ($this->memoriaTotal as $mt) {
            return ($emBytes) ? $mt->TotalPhysicalMemory : Transformar::converterBytes($mt->TotalPhysicalMemory);
        }
    }
    
    /**
     * Retorna uma visão geral referente a memória física
     * 
     * @return array
     */
    public function detalhes()
    {
        $livre = $this->memoriaLivre();
        $utilizada = $this->memoriaUtilizada();
        
        return [
                    'livre' => $livre['livre'],
                    'percentualLivre' => $livre['percentualLivre'],
                    'utilizado' => $utilizada['utilizado'],
                    'percentualUtilizado' => $utilizada['percentualUtilizado'],
                    'memoriaTotal' => $this->memoriaTotal()
               ];
    }
}