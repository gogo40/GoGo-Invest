<?php
/*
(c) 2013, 2014, GoGo40 - Pericles Lopes Machado

Script PHP para automaticamente baixar o database de 12 em 12 horas e 
enviar email para a lista de usuários cadastrada
*/

//Parametros fundamentalistas analisados

/*
	[P/Ativos] => -
    [P/Cap. Giro] => -
    [P/Ativ Circ Liq] => -
    [EBIT / Ativo] => 0
    [P/L] => 7.27
    [P/EBIT] => -
    [ROIC] => -
    [Min 52 sem] => 10.8
    [Div. Yield] => 4.3
    [Subsetor] => Bancos
    [EV / EBIT] => -
    [Cotao] => 13.5
    [VPA] => 13.15
    [Data lt cot] => 09/05/2014
    [2014] => 10.11
    [2011] => -10.49
    [2010] => 29.1
    [2013] => -5.4
    [P/VP] => 1.03
    [ROE] => 14.1
    [Nro. Aes] => 151043000
    [lt balano processado] => 31/03/2014
    [Dia] => -0.74
    [Liquidez Corr] => -
    [Ms] => 5.06
    [Div Br/ Patrim] => -
    [Setor] => Financeiros
    [LPA] => 1.86
    [Marg. EBIT] => -
    [Empresa] => N2 ABC Brasil PN N2
    [2009] => 133.33
    [Cres. Rec 5a] => 1.8
    [Max 52 sem] => 14.68
    [Papel] => ABCB4
    [Giro Ativos] => -
    [PSR] => -
    [2012] => 19.56
    [Valor de mercado] => 2039080000
    [30 dias] => 4.25
*/

$params = array(
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
    "Empresa"=>"n",
    "Cres. Rec 5a"=>"n",
    "Giro Ativos"=>"n",
    "PSR"=>"n"
    );

$alias  = array(
	"Papel",
	"Setor",
    "Subsetor",
	"cotacao",
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
    "Empresa",
    "Cres. Rec 5a",
    "Giro Ativos",
    "PSR"
	);

$cmd = 'python get_fundamentus.py > download_log_'.date("Y_m_d").'.log';

system($cmd);


$log = fopen("fundamentus_".date("Y_m_d").".log", "a");
if (!$log) {
	die ("Falha ao abrir arquivo de log");
}

fprintf($log, "Carregado script...\n");

$fdata = fopen("fundamentus.json", "r");
if (!$fdata) {
	die ("Falha ao abrir arquivo fundamentus.json");
}

$info = "";
$info = fgets($fdata, 1999999999);

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

