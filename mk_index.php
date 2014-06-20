#!/usr/bin/php
<?php
/*
(c) 2013, 2014, GoGo40 - Pericles Lopes Machado

Script PHP para automaticamente baixar o database de 12 em 12 horas e 
enviar email para a lista de usuários cadastrada
*/

require_once (__DIR__.'/mk_config.php');

$weights_acc = 0;
foreach ($weights as $id=>$value) {
	$weights_acc += abs($value);
}



$cmd = 'python '.__DIR__.'/get_fundamentus.py > '.__DIR__.'/download_log_'.date("Y_m_d").'.log';

/*INVOCA SCRIPT PYTHON PARA BAIXAR BASE DE DADOS*/
system($cmd);

$log = fopen(__DIR__."/fundamentus_".date("Y_m_d").".log", "a");
if (!$log) {
	die ("Falha ao abrir arquivo de log");
}

fprintf($log, "Carregado script...\n");

$fdata = fopen(__DIR__."/fundamentus.json", "r");
if (!$fdata) {
	die ("Falha ao abrir arquivo fundamentus.json");
}

$info = "";
$info = fgets($fdata, $BUFFER_SIZE);

//Carrega estrutura com os dados
$info = json_decode($info, true);

$YEAR = intval(date('Y'));
$MONTH = intval(date('M'));
$DAY = intval(date('D'));

$info_valid = array();
$n_v = 0;
foreach ($info as $acao=>$dado) {
	$ok = true;
	
	foreach ($params as $p => $type) {
		if (array_key_exists($p, $dado)) {
			switch ($type) {
				case "n":
					if ($dado[$p] === "-") {
						$dado[$p] = 0.0;
					} else {
						$dado[$p] = floatval($dado[$p]);
					}
				break;

				case "s":
				break;

				case "d":
					if ($dado[$p] !== '-') {
						list ($dia, $mes, $ano) = split ('[/.-]', $dado[$p]);

						if ($YEAR  - intval($ano) > 1) {
							$ok = false;
						}
					}
				break;
			}

		} else {
			$ok = false;
			break;
		}

		if (!$ok) {
			break;
		}
	}

	if ($ok) {
		if (abs($dado["Dia"]) > 1e-4 ) {
			$info_valid[$n_v] = $dado;
			++$n_v;
		}
	}
}


fprintf($log,"INFO VALID: \n");
fprintf($log,"SIZE: $n_v\n");


/*
Calcula indice MK de cada papel

*/

//Calculo valor esperado de cada papel e min e max de cada parametro essencial

/*	"P/Ativos"=>"n",  -1
	"P/Cap. Giro"=>"n",  -2
    "P/Ativ Circ Liq"=>"n", -3
    "P/L"=>"n",  -2
    "P/EBIT"=>"n",  -1
	"EV / EBIT"=>"n", PESO -1
    "Div Br/ Patrim"=>"n",   PESO -5
    "PSR"=>"n", -2
	"P/VP"=>"n",   PESO -1
    
    "ROIC"=>"n",   PESO 1
    "ROE"=>"n",    PESO 2
    "Div. Yield"=>"n",  PESO 3
    "EBIT / Ativo"=>"n", PESO 2
    
    
    "VPA"=>"n",   PESO 1
    "Liquidez Corr"=>"n",  PESO 2
    "LPA"=>"n",    PESO 2
    "Marg. EBIT"=>"n",  PESO 1
    "Cres. Rec 5a"=>"n",  PESO 2
    "Giro Ativos"=>"n",  PESO 2
*/

$mins = array();
$maxs = array();

foreach ($info_valid as $id=>$dado) {
/*
PESOS
	Controle de oscilacao (valor normalizado com relacao aos outros) = 
    (max - Cotacao) / (max - min) * 
    (1 +  (2 * MEDIA(OSCILACAO ANO != 0) + 3 * MEDIA(DIA, mes, 30 DIAS)) / 500)  PESO 1
    
	"Cotao"=>"n",
    
    "Min 52 sem"=>"n", 
	"Max 52 sem"=>"n", 
	
	"Dia"=>"n", 
	"Ms"=>"n",
	"30 dias"=>"n",	 
*/
	$min_valor = $dado["Min 52 sem"];
	$max_valor = $dado["Max 52 sem"];

	$osc_esp = (2 * $dado["Dia"] + $dado["Ms"] + $dado["30 dias"]) / 4.0;

	if ($max_valor === $min_valor) {
		$valor_esperado = 1;
	} else {
		$valor_esperado = 
		($max_valor - $dado["Cotao"]) / ($max_valor - $min_valor) * 
		(1 + $osc_esp / 100.0);
	}
	$info_valid[$id]["Cotacao Esperada"] = $valor_esperado;

}

foreach ($info_valid as $id=>$dado) {
	foreach ($weights as $w_id => $w_value) {
		if (!array_key_exists($w_id, $mins)) {
			$mins[$w_id] = $dado[$w_id];
		} elseif ($mins[$w_id] > $dado[$w_id]) {
			$mins[$w_id] = $dado[$w_id];
		}

		if (!array_key_exists($w_id, $maxs)) {
			$maxs[$w_id] = $dado[$w_id];
		} elseif ($maxs[$w_id] < $dado[$w_id]) {
			$maxs[$w_id] = $dado[$w_id];
		}
	}
}

fprintf($log,"Dados normalizados e calculo do indice mk...\n");
//Normalize parametros
$info_valid_norm = array();
$min_mk = 0;
$max_mk = 0;
foreach ($info_valid as $id=>$dado) {
	$dado_norm = $dado;
	$acc = 0;
	foreach ($weights as $w_id => $w_value) {
		$dado_norm[$w_id] = 
		($dado[$w_id] - $mins[$w_id]) / ($maxs[$w_id] - $mins[$w_id]) 
		* $w_value;

		$acc += $dado_norm[$w_id];
	}
	$info_valid_norm[$id] = $dado_norm;
	$indice_mk = $acc / $weights_acc;
	$info_valid[$id]['Indice MK'] = $indice_mk;
	$info_valid_norm[$id]['Indice MK'] = $indice_mk; 

	if ($indice_mk < $min_mk) {
		$min_mk = $indice_mk;
	}

	if ($indice_mk > $max_mk) {
		$max_mk = $indice_mk;
	}
}

foreach ($info_valid_norm as $id=>$dado) {
	$indice_mk = $dado['Indice MK'];
	$info_valid[$id]['Indice MK'] = $indice_mk;
	$info_valid_norm[$id]['Indice MK'] = 1000 * ($indice_mk - $min_mk) / ($max_mk - $min_mk);
}

//print_r($info_valid_norm);

//SALVA DADOS BRUTOS VALIDOS FILTRADOS e COM MK

$raw_file = fopen(__DIR__."/fundamentus_raw.json", "w");

fprintf($raw_file, json_encode($info_valid));

fclose($raw_file);

$mk_file = fopen(__DIR__."/fundamentus_mk.json", "w");

fprintf($mk_file, json_encode($info_valid_norm));

fclose($mk_file);


fclose($log);

fclose($fdata);


require_once (__DIR__."/mk_mail_send.php");


send_mk_data(__DIR__."/fundamentus_mk.json", $params_to_send);
