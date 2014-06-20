<?php
/*
(c) 2013, 2014, GoGo40 - Pericles Lopes Machado

Script PHP para automaticamente baixar o database de 12 em 12 horas e 
enviar email para a lista de usuários cadastrada
*/


define("SITE_PATH", "/var/www/mkindex");
define("BUFFER_SIZE", 10000000);
define("NUMBER_OUTPUT", 99);
define("NUMBER_PAGES", 10);

$params_to_send = array(
	"Papel"=>"s",
	"Empresa"=>"s",
	"Cotao"=>"s",
	"Subsetor"=>"s",
	"Indice MK"=>"n"
);

$params_name = array(
	"Papel"=>"Papel",
	"Empresa"=>"Empresa",
	"Cotao"=>"Cotação",
	"Subsetor"=>"Subsetor",
	"Indice MK"=>"Índice MK"
);

$papeis_bovespa = array(
"ABEV3" => 1,
"AEDU3" => 1,
"ALLL3" => 1,
"BBAS3" => 1,
"BBDC3" => 1,
"BBDC4" => 1,
"BBSE3" => 1,
"BISA3" => 1,
"BRAP4" => 1,
"BRFS3" => 1,
"BRKM5" => 1,
"BRML3" => 1,
"BRPR3" => 1,
"BVMF3" => 1,
"CCRO3" => 1,
"CESP6" => 1,
"CIEL3" => 1,
"CMIG4" => 1,
"CPFE3" => 1,
"CPLE6" => 1,
"CRUZ3" => 1,
"CSAN3" => 1,
"CSNA3" => 1,
"CTIP3" => 1,
"CYRE3" => 1,
"DTEX3" => 1,
"ECOR3" => 1,
"ELET3" => 1,
"ELET6" => 1,
"ELPL4" => 1,
"EMBR3" => 1,
"ENBR3" => 1,
"ESTC3" => 1,
"EVEN3" => 1,
"FIBR3" => 1,
"GFSA3" => 1,
"GGBR4" => 1,
"GOAU4" => 1,
"GOLL4" => 1,
"HGTX3" => 1,
"HYPE3" => 1,
"ITSA4" => 1,
"ITUB4" => 1,
"JBSS3" => 1,
"KLBN11" => 1,
"KROT3" => 1,
"LAME4" => 1,
"LIGT3" => 1,
"LREN3" => 1,
"MMXM3" => 1,
"MRFG3" => 1,
"MRVE3" => 1,
"NATU3" => 1,
"OIBR4" => 1,
"PCAR4" => 1,
"PDGR3" => 1,
"PETR3" => 1,
"PETR4" => 1,
"QUAL3" => 1,
"RENT3" => 1,
"RSID3" => 1,
"SANB11" => 1,
"SBSP3" => 1,
"SUZB5" => 1,
"TBLE3" => 1,
"TIMP3" => 1,
"UGPA3" => 1,
"USIM5" => 1,
"VALE3" => 1,
"VALE5" => 1,
"VIVT4" => 1
);

function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    arsort($sorter);

    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

