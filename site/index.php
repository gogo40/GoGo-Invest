<?php
/*
(c) 2013, 2014, GoGo40 - Pericles Lopes Machado

Script PHP para automaticamente baixar o database de 12 em 12 horas e 
enviar email para a lista de usuários cadastrada
*/

require_once "site_config.php";

echo "Ola<p>";
echo __DIR__."<p>";
echo SITE_PATH."<p>";

$files = glob(SITE_PATH."/fundamentus_mk_*.json");

print_r($files);
