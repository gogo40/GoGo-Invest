<?php
/*
(c) 2013, 2014, GoGo40 - Pericles Lopes Machado

Script PHP para automaticamente baixar o database de 12 em 12 horas e 
enviar email para a lista de usuários cadastrada
*/

require_once "site_config.php";



$database = glob("fundamentus_mk_*.json");
$size = count($database);
$step = intval($size / NUMBER_PAGES);

if (array_key_exists("f", $_GET)) {
	$file = $_GET["f"];
} else {
	$file = $database[$size - 1];
}

?>



<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MK Index</title>

    <!-- Core CSS - Include with every page -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">

    <!-- Page-Level Plugin CSS - Dashboard -->
    <link href="css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">
    <link href="css/plugins/timeline/timeline.css" rel="stylesheet">

    <!-- SB Admin CSS - Include with every page -->
    <link href="css/sb-admin.css" rel="stylesheet">


    <!-- Page-Level Plugin CSS - Tables -->
    <link href="css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">

</head>

<body>

    <div id="wrapper">

        <nav class="navbar navbar-default navbar-fixed-top  bovespa-header" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html">MK Index</a>
            </div>
            <!-- /.navbar-top-links -->

           
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"> RANKING IBOVESPA  </h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

             <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="btn-group">
				    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
					<?php 
						list($year, $month, $day, $hour) = sscanf($file, "fundamentus_mk_%4d%2d%2d%4d.json");
						echo " <p> Data:  ".$day.' / '. $month.' / '.$year. "  </p>"; 
					?> 
					<span class="caret"></span>
				    </button>
				    <ul class="dropdown-menu pull-right" role="menu">
					<?php
						//foreach ($database as $key => $value) 
						for ($i = $size - 1; $i > -1; $i -= $step){
							$value = $database[$i];
							//fundamentus_mk_*.json
							list($year, $month, $day, $hour) = sscanf($value, "fundamentus_mk_%4d%2d%2d%4d.json");
							echo '<li><a href="index.php?f='.$value.'">'.$day.' / '. $month.' / '.$year.' </a></li>';
						}
					?>
				    </ul>
			     </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
<?php

$fdata = fopen($file, "r");
if (!$fdata) {
	die ("Falha ao abrir arquivo " + $file);
}

$info_mk =  json_decode(fgets($fdata, BUFFER_SIZE), true);

aasort($info_mk,"Indice MK");

fclose($fdata);
echo  "<th> # </th> ";
foreach ($params_to_send as $key => $value) {
	$v = $params_name[$key];
	echo "<th>$v</th>";
}
?>
                         
                                        </tr>
                                    </thead>
                                    <tbody>

<?php

$nr = 0;
foreach ($info_mk as $id=>$data) {
	$papel = $data["Papel"];
	if (array_key_exists($papel, $papeis_bovespa)) {
		echo "<tr class=\"gradeA\"> ";

		echo "<td> ".($nr + 1)." </td> ";

		foreach ($params_to_send as $p => $type) {
			if ($type === "n") {
				$data[$p] = sprintf("%.2f",$data[$p]);
			}

			echo " <td> ".$data[$p]." </td> ";
		}

		echo "\n </tr>";

		if ($nr >= NUMBER_OUTPUT) { break; }
		++$nr;
	}

}
?>
                                 
                                       
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
           <div class="panel panel-warning">
                <div class="panel-heading">
                    Aviso
                </div>
                <div class="panel-body">
                    <p>
O índice MK é experimental e baseado em heurísticas. Por isso, os autores se isentam de qualquer responsabilidade pelo uso das informações apresentadas pelo site em tomadas de decisão. Os balanços e outras informações financeiras das empresas foram extraídas do site <a href="http://www.fundamentus.com.br">Fundamentus</a>. 
                    </p>

                </div>
                <div class="panel-footer">
                    (c) 2014, Péricles Lopes Machado &lt;pericles.raskolnikoff [at] gmail.com&gt; e Pablo Koury &lt;pablo.koury [at] ufrgs.br&gt;.
                </div>
            </div>
	    <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- Core Scripts - Include with every page -->
    <script src="js/jquery-1.10.2.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>

    <!-- Page-Level Plugin Scripts - Dashboard -->
    <script src="js/plugins/morris/raphael-2.1.0.min.js"></script>
   
    <!-- SB Admin Scripts - Include with every page -->
    <script src="js/sb-admin.js"></script>



    <!-- Page-Level Plugin Scripts - Tables -->
    <script src="js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="js/plugins/dataTables/dataTables.bootstrap.js"></script>

    
    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script>
    $(document).ready(function() {
        $('#dataTables-example').dataTable();
    });
    </script>

</body>

</html>
