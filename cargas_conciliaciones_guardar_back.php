<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<title>Carga Conciliaciones</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta content="CRM" name="description" />
	<meta content="" name="author" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- App favicon -->
	<link rel="shortcut icon" href="assets/images/favicon.ico">
	<link href="plugins/dropify/css/dropify.min.css" rel="stylesheet">
	<!-- Plugins css -->
	<link href="plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
	<link href="plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.css" rel="stylesheet" type="text/css" />
	<link href="plugins/timepicker/bootstrap-material-datetimepicker.css" rel="stylesheet">
	<link href="plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
	<!-- App css -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
	<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body>

	<!-- Pantalla de Carga -->
	<div id="loading-screen">
		<div class="spinner"></div>
		<p>Cargando...</p>
	</div>
	<div class="container" id="content">

		<?php
		session_start();
		include("funciones.php");
		// CONEXION a ZEUS
		// SET UP CONEXION SQL SERVER
		$repositorio    	= "\\\\192.168.1.193";
		$folder         	= "$repositorio\\excel_PAGOS";

		//$repositorio    	= "\\\\192.168.101.15";
		//$folder         	= "$repositorio\\excel_pagos\RUTERO";

		$serverName3 = "192.168.101.15\RECOVER";
		$connectionInfo3 = array("Database" => "SYM", "UID" => "sa", "PWD" => "Hendrix1966.");
		$conn3           = sqlsrv_connect($serverName3, $connectionInfo3);

		$serverName 		= "192.168.1.193\EXPLOTACION";
		$connectionInfo 	= array("Database" => "conciliacion", "UID" => "prueba3", "PWD" => "123456789");
		$conn2           	= sqlsrv_connect($serverName, $connectionInfo);

		noCache();
		/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

		//$resultado_r($_FILES);
		//exit;

		if ($_FILES['archivo']['name'] != '') {
			//datos del arhivo
			$arr			= explode(".", $_FILES['archivo']['name']);
			$extension		= $arr[1];
			$nombre_archivo = generateRandomString(20) . '.' . $extension;
			$tipo_archivo 	= $_FILES['archivo']['type'];
			$tamano_archivo = $_FILES['archivo']['size'];
			//echo $tipo_archivo ."<BR>";
			//compruebo si las características del archivo son las que deseo

			move_uploaded_file($_FILES['archivo']['tmp_name'], 'TransferenciasRecibidas.xlsx');
		};

		require_once('phpexcel2/vendor/autoload.php');
		$allowedFileType = [
			'application/vnd.ms-excel',
			'text/xls',
			'text/xlsx',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
		];

		$Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

		$spreadSheet 	= $Reader->load('TransferenciasRecibidas.xlsx');
		$excelSheet 	= $spreadSheet->getActiveSheet();
		$spreadSheetAry = $excelSheet->toArray();
		$sheetCount 	= count($spreadSheetAry);

		$numeroMayorDeFila 	 = $excelSheet->getHighestRow(); // Numérico
		$letraMayorDeColumna = $excelSheet->getHighestColumn(); // Letra


		$sql = "delete from [192.168.1.193].conciliacion.dbo.[Transferencias_Recibidas]";

		$stmt = sqlsrv_query($conn3, $sql);
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
		}
		$sql  = "delete from [Transferencias_Recibidas]";
		$stmt = sqlsrv_query($conn3, $sql);
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
		}

		for ($i = 7; $i <= $sheetCount - 1; $i++) {
			$transaccion	= $spreadSheetAry[$i][0];
			$fecha 			= $spreadSheetAry[$i][2];
			$rutempresa		= $spreadSheetAry[$i][3];
			$nombreempresa	= $spreadSheetAry[$i][4];
			$cuenta_bene	= $spreadSheetAry[$i][5];
			$producto 		= $spreadSheetAry[$i][6];
			$rutordenante	= $spreadSheetAry[$i][7];
			$nombreorden	= $spreadSheetAry[$i][8];
			$bancoorden		= $spreadSheetAry[$i][9];
			$cuentaorden	= $spreadSheetAry[$i][10];
			$monto			= $spreadSheetAry[$i][11];
			$formapago 		= $spreadSheetAry[$i][12];
			$numerodoc		= $spreadSheetAry[$i][13];
			$oficina		= $spreadSheetAry[$i][14];

			$sql = "INSERT INTO [dbo].[Transferencias_Recibidas]			   
		 VALUES
			   ('" . $transaccion . "'
			   ,'" . $fecha . "'
			   ,'" . $rutempresa . "'
			   ,'" . $nombreempresa . "'
			   ,'" . $cuenta_bene . "'
			   ,'" . $producto . "'
			   ,'" . $rutordenante . "'
			   ,'" . $nombreorden . "'
			   ,'" . $bancoorden . "'
			   ,'" . $cuentaorden . "'
			   ,'" . $monto . "'
			   ,'" . $formapago . "'
			   ,'" . $numerodoc . "'
			   ,'" . $oficina . "'
			   )";
			// echo $sql.'<BR>';	
			$stmt = sqlsrv_query($conn3, $sql);
			if ($stmt === false) {
				die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
			}
		};
		$sql = "insert into [192.168.1.193].conciliacion.dbo.[Transferencias_Recibidas]
			select * -- delete
			from [Transferencias_Recibidas]";

		$stmt = sqlsrv_query($conn3, $sql);
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
		}

		$sql = "EXEC [192.168.1.193].conciliacion.dbo.Carga_cartola";

		$stmt = sqlsrv_query($conn3, $sql);
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
		}

		header("Location: cargas_conciliaciones.php?op=1");
		?>
	</div>
	<script>
		window.onload = function() {
			// Oculta la pantalla de carga y muestra el contenido principal
			document.getElementById('loading-screen').style.display = 'none';
			document.getElementById('content').style.display = 'block';
		};
	</script>


</body>

<!-- jQuery  -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/metismenu.min.js"></script>
<script src="assets/js/waves.js"></script>
<script src="assets/js/feather.min.js"></script>
<script src="assets/js/simplebar.min.js"></script>
<script src="assets/js/moment.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Required datatable js -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="plugins/datatables/dataTables.buttons.min.js"></script>
<script src="plugins/datatables/buttons.bootstrap4.min.js"></script>
<script src="plugins/datatables/jszip.min.js"></script>
<script src="plugins/datatables/pdfmake.min.js"></script>
<script src="plugins/datatables/vfs_fonts.js"></script>
<script src="plugins/datatables/buttons.html5.min.js"></script>
<script src="plugins/datatables/buttons.print.min.js"></script>
<script src="plugins/datatables/buttons.colVis.min.js"></script>
<!-- Responsive examples -->
<script src="plugins/datatables/dataTables.responsive.min.js"></script>
<script src="plugins/datatables/responsive.bootstrap4.min.js"></script>
<script src="assets/pages/jquery.datatable.init.js"></script>
<!-- App js -->
<script src="assets/js/app.js"></script>
<script src="plugins/datatables/spanish.js"></script>
<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>


</html>