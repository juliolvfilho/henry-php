###Funções PHP para comunicação com catracas e relógios de acesso Henry&copy;
Testada com as APIs de comunicação dos modelos **Prime Acesso 8X** e **Prime Acesso SF**.

#####Considerações Gerais
1. Instancie um objeto dessa classe: definir o IP e a Porta
2. Execute as operações (funções) disponíveis
3. Há somente duas classes de operações disponíveis: Escutar ou Enviar Comando (mensagem).
4. Durante o processo de Escuta a função 'escutar' permanece bloqueada até receber um evento e então retorna.
5. As funções de Enviar Comando estão divididas em duas categorias: as que precisam ser executadas após o processo de escuta e as que precisam executar antes do processo de escuta.
6. Uma função da primeira categoria requer o parâmetro INDEX que é retornado numa resposta da função 'escutar'.
7. Nas funções do segundo tipo você deve invocar 'escutar' após enviar o comando para que ele seja efetivado.

#####Retorno das Funções
As funções dessa classe geralmente apresentam os seguintes retornos:
- Retornam STRING em caso de erro, contendo a mensagem de erro;
- Diferente de STRING em caso de sucesso, geralmente algum valor vazio (ex.: false) ou objeto/array no caso da função retornar dados.

#####Funções disponíveis

| Nome | Descrição |
| - | - |
| `conectar` | Conecta na catraca. Essa função pode ser chamada explicitamente antes de começar a utilizar a catraca, mas é opcional. As funções dessa classe automaticamente chamam essa função caso o socket não esteja criado. <br/> Parâmetros: nenhum. <br/> Retorno: FALSE em caso de sucesso ou STRING contendo a mensagem de erro em caso de falha. |
| `escutar` | Captura uma mensagem enviada pela catraca. Essa função é bloqueante, isso significa que ela só irá retornar após a catraca enviar uma mensagem - fica aguardando indefinidamente enquanto isso. <br/> Parâmetros: nenhum. <br/> Retorno: ARRAY com resultado em caso de sucesso ou STRING contendo a mensagem de erro em caso de falha. Veja o detalhamento do retorno mais abaixo. |
| `permitir_entrada` | Envia a mensagem de liberação da catraca (após escutar). <br/> Parâmetros: <br/> **index**: numero da thread (índice da mensagem) - string numérica de tamanho 2 <br/> **mensagem**: string contendo a mensagem para o display da catraca, por padrão é "ENTRADA LIBERADA" <br/> Retorno: FALSE em caso de sucesso ou STRING contendo a mensagem de erro em caso de falha. |
| `impedir_entrada` | Envia a mensagem de bloqueio da catraca (após escutar). <br/> Parâmetros: <br/> **index**: numero da thread (índice da mensagem) - string numérica de tamanho 2 <br/> **mensagem**: string contendo a mensagem para o display da catraca." <br/> Retorno: FALSE em caso de sucesso ou STRING contendo a mensagem de erro em caso de falha. |
| `liberar_entrada` | Envia a mensagem de abertura da catraca (antes de escutar). <br/> Parâmetros: <br/> **index**: numero da thread (índice da mensagem) - string numérica de tamanho 2 <br/> **mensagem**: string contendo a mensagem para o display da catraca, por padrão é "SEJA BEM VINDO" <br/> Retorno: FALSE em caso de sucesso ou STRING contendo a mensagem de erro em caso de falha. |
| `get_config` | Retorna o valor gravado de uma determinada configuração. <br/> Parâmetros: <br/> **parametro**: string contendo o nome da configuração - consulte a sessão 'Configurações' do manual do seu chip Henry&copy; para obter as configurações disponíveis. |
| `set_config` | Grava o valor de uma determinada configuração. <br/> Parâmetros: <br/> **parametro**: string contendo o nome da configuração - consulte a sessão 'Configurações' do manual do seu chip Henry&copy; para obter as configurações disponíveis. <br/> **valor**: string com o valor da configuração. |
| `set_datetime` | Grava o valor do dia/hora atual no relógio da catraca. <br/> Parâmetros: <br/> **dia_hora**: string no formato dd/mm/YYYY H&#58;i&#58;s |

#####Exemplo

```php
// sistema de garagem solicitando a abertura de uma cancela para passagem de veículo - chip Henry Argos
require_once 'catraca_lib.php';
$catraca = new Catraca('192.168.1.63', 3000);
$resposta = $catraca->liberar_entrada();
if( is_string($resposta) ){
    echo "ERRO: $resposta";
    die;
}
elseif(empty($resposta) ){
    echo "mensagem de abertura enviada com sucesso";
}
```

```php
// catraca liberando ou impedindo a passagem de pedestres após a identificação através da leitura de um cartão rfid - chipe Henry Prime SF
require_once 'catraca_lib.php';
$catraca = new Catraca('192.168.1.63', 3000);
while (true) {
    $resposta = $catraca->escutar();
    if( is_string($resposta) ){
        echo "ERRO: $resposta";
        die;
    }
    elseif( is_array($resposta) ){
        // obtemos o ID do usuário - número do cartão rfid
        if( $resposta['err_or_version'] != '00' ){
            echo "recebido o código de erro [".$resposta['err_or_version']."]";
        }
        else{
            $params = explode(']', $resposta['data']);
            $usuario_id = $params[1];
            // checa sua regra de negócio
            if ( usuario_pode_passar($usuario_id) ) {
                $resposta2 = $catraca->permitir_entrada($resposta['index']);
            }
            else {
                $resposta2 = $catraca->impedir_entrada( $resposta['index'], "BLOQUEADO" );
            }
            // verifica a resposta do comando de permitir/impedir entrada
            if( empty($resposta2) ){
                echo "mensagem de liberacao enviada\n";
            }
            elseif( is_string($resposta2) ){
                echo "ERRO: $resposta2";
            }
            // recebendo a resposta da catraca após a liberação
            $resposta3 = $catraca->escutar();
            if( is_string($resposta3) ){
                echo "ERRO: $resposta3";
                die;
            }
            elseif( is_array($resposta3) ){
                if( $resposta3['err_or_version'] != '00' ){
                    echo "recebido o código de erro [".$resposta3['err_or_version']."]\n";
                    echo "data: ".$resposta3['data']."\n";
                }
                else{
                    $params3 = explode(']', $resposta3['data']);
                    if( $params3[0] == '1' ) { // a catraca deve retornar 1 para sucesso
                        echo "catraca liberada";
                    }
                    else{
                        echo "a catraca não autorizou a liberação. código retornado [".$params3[0]."]";
                    }
                }
            }
        }
    }
}
```
