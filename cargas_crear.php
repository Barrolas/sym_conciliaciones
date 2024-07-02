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
	<title><?= $sistema ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta content="CRM" name="description" />
	<meta content="" name="author" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- App favicon -->
	<link rel="shortcut icon" href="assets/images/favicon.ico">
	<!-- App css -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
	<link href="plugins/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
	<link href="plugins/dropify/css/dropify.min.css" rel="stylesheet">
	<!-- Select2 CSS -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
	<link href="plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
	<!-- Plugins -->
	<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
	<!-- Custom CSS -->
	<style>
		.form-control,
		.select2-container .select2-selection--single {
			border: 1px solid #ced4da;
			/* Cambia esto según el color de borde que estés utilizando */
			height: 40px;
			/* Ajusta la altura según sea necesario */
		}

		.select2-container .select2-selection--single .select2-selection__rendered {
			line-height: 40px;
			/* Ajusta para que coincida con la altura */
		}

		.select2-container--default .select2-selection--single .select2-selection__arrow {
			height: 40px;
			/* Ajusta la altura según sea necesario */
		}
	</style>
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
				<!-- Page-Title -->
				<div class="row">
					<div class="col-sm-12">
						<div class="page-title-box">
							<div class="row">
								<div class="col">

									<ol class="breadcrumb">

										<li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>

										<li class="breadcrumb-item"><a href="cargas.php">Cargas</a></li>
										<li class="breadcrumb-item active">Crear Cargas</li>
									</ol>
								</div><!--end col-->
							</div><!--end col-->
						</div><!--end row-->
					</div><!--end page-title-box-->
				</div><!--end col-->
				<!-- end page title end breadcrumb -->


				<div class="container px-3">
					<div class="row">
						<div class="col">
							<h3>
								<b>Crear carga</b>
							</h3>
						</div>
						<div class="row">
							<div class="col-12 mx-2">
								<p class="card-text">
									Ingrese el cliente al cual corresponda la carga de documentos, asegurándose de seleccionar la opción correcta. Luego, arrastre el archivo correspondiente hasta la caja designada o haga clic en ella para iniciar el proceso de subida del archivo. Es fundamental verificar que el archivo esté en el formato adecuado. Si todavía no cuenta con el formulario necesario,
									haga click <strong><a href="assets/base.xlsx">aquí</a></strong> para descargar el archivo base.
									<br><br>
									<b>Antes de continuar, asegúrese de revisar las validaciones que se detallan al hacer click aquí </b><a href="#"><i class="fa fa-question-circle p-1 text-primary" data-toggle="collapse" data-target="#demo" aria-hidden="true"></i></a>
								</p>
							</div>
						</div>
					</div>
				</div>


				<div class="container my-3 mt-4">
					<div id="demo" class="collapse">
						<div class="row justify-content-center">
							<div class="col-lg-10">
								<div class="card bg-light rounded p-1 style=" width: 100%;"">
									<div class="card-body">
										<h5 class="card-title pt-3" style="text-transform: none;">
											<i class="fas fa-check-circle text-info pr-2"></i><strong>Validaciones de ingreso de registros</strong>
										</h5>
										<br>
										<p class="card-text">
											<b>&emsp; Asegúrese de cumplir con las siguientes validaciones al ingresar registros:</b>
										</p>
										<ul class="list-unstyled">
											<li><strong>&emsp; RUT:</strong> Al ingresar algun RUT, asegurese de no usar puntos ni dígito verificador.</li>
											<li><strong>&emsp; RUT DEUDOR:</strong> Debe indicar el RUT del deudor.</li>
											<li><strong>&emsp; NUMEROS:</strong> Al ingresar números, asegurese de no ingresar espacios, ni puntos, ni otro tipo de caracteres.</li>
											<li><strong>&emsp; FECHA DE VENCIMIENTO o FECHA DE PROTESTO:</strong> Debe incluir al menos una de estas fechas.</li>
											<li><strong>&emsp; TOTAL MORA:</strong> Debe especificar el total de la mora correctamente sin espacios, sin puntos, ni otro tipo de caracteres.</li>
											<li><strong>&emsp; TIPO DE DOCUMENTO:</strong> Seleccione el tipo de documento correspondiente (revise la hoja <strong>TIPO DOCUMENTOS</strong> del formulario).</li>
											<li><strong>&emsp; NOMBRE DEUDOR:</strong> Debe proporcionar el nombre completo del deudor.</li>
											<li><strong>&emsp; RUT GIRADOR y NOMBRE GIRADOR:</strong> Si ingresa el RUT del girador, asegúrese de incluir también su nombre.</li>
										</ul>
									</div> <!-- end card-body -->
								</div> <!-- end card -->
							</div> <!-- end col-lg-9 -->
						</div> <!-- end row -->
					</div><!-- collapse -->
				</div><!-- container -->

				<div class="container">
					<div class="row justify-content-center"> <!-- Alinea el contenido al centro horizontalmente -->
						<div class="col-lg-11"> <!-- Ajusta el tamaño de la columna -->
							<div class="card">
								<div class="card-body">
									<form method="post" action="cargas_guardar.php" class="form-horizontal needs-validation " id="formulario" name="formulario" autocomplete="on" onsubmit="return valida_envia(); return false;" enctype="multipart/form-data">

										<div class="form-group row pt-3">
											<label for="idcliente" class="col-lg-1 col-form-label">CLIENTE</label>
											<div class="col-lg-11">
												<select name="idcliente" id="idcliente" class="select2 form-control mb-3 custom-select" style="width: 100%; height:36px;">
													<option value="0" selected>Seleccione un cliente al cual asociar la carga</option>
													<?php
													$sql = "EXEC [_SP_SJUD_CLIENTE_LISTA] 1";

													$stmt = sqlsrv_query($conn, $sql);
													if ($stmt === false) {
														die(print_r(sqlsrv_errors(), true));
													}
													while ($cliente = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
													?>
														<option value="<?php echo $cliente["ID_CLIENTE"] ?>"><?php echo $cliente["NOMBRE"] ?></option>
													<?php }; ?>
												</select>
											</div>
										</div><!--end form-group-->

										<div class="form-group row">
											<label for="archivo" class="col-lg-1 col-form-label">ARCHIVO</label>
											<div class="col-lg-11">
												<input type="file" name="archivo" id="archivo" class="dropify" />
											</div>
										</div><!--end form-group-->

										<div class="mt-3 text-center pl-5 pb-3"> <!-- Centra los botones -->
											<button type="submit" class="btn btn-primary waves-effect waves-light">CREAR</button>
											<button type="button" onClick="javascript:location.href='cargas.php';" class="btn btn-danger waves-effect waves-light">VOLVER</button>
										</div>
									</form>
								</div> <!-- end card-body -->
							</div> <!-- end card -->
						</div> <!-- end col-lg-9 -->
					</div> <!-- end row justify-content-center -->
				</div><!-- container -->


			</div>
			<!-- end page content -->
		</div>
		<!-- end page-wrapper -->
		<?php include('footer.php'); ?>
	</div>
</body>

<!-- jQuery  -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/metismenu.min.js"></script>
<script src="assets/js/waves.js"></script>
<script src="assets/js/feather.min.js"></script>
<script src="assets/js/simplebar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- App js -->
<script src="assets/js/app.js"></script>
<script src="plugins/datatables/spanish.js"></script>
<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
<script src="plugins/dropify/js/dropify.min.js"></script>
<script src="assets/pages/jquery.form-upload.init.js"></script>

<script language="javascript">
	$(document).ready(function() {
		$('#idcliente').select2();
	});
</script>


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

	$(document).ready(function() {
		$('#idcliente').select2();
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

	function valida_envia() {

		if (document.formulario.idcliente.selectedIndex == 0) {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe seleccionar un módulo.',
				showConfirmButton: false,
				timer: 2000,
			})
			return false;
		}

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

<script language="javascript">
	// Select 2 Script
	$('#idcliente').select2({
		theme: 'bootstrap-5'
	});
</script>

<script language="javascript">
	function Optionselection(select) {
		window.location.href = "perfiles_modulos.php?perfil1=" + select;
		//alert("Option Chosen by you is " + chosen.value);
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


</html>