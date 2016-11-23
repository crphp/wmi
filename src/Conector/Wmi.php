<?php

/**
 * Essa classe fornece uma interface de conexão WMI com máquinas Windows,
 * possibilitando dessa forma a execução de comandos remotos de forma
 * rápida, eficiente e segura
 * 
 * @package     crphp
 * @subpackage  wmi
 * @author      Fábio J L Ferreira <contato@fabiojanio.com>
 * @license     MIT (consulte o arquivo "license" disponibilizado com este pacote)
 * @copyright   (c) 2016, Fábio J L Ferreira
 */

namespace Crphp\Wmi\Conector;

use COM;
use \Exception;
use \RuntimeException;
use Crphp\Core\Sistema\Conector;

class Wmi extends Conector
{
    /**
     * Estabelece conexão com máquinas Windows via chamada COM
     * 
     * @param   string  $host
     * @param   string  $usuario
     * @param   string  $senha
     * @param   int     $porta
     * @param   int     $timeout
     * @return  null
     */
    public function conectar($host, $usuario = null, $senha = null, $porta = 135, $timeout = 10)
    {
        try {
            /**
             * Testa conectividade com host alvo
             * 
             * @param string $host
             * @param string $porta
             * @param int    $errno   valor de sistema
             * @param string $errstr  mensagem de sistema
             * @param int    $timeout tempo máximo a esperar
             */
            if (!$socket = @fsockopen($host, $porta, $errno, $errstr, $timeout)) {
                // @see https://msdn.microsoft.com/en-us/library/windows/desktop/ms740668(v=vs.85).aspx
                $dic = [
                            10056 => "Já existe uma conexão socket aberta para o host <b>{$host}</b>!",
                            10057 => "Não foi possível conectar ao socket na chamada do host <b>{$host}</b>!",
                            10060 => "Time Out na chamada do host <b>{$host}</b>!",
                            10061 => "O host <b>{$host}</b> recusou a conexão!",
                        ];

                $mensagem = (array_key_exists($errno, $dic)) ? strtr($errno, $dic) : $errstr;

                throw new RuntimeException("Erro ({$errno}): {$mensagem}");
            }

            fclose($socket); // Fecha o socket aberto anteriormente

            $WbemLocator = new COM("WbemScripting.SWbemLocator");
            // @see https://msdn.microsoft.com/en-us/library/aa393720(v=vs.85).aspx
            $this->conexao = $WbemLocator->ConnectServer($host, 'root\cimv2', $usuario, $senha, 'MS_416');
            $this->conexao->Security_->ImpersonationLevel = 3;
        } catch (com_exception $e) {
            $this->mensagemErro = utf8_encode($e->getMessage());
        } catch (RuntimeException $e) {
            $this->mensagemErro = $e->getMessage();
        } catch (Exception $e) {
            $this->mensagemErro = $e->getMessage();
        }
    }

    /**
     * Executa a instrução remotamente
     * 
     * @param   string         $instrucao
     * @return  object|string  em caso de erro retorna uma string
     */
    public function executar($instrucao)
    {
        try {
            if (!$this->conexao) {
                throw new RuntimeException("Antes de executar uma instrução é necessário instanciar uma conexão!");
            }

            // @see http://php.net/manual/en/ref.com.php
            if (!$retorno = $this->conexao->ExecQuery($instrucao)) {
                throw new RuntimeException("O host remoto não retornou dados!");
            }

            return $retorno;
            
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }
    }
}