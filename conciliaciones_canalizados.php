<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$op = 0;
if (isset($_GET["op"])) {
	$op = $_GET["op"];
};

$iddeudor = 0;
if (isset($_GET["iddeudor"])) {
	$iddeudor = $_GET["iddeudor"];
};

?>
<!DOCTYPE html>
<html lang="en">

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
	<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css">
	<link href="plugins/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
	<!-- Select2 CSS -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
	<!-- Plugins -->
	<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
	<script src="assets/js/sjud/funciones.js"></script>
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
		<?php include("menu_top.php"); ?>
		<!-- Top Bar End -->

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

										<li class="breadcrumb-item" id="volverbtn"><a href="menu_principal.php">Inicio</a></li>
										<li class="breadcrumb-item" id="volverbtn"><a href="deudores.php">Deudores</a></li>
										<li class="breadcrumb-item active">Información de contacto</li>
									</ol>
								</div><!--end col-->
							</div><!--end col-->
						</div><!--end row-->
					</div><!--end page-title-box-->
				</div><!--end col-->
			</div><!--end row-->
			<!-- end page title end breadcrumb -->

			<div class="container-fluid px-4 mb-2">
				<div class="row">
					<div class="col">
						<h3>
							<b>Información de contacto del deudor</b>
						</h3>
					</div>
					<div class="row">
						<div class="col-12 mx-2">
							<p>
								La herramienta permite <i class='fas fa-plus pr-2'></i>AGREGAR información de contacto completa para un deudor,
								incluyendo direcciones, teléfonos y correos electrónicos. Para ingresar estos datos,
								simplemente haga clic en las pestañas correspondientes. Cada pestaña facilita la entrada de la
								información específica, asegurando que todos los detalles relevantes estén organizados y
								fácilmente accesibles para su gestión y seguimiento. Además, la interfaz intuitiva de la
								herramienta permite una revisión de la información ingresada. Esto garantiza
								que los datos de contacto estén siempre disponibles además de poder habilitar/deshabilitar cada
								registro.
							</p>
						</div>
					</div>
				</div>
			</div>

			<div class="container-fluid px-5 ">
				<div class="row">
					<div class="col mx-2 mb-3">
						<div class="card bg-light rounded h-100" style="width: 100%;">
							<div class="card-body px-4">

								<h4>
									<i class="fas fa-info-circle pr-2 text-info"></i>
									<b>Paso 2: Información de contacto</b>
								</h4>
								<p class="mb-0">
									En este paso, ingrese los datos de contacto del deudor, incluyendo direcciones,
									teléfonos y correos, en las pestañas correspondientes. Los campos marcados
									con un asterisco rojo <span class="text-danger">*</span></label> son obligatorios. Puede revisar cada registro en las tablas
									ubicadas en la parte inferior de cada sección.
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>


			<div class="container-fluid d-flex justify-content-start justify-items-stretch">
				<div class="col-lg-12">
					<div class="card text-start border-0">
						<div class="card-body text-start">
							<div class="row text-start">
								<div class="col-md-12 px-5 text-start">
									<div class="form-group row text-start justify-content-start justify-items-stretch">
										<label for="deudor" class="col-lg-2 col-form-label">DEUDOR</label>
										<div class="col-lg-8">
											<?php
											$sql = "EXEC [_SP_SJUD_DEUDOR_CONSULTA_ID] $iddeudor";

											$stmt = sqlsrv_query($conn, $sql);
											//echo $sql ;	
											if ($stmt === false) {
												die(print_r(sqlsrv_errors(), true));
											}
											$deudor = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
											?>
											<input type="text" name="deudor" id="deudor" class="form-control" maxlength="50" value="<?php echo $deudor['RUT'] . ' - ' . $deudor['NOMBRES'] . ' ' . $deudor['APELLIDO_PATERNO'] . ' ' . $deudor['APELLIDO_MATERNO']; ?>" autocomplete="off" disabled />
										</div>
									</div><!--end form-group-->
								</div><!--end col-->
							</div><!--end row-->

							<?php if ($iddeudor <> 0) { ?>

								<!-- TABS -->

								<div class="card border-0">
									<div class="card-header border-0">
										<ul class="nav back nav-pills nav-justified mb-3 mx-3 border-0 bg-light" role="tablist" id="pestañas">
											<li class="nav-item waves-effect waves-light">
												<a class="nav-link active" data-toggle="tab" href="#direcciones" role="tab" aria-selected="true">DIRECCIONES</a>
											</li>
											<li class="nav-item waves-effect waves-light">
												<a class="nav-link" data-toggle="tab" href="#telefonos" role="tab" aria-selected="false">TELEFONOS</a>
											</li>
											<li class="nav-item waves-effect waves-light">
												<a class="nav-link" data-toggle="tab" href="#correos" role="tab" aria-selected="false">CORREOS</a>
											</li>
										</ul>

										<!-- Tab panes -->
										<div class="container-fluid tab-content">
											<div class="tab-pane active" id="canalizados" role="tabpanel">
												<div class="card">
													<div class="card-body px-4">
														<form method="post" action="deudores_direcciones_guardar.php?iddeudor=<?php echo $iddeudor; ?>" class="form-horizontal " id="validate" role="form" id="formulario1" name="formulario1" class="needs-validation" autocomplete="on" onsubmit="return valida_envia1();return false;">

															<div class="form-group row text-start justify-content-start justify-items-stretch mb-1">
																<div class="col-6">
																	<h4 class="mb-0 pb-0">Ingreso de direcciones</h4>
																</div>
															</div>

															<div class="form-group row text-start justify-content-start justify-items-stretch">
																<div class="col-12">
																	<div class="form-group row text-start justify-content-start justify-items-stretch mb-0">
																		<div class="col-12">
																			<p class="my-0 pb-0 text-secondary">Debe ingresar la dirección y número en el campo DIRECCIÓN
																				<i>(Ej. AV. EJEMPLO 123)</i>. <br> En el campo COMPLEMENTO puede
																				ingresar detalles de la dirección. <i>(Ej. OF 101, LOCAL-A, PISO 1)</i>
																			</p>
																		</div>
																	</div>
																</div>
															</div>

															<div class="form-group row text-center justify-content-start justify-items-stretch">
																<label for="direccion" class="col-lg-2 col-form-label">DIRECCIÓN<span class="text-danger">*</span></label>
																<div class="col-lg-4">
																	<input onblur="convertirAMayusculas(this)" type="text" name="direccion" id="direccion" class="form-control" maxlength="50" placeholder="Ingrese la dirección" autocomplete="off" />
																</div>
																<label for="complemento" class="col-lg-2 col-form-label">COMPLEMENTO</label>
																<div class="col-lg-4">
																	<input onblur="convertirAMayusculas(this)" type="text" name="complemento" id="complemento" class="form-control" maxlength="50" placeholder="Ingrese el complemento de la dirección" autocomplete="off" />
																</div>
															</div>

															<div class="form-group row text-center justify-content-start justify-items-stretch">
																<label for="region" class="col-lg-2 col-form-label">REGIÓN<span class="text-danger">*</span></label>
																<div class="col-lg-4">
																	<select name="region" id="region" class="form-control custom-select" style="width: 100%; height:36px;">
																		<option value="0" selected>Seleccione una región</option>
																		<?php
																		$sql = "EXEC [_SP_SJUD_REGION_LISTA]";

																		$stmt = sqlsrv_query($conn, $sql);
																		//echo $sql ;	
																		if ($stmt === false) {
																			die(print_r(sqlsrv_errors(), true));
																		}
																		while ($region = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
																		?>
																			<option <?php if ($region["ID_REGION"] == $region["ID_REGION"]) {
																					}; ?> value="<?php echo $region["ID_REGION"] ?>"><?php echo $region["DESCRIPCION"] ?></option>
																		<?php }; ?>
																	</select>
																</div>
																<label for="comuna" class="col-lg-2 col-form-label">COMUNA<span class="text-danger">*</span></label>
																<div class="col-lg-4">
																	<select name="comuna" id="comuna" class="form-control custom-select" style="width: 100%; height:36px;">
																		<option value="0" selected>Seleccione una comuna</option>
																	</select>
																</div>
															</div>

															<div class="form-group row text-start justify-content-start justify-items-stretch">
																<label for="tipodir" class="col-lg-2 col-form-label py-0">TIPO DIRECCION<span class="text-danger">*</span></label>
																<div class="col-lg-4">
																	<select name="tipodir" id="tipodir" class="form-control custom-select" style="width: 100%; height:36px;">
																		<option value="0" selected>Seleccione un tipo de dirección</option>
																		<?php
																		$sql_tipodir = "EXEC [_SP_SJUD_TIPO_DIRECCION_LISTA]";

																		$stmt_tipodir = sqlsrv_query($conn, $sql_tipodir);
																		//echo $sql ;	
																		if ($stmt_tipodir === false) {
																			die(print_r(sqlsrv_errors(), true));
																		}
																		while ($tipodir = sqlsrv_fetch_array($stmt_tipodir, SQLSRV_FETCH_ASSOC)) {
																		?>
																			<option <?php if ($tipodir["ID_TIPO_DIRECCION"] == $tipodir["ID_TIPO_DIRECCION"]) {
																					}; ?> value="<?php echo $tipodir["ID_TIPO_DIRECCION"] ?>"><?php echo $tipodir["DESCRIPCION"] ?></option>
																		<?php }; ?>
																	</select>
																</div>
															</div>


															<div class="my-4" align="center">
																<button type="submit" class="btn btn-primary waves-effect waves-light"><i class='fas fa-plus pr-2'></i>AGREGAR</button>
																<button type="button" id="volverbtn" onClick="javascript:location.href='deudores.php';" class="btn btn-danger waves-effect waves-light"><i class='fas fa-arrow-left pr-2'></i>VOLVER</button>
															</div>

														</form>

														<hr class="hr" />

														<table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
															<thead>
																<tr>
																	<th>DIRECCION</th>
																	<th>COMPLEMENTO</th>
																	<th>TIPO</th>
																	<th>COMUNA</th>
																	<th>ESTADO</th>
																</tr>
															</thead>
															<tbody>
																<?php
																$sql_direcciones = "EXEC [_SP_SJUD_DIRECCION_MANUAL_CONSULTA] $iddeudor";

																$stmt_direcciones = sqlsrv_query($conn, $sql_direcciones);
																//echo $sql ;	
																if ($stmt_direcciones === false) {
																	die(print_r(sqlsrv_errors(), true));
																}
																while ($direcciones = sqlsrv_fetch_array($stmt_direcciones, SQLSRV_FETCH_ASSOC)) {
																?>
																	<tr>
																		<td><?php echo $direcciones["DIRECCION"] ?></td>
																		<td><?php echo $direcciones["COMPLEMENTO"] ?></td>
																		<td><?php echo $direcciones["TIPO_DIR"] ?></td>
																		<td><?php echo $direcciones["COMUNA"] ?></td>
																		<td class="col-1">
																			<?php if ($direcciones["ID_ESTADO"] == 1) { ?>
																				<a data-toggle="tooltip" title="Cambia Estado" href="deudores_direcciones_cambiar.php?id=<?php echo $direcciones["ID_DIRECCION"] ?>&iddeudor=<?php echo $iddeudor ?>" class=" btn btn-icon btn-rounded btn-success"><i class="feather-24" data-feather="thumbs-up"></i></a>
																			<?php } else { ?>
																				<a data-toggle="tooltip" title="Cambia Estado" href="deudores_direcciones_cambiar.php?id=<?php echo $direcciones["ID_DIRECCION"] ?>&iddeudor=<?php echo $iddeudor ?>" class=" btn btn-icon btn-rounded btn-danger"><i class="feather-24" data-feather="thumbs-down">
																					<?php }; ?>
																		</td>
																	</tr>
																<?php }; ?>
															</tbody>
														</table>
													</div>
												</div>
											</div> <!--final del tab -->

											<div class="tab-pane" id="telefonos" role="tabpanel">
												<div class="card">
													<div class="card-body px-4">
														<form method="post" action="deudores_telefonos_guardar.php?iddeudor=<?php echo $iddeudor; ?>" class="form-horizontal " id="validate" role="form" id="formulario2" name="formulario2" class="needs-validation" autocomplete="on" onsubmit="return valida_envia2();return false;">

															<div class="form-group row text-start justify-content-start justify-items-stretch mb-1">
																<div class="col-6">
																	<h4 class="mb-0 pb-0">Ingreso de teléfonos</h4>
																</div>
															</div>

															<div class="form-group row text-start justify-content-start justify-items-stretch">
																<div class="col-12">
																	<p class="my-0 pb-0 text-secondary">Los telefonos deben tener un largo de 9 números.
																		<i>(Ej. 9XXXXXXXX / 2XXXXXXXX)</i>
																	</p>
																</div>
															</div>

															<div class="form-group row text-start justify-content-start justify-items-stretch">
																<label for="numerotel" class="col-lg-2 col-form-label">NÚMERO<span class="text-danger">*</span></label>
																<div class="col-lg-4">
																	<input type="text" name="numerotel" id="numerotel" class="form-control" maxlength="50" placeholder="Ingrese el número de teléfono" autocomplete="off" />
																</div>

																<label for="tipotel" class="col-lg-2 col-form-label py-0">TIPO TELÉFONO<span class="text-danger">*</span></label>
																<div class="col-lg-4">
																	<select name="tipotel" id="tipotel" class="form-control custom-select" style="width: 100%; height:36px;">
																		<option value="0" selected>Seleccione un tipo de teléfono</option>
																		<?php
																		$sql_tipotel = "EXEC [_SP_SJUD_TIPO_TELEFONO_LISTA]";

																		$stmt_tipotel = sqlsrv_query($conn, $sql_tipotel);
																		//echo $sql ;	
																		if ($stmt_tipotel === false) {
																			die(print_r(sqlsrv_errors(), true));
																		}
																		while ($tipotel = sqlsrv_fetch_array($stmt_tipotel, SQLSRV_FETCH_ASSOC)) {
																		?>
																			<option <?php if ($tipotel["ID_TIPO_TELEFONO"] == $tipotel["ID_TIPO_TELEFONO"]) {
																					}; ?> value="<?php echo $tipotel["ID_TIPO_TELEFONO"] ?>"><?php echo $tipotel["DESCRIPCION"] ?></option>
																		<?php }; ?>
																	</select>
																</div>
															</div>

															<div class="form-group row text-center justify-content-start justify-items-stretch">
															</div>

															<div class="my-4" align="center">
																<button type="submit" class="btn btn-primary waves-effect waves-light"><i class='fas fa-plus pr-2'></i>AGREGAR</button>
																<button type="button" id="volverbtn" onClick="javascript:location.href='deudores.php';" class="btn btn-danger waves-effect waves-light"><i class='fas fa-arrow-left pr-2'></i>VOLVER</button>
															</div>

														</form>

														<hr class="hr" />

														<table id="datatable3" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
															<thead>
																<tr>
																	<th>FONO</th>
																	<th>TIPO</th>
																	<th>ESTADO</th>
																</tr>
															</thead>
															<tbody>
																<?php
																$sql_telefonos = "EXEC [_SP_SJUD_TELEFONO_MANUAL_CONSULTA] $iddeudor";

																$stmt_telefonos = sqlsrv_query($conn, $sql_telefonos);
																//echo $sql ;	
																if ($stmt_telefonos === false) {
																	die(print_r(sqlsrv_errors(), true));
																}
																while ($telefonos = sqlsrv_fetch_array($stmt_telefonos, SQLSRV_FETCH_ASSOC)) {
																?>
																	<tr>
																		<td><?php echo $telefonos["FONO"] ?></td>
																		<td><?php echo $telefonos["TIPO_TEL"] ?></td>
																		<td class="col-1">
																			<?php if ($telefonos["ID_ESTADO"] == 1) { ?>
																				<a data-toggle="tooltip" title="Cambia Estado" href="deudores_telefonos_cambiar.php?id=<?php echo $telefonos["ID_TELEFONO"] ?>&iddeudor=<?php echo $iddeudor ?>" class=" btn btn-icon btn-rounded btn-success"><i class="feather-24" data-feather="thumbs-up"></i></a>
																			<?php } else { ?>
																				<a data-toggle="tooltip" title="Cambia Estado" href="deudores_telefonos_cambiar.php?id=<?php echo $telefonos["ID_TELEFONO"] ?>&iddeudor=<?php echo $iddeudor ?>" class=" btn btn-icon btn-rounded btn-danger"><i class="feather-24" data-feather="thumbs-down">
																					<?php }; ?>
																		</td>
																	</tr>
																<?php }; ?>
															</tbody>
														</table>
													</div>
												</div>
											</div> <!--final del tab -->

											<div class="tab-pane" id="correos" role="tabpanel">
												<div class="card">
													<div class="card-body px-4">
														<form method="post" action="deudores_correos_guardar.php?iddeudor=<?php echo $iddeudor; ?>" form-horizontal " id=" validate" role="form" id="formulario3" name="formulario3" class="needs-validation" autocomplete="on" onsubmit="return valida_envia3();return false;">
															<div class="form-group row text-start justify-content-start justify-items-stretch mb-1">
																<div class="col-6">
																	<h4 class="mb-0 pb-0">Ingreso de correos electrónicos</h4>
																</div>
															</div>

															<div class="form-group row text-start justify-content-start justify-items-stretch">
																<div class="col-12">
																	<p class="my-0 pb-0 text-secondary">Debe ingresar un correo electrónico segun corresponda <i>(ej. CORREO@EJEMPLO.COM)</i>.</p>
																</div>
															</div>

															<div class="form-group row text-center justify-content-start justify-items-stretch">
																<label for="correo" class="col-lg-2 col-form-label">CORREO<span class="text-danger">*</span></label>
																<div class="col-lg-4">
																	<input onblur="convertirAMayusculas(this)" type="text" name="correo" id="correo" class="form-control" maxlength="50" placeholder="Ingrese el correo electrónico" autocomplete="off" />
																</div>
																<label for="tipocorreo" class="col-lg-2 col-form-label py-0">TIPO CORREO<span class="text-danger">*</span></label>
																<div class="col-lg-4">
																	<select name="tipocorreo" id="tipocorreo" class="form-control custom-select" style="width: 100%; height:36px;">
																		<option value="0" selected>Seleccione un tipo de correo</option>
																		<?php
																		$sql_tipocorreo = "EXEC [_SP_SJUD_TIPO_CORREO_LISTA]";

																		$stmt_tipocorreo = sqlsrv_query($conn, $sql_tipocorreo);
																		//echo $sql ;	
																		if ($stmt_tipocorreo === false) {
																			die(print_r(sqlsrv_errors(), true));
																		}
																		while ($tipocorreo = sqlsrv_fetch_array($stmt_tipocorreo, SQLSRV_FETCH_ASSOC)) {
																		?>
																			<option <?php if ($tipocorreo["ID_TIPO_CORREO"] == $tipocorreo["ID_TIPO_CORREO"]) {
																					}; ?> value="<?php echo $tipocorreo["ID_TIPO_CORREO"] ?>"><?php echo $tipocorreo["DESCRIPCION"] ?></option>
																		<?php }; ?>
																	</select>
																</div>
															</div>

															<div class="my-4" align="center">
																<button type="submit" class="btn btn-primary waves-effect waves-light"><i class='fas fa-plus pr-2'></i>AGREGAR</button>
																<button type="button" id="volverbtn" onClick="javascript:location.href='deudores.php';" class="btn btn-danger waves-effect waves-light"><i class='fas fa-arrow-left pr-2'></i>VOLVER</button>
															</div>

														</form>

														<hr class="hr" />

														<table id="datatable4" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
															<thead>
																<tr>
																	<th>CORREO</th>
																	<th>TIPO</th>
																	<th>ESTADO</th>
																</tr>
															</thead>
															<tbody>
																<?php
																$sql_correos = "EXEC [_SP_SJUD_CORREO_MANUAL_CONSULTA] $iddeudor";

																$stmt_correos = sqlsrv_query($conn, $sql_correos);
																//echo $sql ;	
																if ($stmt_correos === false) {
																	die(print_r(sqlsrv_errors(), true));
																}
																while ($correos = sqlsrv_fetch_array($stmt_correos, SQLSRV_FETCH_ASSOC)) {
																?>
																	<tr>
																		<td><?php echo $correos["CORREO"] ?></td>
																		<td><?php echo $correos["DESCRIPCION"] ?></td>
																		<td class="col-1">
																			<?php if ($correos["ID_ESTADO"] == 1) { ?>
																				<a data-toggle="tooltip" title="Cambia Estado" href="deudores_correos_cambiar.php?id=<?php echo $correos["ID_CORREO"] ?>&iddeudor=<?php echo $iddeudor ?>" class=" btn btn-icon btn-rounded btn-success"><i class="feather-24" data-feather="thumbs-up"></i></a>
																			<?php } else { ?>
																				<a data-toggle="tooltip" title="Cambia Estado" href="deudores_correos_cambiar.php?id=<?php echo $correos["ID_CORREO"] ?>&iddeudor=<?php echo $iddeudor ?>" class=" btn btn-icon btn-rounded btn-danger"><i class="feather-24" data-feather="thumbs-down">
																					<?php }; ?>
																		</td>
																	</tr>
																<?php }; ?>
															</tbody>
														</table>
													</div>
												</div>
											</div> <!--final del tab -->
										</div> <!--final de los tabs -->
									</div>
								</div>
							<?php }; ?>
						</div> <!-- end card-body -->
					</div> <!-- end card -->
				</div> <!-- end col -->
			</div> <!-- end row -->
		</div><!-- container -->
		<!-- end page content -->
		<?php include('footer.php'); ?>
	</div><!-- end page wrapper -->
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
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- App js -->
<script src="assets/js/app.js"></script>
<script src="plugins/datatables/spanish.js"></script>
<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>

<script language="javascript">
	$(document).ready(function() {
		$('#idprograma').select2();
	});
</script>

<script>
	$(document).ready(function() {
		$('#datatable2').DataTable({
			"language": {
				"url": "plugins/datatables/spanish.js"
			},
			"order": [
				[0, "asc"]
			]
		});
	});
</script>

<script>
	$(document).ready(function() {
		$("#region").on('change', function() {
			$("#region option:selected").each(function() {
				var id_region = $(this).val();

				$.post("get_comunas.php", {
					id_region: id_region
				}, function(data) {
					$("#comuna").html(data);
				});
			});
		});
	});
</script>

<script>
	$(document).ready(function() {
		// Guardar la pestaña activa en localStorage
		$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
			var tabId = $(e.target).attr('href');
			localStorage.setItem('activeTab', tabId);
		});

		// Restaurar la pestaña activa al cargar la página
		var activeTab = localStorage.getItem('activeTab');
		if (activeTab) {
			$('#pestañas a[href="' + activeTab + '"]').tab('show');
		}

		// Restablecer a los valores por defecto al hacer clic en el botón de reinicio
		$('#volverbtn').on('click', function() {
			localStorage.removeItem('activeTab'); // Elimina la clave activeTab de localStorage

			// Mostrar la primera pestaña por defecto
			$('#pestañas a:first').tab('show');
		});

		// Verificar el valor de op y restablecer pestañas si es necesario
		<?php if ($op == 5 || $op == 9) : ?>
			localStorage.removeItem('activeTab');
			$('#pestañas a:first').tab('show');
		<?php endif; ?>
	});
</script>

<script>
	function valida_envia1() {

		if (document.formulario1.tipodir.value == '0') {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe seleccionar un tipo de dirección.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}

		if (document.formulario1.region.value == '0') {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe seleccionar una región.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}

		if (document.formulario1.comuna.value == '0') {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe seleccionar una comuna.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}

		if (document.formulario1.direccion.value.trim() === '') {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe ingresar una dirección.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}

		// Si se seleccionó un tipo de dirección válido, enviar el formulario
		document.formulario1.submit();
	}
</script>

<script>
	function valida_envia2() {

		if (document.formulario2.numerotel.value.length != 9) {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'El número de teléfono debe tener 9 dígitos.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}

		if (document.formulario2.tipotel.value == '0') {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe seleccionar un tipo de telefono.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}

		if (document.formulario2.numerotel.value.trim() === '') {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe ingresar un telefono.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}

		// Si se seleccionó un tipo de dirección válido, enviar el formulario
		document.formulario2.submit();
	}
</script>

<script>
	function valida_envia3() {
		var correo = document.formulario3.correo.value.trim();

		if (correo === '') {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe ingresar un correo.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		} else if (!validarCorreo(correo)) {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'El correo no es válido.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}
		if (document.formulario3.tipocorreo.value == '0') {
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Debe seleccionar un tipo de correo.',
				showConfirmButton: false,
				timer: 2000
			});
			return false; // Detiene el envío del formulario
		}


		// Si el correo es válido, enviar el formulario
		document.formulario3.submit();
	}
</script>

<?php if ($op == 1) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'success',
				title: 'Dirección agregada.',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
<?php }; ?>

<?php if ($op == 2) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'success',
				title: 'Telefono agregado.',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
<?php }; ?>

<?php if ($op == 3) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'success',
				title: 'Correo agregado.',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
	</script>
	</div>
<?php }; ?>

<?php if ($op == 4) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'success',
				title: 'Estado actualizado',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
<?php }; ?>

<?php if ($op == 5) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'success',
				title: 'Deudor creado con éxito.',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
<?php }; ?>

<?php if ($op == 6) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Dirección ya existe.',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
<?php }; ?>

<?php if ($op == 7) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Teléfono ya existe.',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
<?php }; ?>

<?php if ($op == 8) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'error',
				title: 'Correo ya existe.',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
<?php }; ?>

<?php if ($op == 9) { ?>
	<div class="content">
		<script>
			Swal.fire({
				width: 600,
				icon: 'success',
				title: 'Datos actualizados.',
				showConfirmButton: false,
				timer: 3000,
			})
		</script>
	</div>
<?php }; ?>



<script language="javascript">
	document.getElementById("subcategoria").focus();
</script>

</html>