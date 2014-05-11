#!/usr/bin/php
<?php
/*
(c) 2013, 2014, GoGo40 - Pericles Lopes Machado

Script PHP para enviar email com os relatorios baixados
*/
require_once(__DIR__.'/mk_config.php');
require_once(__DIR__.'/mk_mail_api.php');

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


function send_mk_data($mk_file, $params)
{

	global $BUFFER_SIZE, $mail_list, $system_name, $N_rows;


	$fdata = fopen($mk_file, "r");
	if (!$fdata) {
		die ("Falha ao abrir arquivo " + $mk_file);
	}

	$info_mk =  json_decode(fgets($fdata, $BUFFER_SIZE), true);
	
	aasort($info_mk,"Indice MK");

	fclose($fdata);

	foreach ($mail_list as $nome=>$email) {
		$destinatario = "$nome";
		$assunto = "[$system_name BOT] Indice MK (".date('d/m/Y').")";
		
		$mensagem = 
		    "<p>Olá <b>$nome</b>, tudo bem?</p>" .
			"<p>O índice MK acabou de ser atualizado.</p>\n";


		$mensagem .= "<br><b>TOP 100 </b></br>";
		$mensagem .= "<table>";
	
		$mensagem .= "<tr>";
		$mensagem .= " <th bgcolor=\"#C8C8C8\"> # </th> ";
		foreach ($params as $p=>$type) {
			$mensagem .= " <th bgcolor=\"#C8C8C8\"> $p </th> ";

		}
		$mensagem .= "</tr>\n";

		$nr = 0;
		foreach ($info_mk as $id=>$data) {
			$mensagem .= "<tr> ";

			if ($nr % 2) {
				$mensagem .= " <td bgcolor=\"00FFFF\"> ".($nr + 1)." </td> ";
			} else {
				$mensagem .= " <td> ".($nr + 1)." </td> ";
			}

			foreach ($params as $p=>$type) {
				if ($type === "n") {
					$data[$p] = sprintf("%.2f",$data[$p]);
				}

				if ($nr % 2) {
					$mensagem .= " <td bgcolor=\"00FFFF\"> ".$data[$p]." </td> ";
				} else {
					$mensagem .= " <td> ".$data[$p]." </td> ";
				}
			}
			$mensagem .= " </tr>\n";
			if ($nr >= $N_rows) {
				break;
			}
			++$nr;

		}

		$mensagem .= "</table>";


		$mensagem .=
		    "<p>Obrigado e espero que os dados te ajudem!</p>" .
			"<p>$system_name</p>\n";

		gogo40_mail($destinatario, $email, $assunto, $mensagem);
	}
}


