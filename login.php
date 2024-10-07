<?php
error_reporting(0);

$op = ($_GET["op"] == '') ? '' : $_GET["op"];
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>Login - Sistema Conciliaciones</title>
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
<!--
		<script language="javascript">
	navigator.geolocation.getCurrentPosition(position => {
    console.log(position);
	}, e => {
		location.href ="habilitar.php";
	});
</script>
-->

<body class="account-body accountbg">
	
	<!-- Log In page -->
	<div class="container">
		<div class="row vh-100 d-flex justify-content-center">
			<div class="col-12 align-self-center">
				<!-- Pantalla de Carga -->
				<div id="loading-screen">
					<div class="spinner mr-3"></div>
					<p>Cargando...</p>
				</div>
				<div class="row">
					<div class="col-lg-5 mx-auto">
						<div class="card rounded-lg">
							<div class="card-body p-0 auth-header-box  rounded-top">
								<div class="text-center p-3">
									<H2 style="color: white">SISTEMA CONCILIACIONES</H2>
									<BR>
									<img src="assets/images/symtrnasparente2.png" width="162" height="80" alt="logo-small">
									<BR>
									<h4 class="mt-3 mb-1 font-weight-semibold text-white font-18">Acceso a Sistema</h4>

								</div>
							</div>
							<div class="card-body p-0">
								<!-- Tab panes -->
								<div class="tab-content">
									<div class="tab-pane active p-3" id="LogIn_Tab" role="tabpanel">
										<form class="form-horizontal auth-form" action="login_valida.php" method="post" accept-charset="UTF-8" role="form" name="formulario" enctype="multipart/form-data" autocomplete="off" onsubmit="return valida_envia();return false;">
											<div class="form-group mb-2">
												<label for="usuario">Usuario</label>
												<div class="input-group">
													<input type="text" class="form-control" name="usuario" id="usuario" placeholder="Ingrese su Usuario" autocomplete="off">
													<input name="DummyUsername" type="text" style="display:none;">

												</div>
											</div><!--end form-group-->

											<div class="form-group mb-2">
												<label for="pass">Contraseña</label>
												<div class="input-group">
													<input type="password" class="form-control" name="pass" id="pass" placeholder="Ingrese Contraseña" autocomplete="off">
													<input name="DummyPassword" type="password" style="display:none;">
												</div>
											</div><!--end form-group-->

											<div class="form-group row my-3">
												<div class="col-sm-6">

												</div><!--end col-->
												<div class="col-sm-6 text-right">
													<a href="recupera_clave.php" class="text-muted font-13"><i class="dripicons-lock"></i> Olvido su Clave?</a>
												</div><!--end col-->
											</div><!--end form-group-->

											<div class="form-group mb-0 row">
												<div class="col-12">
													<button class="btn btn-primary btn-block waves-effect waves-light mb-3" type="submit">Ingresar <i class="fas fa-sign-in-alt ml-1"></i></button>
												</div><!--end col-->
											</div> <!--end form-group-->
										</form><!--end form-->
									</div>
								</div>
							</div><!--end card-body-->

						</div><!--end card-->
					</div><!--end col-->
				</div><!--end row-->
			</div><!--end col-->
		</div><!--end row-->
	</div><!--end container-->
	<!-- End Log In page -->




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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

	<script>
		window.onload = function() {
			// Oculta la pantalla de carga y muestra el contenido principal
			document.getElementById('loading-screen').style.display = 'none';
			document.getElementById('content').style.display = 'block';
		};
	</script>


	<script>
		$(document).ready(function() {
			var some_id = $('#usuario');
			some_id.prop('type', 'text');
			some_id.removeAttr('autocomplete');
		});
	</script>
	<script language="javascript">
		function validar_email(email) {
			var regex = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			return regex.test(email) ? true : false;
		}

		function valida_envia() {
			//valido el nombre

			if (document.formulario.usuario.value.length == 0) {
				Swal.fire({
					width: 600,
					icon: 'error',
					title: 'Debe ingresar un Usuario.',
					showConfirmButton: false,
					timer: 2000,
				})
				document.formulario.usuario.focus()
				return false;
			}
			if (document.formulario.pass.value.length == 0) {
				Swal.fire({
					width: 600,
					icon: 'error',
					title: 'Debe ingresar una Contraseña',
					showConfirmButton: false,
					timer: 2000,
				})
				document.formulario.pass.focus()
				return false;
			}

			document.formulario.submit();
		};
	</script>
	<?php if ($op == 1) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'error',
					title: 'USUARIO NO EXISTE',
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
	if ($op == 3) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'success',
					title: 'CORREO DE RECUPERACION ENVIADO',
					showConfirmButton: false,
					timer: 2000,
				})
			</script>
		</div>
	<?php }; ?>
	<?php
	if ($op == 4) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'success',
					title: 'CUENTA DESHABILITADA',
					showConfirmButton: false,
					timer: 2000,
				})
			</script>
		</div>
	<?php }; ?>
	<?php
	if ($op == 5) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'success',
					title: 'RECUPERACION NO VALIDAD',
					showConfirmButton: false,
					timer: 2000,
				})
			</script>
		</div>
	<?php }; ?>
	<?php
	if ($op == 6) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'success',
					title: 'CONTRASEÑA ACTUALIZADAS',
					showConfirmButton: false,
					timer: 2000,
				})
			</script>
		</div>
	<?php }; ?>
	<?php
	if ($op == 7) { ?>
		<div class="content">
			<script>
				Swal.fire({
					width: 600,
					icon: 'success',
					title: 'CORREO DE RECUPERACION ENVIADO',
					showConfirmButton: false,
					timer: 2000,
				})
			</script>
		</div>
	<?php }; ?>
	<script language="javascript">
		document.getElementById("usuario").focus();
	</script>
</body>

</html>