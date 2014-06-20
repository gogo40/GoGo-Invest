<?php
/*
(c) 2013, 2014, GoGo40 - Pericles Lopes Machado

Script PHP para automaticamente baixar o database de 12 em 12 horas e 
enviar email para a lista de usuários cadastrada
*/


define("SITE_PATH", "/var/www/mkindex");
define("BUFFER_SIZE", 10000000);
define("NUMBER_OUTPUT", 200);

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

