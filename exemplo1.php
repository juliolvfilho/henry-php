<?php

/**
 * Exemplo 1: Ler cartões e liberar
 */

require_once 'catraca_lib.php';

// recebe parâmetros por linha de comando
$ip = empty($argv[1])? '192.168.0.117' : $argv[1];
$port = empty($argv[2])? 3000 : intval($argv[2]);

// instancia um objeto catraca
echo "iniciando catraca em $ip:$port\n\n";
$catraca = new Catraca($ip, $port);

// inicia o serviço de leitura de cartão/liberação
while (true) {
	echo "aguardando leitura de cartao...\n";
	$resposta = $catraca->escutar();
	echo "uma resposta foi recebida da catraca\n";

	if( is_string($resposta) ){
		echo "ERRO: $resposta";
		die;
	}
	elseif( is_array($resposta) ){
		if( $resposta['err_or_version'] != '00' ){
			echo "recebido o código de erro [".$resposta['err_or_version']."]\n";
			echo "data: ".$resposta['data']."\n";
		}
		else{
			//$params = explode(']', $resposta['data']); // tratamento da resposta recebida
			//echo "recebido o cartao numero [".$params[1]."] em [".$params[2]."]\n";
			echo "data: ".$resposta['data']."\n";
		}
	}

	echo "enviando mensagem de liberacao\n";
	//$resposta = $catraca->impedir_entrada( $resposta['index'], "SEM SALDO" );
	$resposta = $catraca->permitir_entrada($resposta['index']);

	if( empty($resposta) ){
		echo "mensagem de liberacao enviada\n";
	}
	elseif( is_string($resposta) ){
		echo "ERRO: $resposta";
	}

	// recebendo a resposta da catraca após a liberação
	$resposta = $catraca->escutar();
	if( is_string($resposta) ){
		echo "ERRO: $resposta";
		die;
	}
	elseif( is_array($resposta) ){
		if( $resposta['err_or_version'] != '00' ){
			echo "recebido o código de erro [".$resposta['err_or_version']."]\n";
			echo "data: ".$resposta['data']."\n";
		}
		else{
			$params = explode(']', $resposta['data']);
			if( $params[0] == '1' ){ // << a catraca deve retornar 1 para sucesso
				//echo "catraca liberada em [".$params[2]."]\n";
				echo "data: ".$resposta['data']."\n";
			}
			else{
				echo "a catraca não autorizou a liberação. código retornado [".$params[0]."]\n";
				echo "data: ".$resposta['data']."\n";
			}
		}
	}
}
