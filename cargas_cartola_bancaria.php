<?php
session_start();
include("funciones.php");
include("conexiones.php");
//include("permisos_adm.php");
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
	<title>Cargas SISREC</title>
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

<body class="dark-sidenav">
	<!-- Left Sidenav -->
	<?php include("menu_izquierda.php"); ?>

	<!-- end left-sidenav-->


	<div class="page-wrapper">
		<!-- Top Bar Start -->

		<!-- Top Bar End -->
		<?php include("menu_top.php"); ?>
		<!-- Page Content-->
		<div class="page-content">
			<div class="container-fluid">
				<center>
					<h4 class="mt-4">CARGA SISREC</h4>
				</center>

				<!-- Page-Title -->
				<div class="row">
					<div class="col-sm-12">
						<div class="page-title-box">
						</div><!--end row-->
					</div><!--end page-title-box-->
				</div><!--end col-->
				<!-- end page title end breadcrumb -->
				<br><br>
				<div class="row">
					<div class="col-lg-9 offset-md-2">
						<div class="card">

							<div class="card-body">
								<form method="post" action="cargas_sisrec_guardar.php" class="form-horizontal " id="validate" role="form" id="formulario" name="formulario" class="needs-validation" autocomplete="on" onsubmit="return valida_envia();return false;" enctype="multipart/form-data">



									<div class="row">

										<div class="col-md-6">
											<div class="form-group row">
												<label for="apellido" class="col-lg-3 col-form-label">ARCHIVO</label>
												<div class="col-lg-9">

													<input type="file" name="archivo" id="archivo" class="dropify" />
												</div>
											</div><!--end form-group-->
										</div><!--end col-->
									</div><!--end row-->
									<div class="mt-3" align="center">
										<button type="submit" class="btn btn-primary waves-effect waves-light">CARGAR</button>
									</div>
								</form>
							</div> <!-- end card-body -->
						</div> <!-- end card -->
					</div> <!-- end col -->
				</div> <!-- end row -->
			</div> <!-- end row -->

		</div><!-- container -->

		<?php include('footer.php'); ?>
	</div>
	<!-- end page content -->
	</div>
	<!-- end page-wrapper -->
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

	<script language="javascript">
		$(document).ready(function() {
			$("#cliente").on('change', function() {
				$("#cliente option:selected").each(function() {
					var id_cliente = $(this).val();
					$.post("get_servicios.php", {
						id_cliente: id_cliente
					}, function(data) {
						$("#servicios").html(data);
					});
				});
			});
		});
	</script>

	<script language="javascript">
		function check(e) {
			tecla = (document.all) ? e.keyCode : e.which;

			//Tecla de retroceso para borrar, siempre la permite
			if (tecla == 8) {
				return true;
			}

			// Patron de entrada, en este caso solo acepta numeros y letras
			patron = /[Kk0-9]/;
			tecla_final = String.fromCharCode(tecla);
			return patron.test(tecla_final);
		};

		function check2(e) {
			tecla = (document.all) ? e.keyCode : e.which;

			//Tecla de retroceso para borrar, siempre la permite
			if (tecla == 8) {
				return true;
			}

			// Patron de entrada, en este caso solo acepta numeros y letras
			patron = /[0-9]/;
			tecla_final = String.fromCharCode(tecla);
			return patron.test(tecla_final);
		};

		function validarEmail(valor) {
			re = /^([\da-z_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/
			if (!re.exec(valor)) {
				return false;
			} else return true;
		}





		$("#btnvalida").click(function() {
			if (Fn.validaRut($("#txt_rut").val())) {
				$("#msgerror").html("El rut ingresado es válido :D");
			} else {
				$("#msgerror").html("El Rut no es válido :'( ");
			}
		});


		function valida_envia() {
			//valido el nombre
			if (document.formulario.archivo.value.length == 0) {
				Swal.fire({
					width: 600,
					icon: 'error',
					title: 'Debe ingresar un Archivo.',
					showConfirmButton: false,
					timer: 2000,
				})
				return false;
			}

			document.formulario.submit();
		}
	</script>
	<?php if ($op == 1) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'error',
					title: 'RUT NO EXISTE',
					showConfirmButton: false,
					timer: 2000,
				})
			</script>
		</div>
	<?php };
	if ($op == 2) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'error',
					title: 'USUARIO O CONTRASEÑA ERRONEA',
					showConfirmButton: false,
					timer: 2000,
				})
			</script>
		</div>
	<?php };
	if ($op == 4) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'error',
					title: 'CARGA CREADA',
					showConfirmButton: false,
					timer: 2000,
				})
			</script>
		</div>
	<?php }; ?>

</body>

</html>