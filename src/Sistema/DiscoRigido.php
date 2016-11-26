<?php

/**
 * Classe utilizada para recuperar informações referentes ao disco rígido
 * da máquina
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
use Crphp\Core\Interfaces\Sistema\DiscoRigidoInterface;

class DiscoRigido implements DiscoRigidoInterface
{
    /**
     * Armazena uma lista das partições reconhecidas pelo host remoto
     *
     * @var object
     */
    private $particoes;

    /**
     * Consulta todas as partições de disco reconhecidas pelo host remoto
     * 
     * @param   \Crphp\Wmi\Conectores\Conector $conexao
     * @param   string|null                    $particao
     * @return  null
     */
    function __construct(Conector $conexao, $particao = null)
    {
        $filtro = ($particao === null) ? "" : "and Caption='{$particao}:'";

        $this->particoes = $conexao->executar(
                                                "Select
                                                    FileSystem,
                                                    Caption,
                                                    Size,
                                                    FreeSpace
                                                from Win32_LogicalDisk
                                                where DriveType=3 {$filtro}"
                                             );
    }

    /**
     * Retorna a capacidade de cada partição
     * 
     * @return array
     */
    public function capacidade()
    {
        foreach ($this->particoes as $p) {
            $particao[$p->Caption] = Transformar::converterBytes($p->Size);
        }
        
        return $particao;
    }

    /**
     * Retorna o espaço livre em cada partição e o percentual que isso representa
     * 
     * @retun array
     */
    public function espacoLivre()
    {
        foreach ($this->particoes as $p) {
            $total = Transformar::converterBytes($p->FreeSpace);
            $percentual = sprintf("%0.2f%%", (100 * $p->FreeSpace / $p->Size));
            
            $particao[$p->Caption] = [
                'total' => $total,
                'percentual' => $percentual
            ];
        }
        
        return $particao;
    }

    /**
     * Retorna o espaço utilizado e o percentual que isso representa
     * 
     * @return  array
     */
    public function espacoUtilizado()
    {
        foreach ($this->particoes as $p) {
            $total = Transformar::converterBytes($p->Size - $p->FreeSpace);
            $percentual = sprintf("%0.2f%%", (100 * ($p->Size - $p->FreeSpace) / $p->Size));
            
            $particao[$p->Caption] = [
                'total' => $total,
                'percentual' => $percentual
            ];
        }
        
        return $particao;
    }

    /**
     * Retorna uma visão geral referente as partições consultadas
     * 
     * @return array
     */
    public function detalhes()
    {
        foreach ($this->particoes as $p) {
            $capacidade = $this->capacidade();
            $livre = $this->espacoLivre();
            $utilizado = $this->espacoUtilizado();
            
            $particao[$p->Caption] = [
                'capacidade' => $capacidade[$p->Caption],
                'espacoLivre' => $livre[$p->Caption]['total'],
                'percentualLivre' => $livre[$p->Caption]['percentual'],
                'espacoUtilizado' => $utilizado[$p->Caption]['total'],
                'percentualUtilizado' => $utilizado[$p->Caption]['percentual'],
                'sistemaDeArquivo' => $p->FileSystem
            ];
        }
        
        return $particao;
    }
}