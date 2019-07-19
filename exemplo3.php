<?php

/**
 * Exemplo 3: Libera/Bloqueia a Catraca
 */

require_once 'catraca_lib.php';

// recebe parâmetros por linha de comando
$ip = empty($argv[1])? '192.168.0.117' : $argv[1];
$port = empty($argv[2])? 3000 : intval($argv[2]);

// instancia um objeto catraca
echo "iniciando catraca em $ip:$port\n\n";
$catraca = new Catraca($ip, $port);


$resposta = $catraca->get_config("EXP_TIPO_LIBERACAO");
if( $resposta == 'A' ){
	echo "a catraca encontrava-se no estado 'A': permite que qualquer pessoa passe sem que haja validação\n";
	echo $catraca->set_config("EXP_TIPO_LIBERACAO","B");
	echo "mudou para o estado 'B'\n"; 
}
elseif( $resposta == 'B' ){
	echo "a catraca encontrava-se no estado 'B': os braços não permitem a passagem sem que haja validação\n";
	echo $catraca->set_config("EXP_TIPO_LIBERACAO","A");
	echo "mudou para o estado 'A'\n"; 
}
else echo $resposta;
