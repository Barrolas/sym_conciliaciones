<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<title>Conciliaciones</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta content="CRM" name="description" />
	<meta content="" name="author" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- App favicon -->
	<link rel="shortcut icon" href="assets/images/favicon.ico">
	<!-- DataTables -->
	<link href="plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css">
	<!-- Responsive datatable examples -->
	<link href="plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
	<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css">
	<!-- App css -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
	<link href="plugins/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
	<link href="plugins/dropify/css/dropify.min.css" rel="stylesheet">
	<link href="assets/css/loading.css" rel="stylesheet" type="text/css" />
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
	<!-- Plugins -->
	<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body class="account-body accountbg">
	<!-- Pantalla de Carga -->
	<div id="loading-screen">
		<div class="spinner mr-3"></div>
		<p>Cargando...</p>
	</div>

	<script>
		window.onload = function() {
			// Oculta la pantalla de carga y muestra el contenido principal
			document.getElementById('loading-screen').style.display = 'none';
			document.getElementById('content').style.display = 'block';
		};
	</script>


	<!-- Contenido Principal -->
	<div id="content" style="display: none;">
		<?php
		session_start();
		include("funciones.php");
		include("conexiones.php");
		noCache();

		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		$usuario = trim($_POST['usuario']);
		$pass = sha1(trim($_POST['pass']));

		$sql = "EXEC [_SP_CONCILIACIONES_VALIDA_USUARIO] '$usuario','$pass'";
		$stmt = sqlsrv_query($conn, $sql);

		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true));
		}

		$elusuario = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

		if (!$elusuario) {
			header("Location: login.php?op=1");
			exit;
		}

		if ($elusuario["ESTADO"] == '0') {
			header("Location: login.php?op=4");
			exit;
		}

		if ($elusuario["PASSWORD"] != $pass) {
			header("Location: login.php?op=2");
			exit;
		}

		$_SESSION["ID_USUARIO"] = $elusuario["ID_USUARIO"];
		$_SESSION["PERFIL"] 	= $elusuario["ID_PERFIL"];
		$_SESSION["NOMBRES"] 	= $elusuario["NOMBRES"] . ' ' . $elusuario["APELLIDOS"];

		print_r($_SESSION["ID_USUARIO"]);


		$sql_deudores = "EXEC [_SP_CONCILIACIONES_DEUDORES_ACTUALIZA]";
		$stmt_deudores = sqlsrv_query($conn, $sql_deudores);

		if ($stmt_deudores === false) {
			die(print_r(sqlsrv_errors(), true));
		}

		header("Location: menu_principal.php");
		exit;
		?>
	</div>


</body>

</html>