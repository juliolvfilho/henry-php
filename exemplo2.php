<?php

/**
 * Exemplo 2: Liberar uma vez a catraca incondicionalmente
 */

require_once 'catraca_lib.php';

// recebe parâmetros por linha de comando
$ip = empty($argv[1])? '192.168.0.117' : $argv[1];
$port = empty($argv[2])? 3000 : intval($argv[2]);

// instancia um objeto catraca
echo "iniciando catraca em $ip:$port\n\n";
$catraca = new Catraca($ip, $port);

// aguém aperta o botão ABRIR CATRACA, por exemplo
echo "enviando a mensagem de abertura da catraca...\n";
$resposta = $catraca->liberar_entrada();
if( is_string($resposta) ){
	echo "ERRO: $resposta";
	die;
}
elseif(empty($resposta) ){
	echo "mensagem de abertura enviada\n";
}

// recebendo a resposta da catraca após a abertura
$resposta = $catraca->escutar();
if( is_string($resposta) ){
	echo "ERRO: $resposta";
	die;
}
elseif( is_array($resposta) ){
	if( $resposta['err_or_version'] != '00' ){
		echo "recebido o código de erro [".$resposta['err_or_version']."]\n";
	}
	else{
		$params = explode(']', $resposta['data']);
		if( $params[0] == '4' ){ // << a catraca deve retornar 4 para sucesso
			echo "catraca aberta em [".$params[2]."]\n";
		}
		else{
			echo "a catraca não autorizou a abertura. código retornado [".$params[0]."]\n";
		}
	}
}
