#!/usr/bin/php
<?php
/*
(c) 2013, 2014, GoGo40 - Pericles Lopes Machado

Script PHP para automaticamente baixar o database de 12 em 12 horas e 
enviar email para a lista de usuários cadastrada
*/

//Parametros fundamentalistas analisados

/*
PESOS
	"Empresa"=>"s",
	"Papel"=>"s",
	"Setor"=>"s",
    "Subsetor"=>"s",

	"Data lt cot"=>"d",
	"lt balano processado"=>"d",
	
	
    Controle de oscilacao (valor normalizado com relacao aos outros) = 
    (Cotacao - min) / (max - min) * 
    (1 +  (2 * MEDIA(OSCILACAO ANO != 0) + 3 * MEDIA(DIA, mes, 30 DIAS)) / 500)  PESO -1
    
	"Cotao"=>"n",
    
    "2014"=>"n",
    "2012"=>"n",
    "2013"=>"n",
    "2011"=>"n",
    "2010"=>"n",
    "2009"=>"n",
	
	"Min 52 sem"=>"n", 
	"Max 52 sem"=>"n", 
	
	"Dia"=>"n", 
	"Ms"=>"n",
	"30 dias"=>"n",
	
-----------------------------------------------------------------------------------------
	"Nro. Aes"=>"n",
    "Valor de mercado"=>"n",
-----------------------------------------------------------------------------------------    

	"P/Ativos"=>"n",  -1
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

$params = array(
	"Empresa"=>"s",
	"Papel"=>"s",
	"Setor"=>"s",
    "Subsetor"=>"s",
	"Data lt cot"=>"d",
	"lt balano processado"=>"d",
	"Cotao"=>"n",
    "Nro. Aes"=>"n",
    "Valor de mercado"=>"n",
    "2014"=>"n",
    "2012"=>"n",
    "2013"=>"n",
    "2011"=>"n",
    "2010"=>"n",
    "2009"=>"n",
	"Min 52 sem"=>"n", 
	"Max 52 sem"=>"n", 
	"Dia"=>"n", 
	"Ms"=>"n",
	"30 dias"=>"n",
	"P/Ativos"=>"n",
	"P/Cap. Giro"=>"n",
    "P/Ativ Circ Liq"=>"n",
    "EBIT / Ativo"=>"n",
    "P/L"=>"n",
    "P/EBIT"=>"n",
    "ROIC"=>"n",
    "Div. Yield"=>"n",
    "EV / EBIT"=>"n",
    "VPA"=>"n",
    "P/VP"=>"n",
    "ROE"=>"n",
    "Liquidez Corr"=>"n",
    "Div Br/ Patrim"=>"n",
    "LPA"=>"n",
    "Marg. EBIT"=>"n",
    "Cres. Rec 5a"=>"n",
    "Giro Ativos"=>"n",
    "PSR"=>"n"
    );

$weights = array (
	"P/Ativos"=>-1.0,
	"P/Cap. Giro"=>-2.0,
    "P/Ativ Circ Liq"=>-3.0,
    "P/L"=>-2.0,
    "P/EBIT"=>-1.0,
	"EV / EBIT"=>-1.0,
    "Div Br/ Patrim"=>-6.0,
    "PSR"=>-2.0,
	"P/VP"=>-1.0,
    
    "ROIC"=>1.0,
    "ROE"=>2.0,
    "Div. Yield"=>3.0,
    "EBIT / Ativo"=>2.0,
    
    
    "VPA"=>1.0,
    "Liquidez Corr"=>4.0,
    "LPA"=>2.0,
    "Marg. EBIT"=>1.0,
    "Cres. Rec 5a"=>2.0,
    "Giro Ativos"=>2.0
	);


$alias  = array(
	"Empresa",
	"Papel",
	"Setor",
    "Subsetor",
	"Cotacao",
    "Min 52 sem", 
	"Max 52 sem",
    "Nro. Acoes",
    "Valor de mercado",
    "Data lt cot",
	"Ult balanco processado",
	"Osc. 2014",
    "Osc. 2012",
    "Osc. 2013",
    "Osc. 2011",
    "Osc. 2010",
    "Osc. 2009",
	"Osc. Dia", 
	"Osc. Mes",
	"Osc. 30 dias",
	"P/Ativos",
	"P/Cap. Giro",
    "P/Ativ Circ Liq",
    "EBIT / Ativo",
    "P/L",
    "P/EBIT",
    "ROIC",
    "Div. Yield",
    "EV / EBIT",
    "VPA",
    "P/VP",
    "ROE",
    "Liquidez Corr",
    "Div Br/ Patrim",
    "LPA",
    "Marg. EBIT",
    "Cres. Rec 5a",
    "Giro Ativos",
    "PSR"
	);

$cmd = 'python '.__DIR__.'/get_fundamentus.py > '.__DIR__.'/download_log_'.date("Y_m_d").'.log';

$BUFFER_SIZE = 1000000000; // 1 GB

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

	echo "Acao $acao:\n";
	print_r($dado);
	echo "\n";

	$ok = true;
	
	foreach ($params as $p => $type) {
		if (array_key_exists($p, $dado)) {
			echo "$p encontrado!\n";
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
					echo "Parametro $p\n";
					
					list ($dia, $mes, $ano) = split ('[/.-]', $dado[$p]);

					if ($YEAR  - intval($ano) > 1) {
						$ok = false;
					}
				break;
			}

		} else {
			echo "$p nao encontrado!\n";
			$ok = false;
			break;
		}

		if (!$ok) {
			break;
		}
	}

	if ($ok) {
		$info_valid[$n_v] = $dado;
		++$n_v;
	}

	print_r($dado);
}


echo "INFO VALID: \n";
echo "SIZE: $n_v\n";

print_r($info_valid);



fclose($log);

fclose($fdata);

