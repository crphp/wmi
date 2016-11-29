# crphp/wmi
Está é uma biblioteca que faz uso do **WQL (WMI Query Language)** para manipulação do **instrumento 
WMI** em máquinas Windows. Em outras palavras, essa biblioteca permite o gerenciamento remoto de 
máquinas Windows, dispensando a instalação de agentes e plugins nas máquinas clientes.

>Caso sua intenção seja somente **consultar** os recursos de uma máquina remota, recomendo fortemente 
que analise a possibilidade de utilizar [crphp/wmic](https://github.com/crphp/wmic) em razão da performance.
>
>**crphp/wmi** e **crphp/wmic** possuem os mesmos recursos de consulta, porém, somente **crphp/wmi** 
tem a capacidade de gerenciar recursos remotos, como parar serviços, matar processos etc.

Está biblioteca segue os padrões descritos na [PSR-2](http://www.php-fig.org/psr/psr-2/), logo, 
isso implica que a mesma está em conformidade com a [PSR-1](http://www.php-fig.org/psr/psr-1/).

As palavras-chave "DEVE", "NÃO DEVE", "REQUER", "DEVERIA", "NÃO DEVERIA", "PODERIA", "NÃO PODERIA", 
"RECOMENDÁVEL", "PODE", e "OPCIONAL" neste documento devem ser interpretadas como descritas no 
[RFC 2119](http://tools.ietf.org/html/rfc2119). Tradução livre [RFC 2119 pt-br](http://rfc.pt.webiwg.org/rfc2119).

1. [Referências](#referencia)
1. [Funcionalidades](#funcionalidades)
1. [Requisitos (recomendados)](#requisitos)
1. [Configurando o servidor](#configurando-o-servidor)
1. [Preparando a máquina cliente](#preparando-a-maquina-cliente)
1. [Baixando o pacote crphp/wmi para o servidor](#wmi)
1. [Exemplos de uso](#exemplos)
1. [Licença (MIT)](#licenca)

## 1 - <a id="referencias"></a>Referências
 - [PSR-1](http://www.php-fig.org/psr/psr-1/)
 - [PSR-2](http://www.php-fig.org/psr/psr-2/)
 - [RFC 2119](http://tools.ietf.org/html/rfc2119). Tradução livre [RFC 2119 pt-br](http://rfc.pt.webiwg.org/rfc2119)

## 2 - <a id="funcionalidades"></a>Funcionalidades
- [x] Consultar CPU
- [x] Consultar RAM
- [x] Consultar Disco Rígido
- [x] Consultar serviço
- [x] Listar Serviços
- [x] Stop / Start de serviço
- [x] Consultar processo
- [x] Listar processos
- [x] Alterar prioridade do processo
- [x] Matar / Finalizar processos
- [x] Transformação de timestamp Windows para data/hora
- [ ] Lançar processos
- [ ] Listar e matar sessões

## 3 - <a id="preparando-o-servidor"></a>Preparando o servidor
> :exclamation: Os requisitos sugeridos logo abaixo representam as versões utilizadas em nosso ambiente 
de desenvolvimento e produção, logo não garantimos que a solução aqui apresentada irá rodar integralmente 
caso as versões dos elementos abaixo sejam outras.

### 3.1 - <a id="requisitos"></a>Requisitos (recomendados)
Servidor
- REQUER Windows (desktop >= Windows 7 ou Windows Server >= 2003)
- REQUER Apache >= 2.4.10
- REQUER PHP >= 5.5.12

Cliente
- REQUER Windows (desktop >= Windows 7 ou Windows Server >= 2003)
- NÃO REQUER a instalação de nenhum componente

## 4 - <a id="configurando-o-servidor"></a>Configurando o servidor

No arquivo php.ini DEVE ser adicionada alinha:
```
extension=php_com_dotnet.dll
```
Você DEVE verificar se o arquivo php_com_dotnet.dll está presente no diretório php/ext

## 5 - <a id="preparando-a-maquina-cliente"></a>Preparando a máquina cliente
As configurações deste tópico DEVEM ser executadas em todas as máquinas alvos de gerenciamento remoto, 
ou seja, em todas as máquinas cliente.

### 5.1 - Liberando regra de firewall nos clientes
Caminho para as regras de firewall:
```
Painel de Controle > Ferramentas Administrativas > Firewall do Windows com Segurança Avançada
```

Para permitir as conexões WMI teremos que habilitar as **Regras de Entrada**:
```
Instrumentação de Gerenciamento do Windows (DCOM-In)
Instrumentação de Gerenciamento do Windows (WMI-In)
```

E as **Regras de Saída**:
```
Instrumentação de Gerenciamento do Windows (WMI-Saída)
```

Para não ter problema, é RECOMENDÁVEL que o usuário de conexão remota tenha privilégio de administrador 
na máquina de destino. Obviamente você PODE configurar o contexto de acesso caso tenha alguma familiridade 
com este assunto.

## 6 - <a id="wmi"></a>Baixando o pacote crphp/wmi para o servidor

Para a etapa abaixo estou pressupondo que você tenha o composer instalado e saiba utilizá-lo:
```
composer require crphp/wmi
```

Ou se preferir criar um projeto:
```
composer create-project --prefer-dist crphp/wmi nome_projeto
```

Caso ainda não tenha o composer instalado, obtenha este em: https://getcomposer.org/download/

## 7 - <a id="exemplos"></a>Exemplos de uso

**Consultar CPU**:
```php
use Crphp\Wmi\Sistema\Cpu;
use Crphp\Wmi\Conector\Wmi;

$wmi = new Wmi;
$wmi->conectar('ip_ou_hostname', 'usuario', 'senha');

if($wmi->status()) {
    
    $cpu = new Cpu($wmi);
    echo "<pre>";
    print_r($cpu->detalhes());
    echo "</pre>";
    
} else {
    echo $wmi->mensagemErro();
}
```

Todas as demais classes funcionam praticamente da mesma forma.

**Consultar Disco Rígido**
```php
use Crphp\Wmi\Conector\Wmi;
use Crphp\Wmi\Sistema\DiscoRigido;

$wmi = new Wmi;
$wmi->conectar('ip_ou_hostname', 'usuario', 'senha');

if($wmi->status())
{
    // Letra de unidade opcional
    $obj = new DiscoRigido($wmi, "C");
    echo "<pre>";
    print_r($obj->detalhes());
    echo "</pre>";
    
} else {
    echo $wmi->mensagemErro();
}
```

**Listar ou encerrar processo**
```php
use Crphp\Wmi\Conector\Wmi;
use Crphp\Wmi\Sistema\Processos;

$wmi = new Wmi;
$wmi->conectar('ip_ou_hostname', 'usuario', 'senha');

if($wmi->status()) {
    $obj = new Processos($wmi);
    
    echo "<pre>";
    print_r($obj->detalhes());
    //print_r($obj->killProcesso());
    echo "</pre>";
    
} else {
    echo $wmi->mensagemErro();
}
```

> Você DEVE sempre instânciar o conector Wmi e a classe referente ao elemento que deseja manipular

**Também é possível executar suas próprias consultas customizadas**
```php
use Crphp\Wmi\Conector\Wmi;

$wmi = new Wmi;
$wmi->conectar('ip_ou_hostname', 'usuario', 'senha');

if($wmi->status()) {
    $memoria = $wmi->executar("select AvailableBytes from Win32_PerfRawData_PerfOS_Memory");
    // Será retornado um objeto em caso de sucesso ou uma string em caso de erro
} else {
    echo $wmi->mensagemErro();
}
```

## 8 - <a id="licenca">Licença (MIT)
Para maiores informações, leia o arquivo de licença disponibilizado junto desta biblioteca.