<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

$op = $_GET["op"];
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<title>Cargas Cartola Bancaria</title>
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
	<link href="assets/css/loading.css" rel="stylesheet" type="text/css" />
	<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body class="dark-sidenav">
	<!-- Left Sidenav -->
	<?php include("menu_izquierda.php"); ?>
	<div class="page-wrapper">
		<?php include("menu_top.php"); ?>

		<!-- Pantalla de Carga -->
		<div id="loading-screen">
			<div class="spinner mr-3"></div>
			<p>Cargando...</p>
		</div>

		<!-- Page Content -->
		<div class="page-content">
			<div class="container my-5">
				<div class="text-center mb-4">
					<h1 class="display-6">Carga de Cheques</h1>
					<p class="text-muted">Módulo para la carga de cheques</p>
				</div>

				<div class="row align-items-center mt-5">

					<!-- Formulario -->
					<div class="col-lg-6">
						<div class="card shadow-lg">
							<div class="card-body">
								<h4 class="card-title text-center mb-4">Sube tu archivo</h4>
								<form method="post" action="cargas_cheques_guardar.php" id="formulario" enctype="multipart/form-data">
									<div class="mb-3">
										<label for="archivo" class="form-label">Seleccione un archivo</label>
										<input type="file" name="archivo" id="archivo" class="dropify" />
									</div>
									<div class="text-center">
										<button type="submit" class="btn btn-primary px-4">Cargar</button>
									</div>
								</form>
							</div>
						</div>
					</div>
					<!-- Instrucciones -->
					<div class="col-lg-6 mb-4">
						<div class="card border-primary shadow-lg">
							<div class="card-header bg-gradient-primary text-white">
								<h5 class="card-title mb-0">¿Cómo subir tu archivo?</h5>
							</div>
							<div class="card-body">
								<ul class="list-group list-group-flush">
									<li class="list-group-item">
										<i data-feather="upload" class="text-primary me-2 mr-2"></i> Arrastra el archivo al área de carga o selecciónalo presionando sobre el recuadro.
									</li>
									<li class="list-group-item">
										<i data-feather="file-text" class="text-primary me-2 mr-2"></i> Solo se permite cargar un archivo por vez. Asegúrate de elegir el correcto.
									</li>
									<li class="list-group-item">
										<i data-feather="check-square" class="text-primary me-2 mr-2"></i> El archivo debe estar en formato Excel (<strong>.xlsx</strong>).
									</li>
									<li class="list-group-item">
										<i data-feather="check-circle" class="text-primary me-2 mr-2"></i> Haz clic en "<strong>Cargar</strong>" para finalizar el proceso.
									</li>
								</ul>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<!-- Footer -->
		<?php include('footer.php'); ?>
	</div>

	<!-- jQuery  -->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/metismenu.min.js"></script>
	<script src="assets/js/waves.js"></script>
	<script src="assets/js/feather.min.js"></script>
	<script src="assets/js/simplebar.min.js"></script>
	<script src="assets/js/moment.js"></script>
	<!-- Plugins js -->
	<script src="plugins/select2/select2.min.js"></script>
	<script src="plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
	<script src="plugins/timepicker/bootstrap-material-datetimepicker.js"></script>
	<script src="plugins/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
	<script src="plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js"></script>
	<!-- App js -->
	<script src="assets/js/app.js"></script>
	<script src="plugins/dropify/js/dropify.min.js"></script>
	<script src="assets/pages/jquery.form-upload.init.js"></script>


	<script>
		$(document).ready(function() {
			// Inicializar Dropify
			$('.dropify').dropify();
			// Interceptar el envío del formulario
			document.getElementById('formulario').addEventListener('submit', function(e) {
				// Obtener el archivo cargado
				const archivo = document.getElementById('archivo').files[0];
				// Validar si hay archivo seleccionado
				if (!archivo) {
					// Detener el envío del formulario
					e.preventDefault();
					// Mostrar alerta de error
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Debe seleccionar un archivo para cargar.',
					});
					return false;
				}
				// Validar la extensión del archivo
				const extension = archivo.name.split('.').pop().toLowerCase();
				if (extension !== 'xlsx') {
					// Detener el envío del formulario
					e.preventDefault();
					// Mostrar alerta de error
					Swal.fire({
						icon: 'error',
						title: 'Formato inválido',
						text: 'El archivo debe estar en formato .xlsx',
					});
					return false;
				}
				// Mostrar el spinner de carga
				document.getElementById('loading-screen').style.display = 'flex';
			});
		});
		// Ocultar el spinner al cargar la página
		window.onload = function() {
			document.getElementById('loading-screen').style.display = 'none';
		};
	</script>

	<!-- Alertas -->
	<?php if ($op == 1) { ?>
		<script>
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'RUT NO EXISTE',
				showConfirmButton: false,
				timer: 2000,
			});
		</script>
	<?php } elseif ($op == 2) { ?>
		<script>
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'USUARIO O CONTRASEÑA ERRONEA',
				showConfirmButton: false,
				timer: 2000,
			});
		</script>
	<?php } elseif ($op == 4) { ?>
		<script>
			Swal.fire({
				width: 600,
				icon: 'success',
				title: 'CARGA CREADA',
				showConfirmButton: false,
				timer: 2000,
			});
		</script>
	<?php } ?>
</body>

</html>