<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$op = isset($_GET["op"]) ? $_GET["op"] : 0;
$idusuario = $_SESSION['ID_USUARIO'];
$id = $_GET["id"];

$sql = "EXEC [_SP_SJUD_CARGA_CONSULTA_CARGA] $id";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
	die(print_r(sqlsrv_errors(), true));
}

$carga = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
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
	<!-- Plugins -->
	<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body class="dark-sidenav">
	<!-- Left Sidenav -->
	<?php include("menu_izquierda.php"); ?>
	<!-- end left-sidenav-->

	<div class="page-wrapper w-75">
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
										<?php if ($_SESSION["TIPO"] == 1) { ?>
											<li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
										<?php } else { ?>
											<li class="breadcrumb-item"><a href="menu_gestor.php">Inicio</a></li>
										<?php }; ?>
										<li class="breadcrumb-item"><a href="cargas.php">Cargas</a></li>
										<li class="breadcrumb-item active">Cargados</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid px-3 mb-2">
					<div class="row">
						<div class="col">
							<h3>
								<b>Detalle cargados</b>
							</h3>
						</div>
						<div class="row">
							<div class="col-12 mx-2">
								<p>
									Este módulo presenta de manera organizada todos los registros cargados exitosamente mediante el proceso de carga masiva. Cada fila de la tabla muestra información sobre cada registro, facilitando una vista rápida y clara de los datos importados.
									Al presionar el icono <i class="fa fa-plus-circle text-primary p-1" aria-hidden="true"></i> en cualquier fila, se expande un panel que revela detalles adicionales del registro seleccionado. Esta funcionalidad permite explorar información más detallada sin perder la estructura visual intuitiva de la tabla principal.
								</p>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid px-2">
					<div class="col-12 px-3">
						<div class="card">
							<div class="card-header" style="background-color: #0055a6">
								<table width="100%" border="0" cellspacing="2" cellpadding="0">
									<tbody>
										<tr style="background-color:#0055a6">
											<td align="right">
												<a align="right" href="cargas.php?"><button type="button" class="btn btn-md btn-danger"><i class="fa fa-plus"></i> VOLVER</button></a>
											</td>
										</tr>
									</tbody>
								</table>
							</div><!--end card-header-->
							<div class="card-body">
								<form class="form-horizontal " id="validate" role="form" class="needs-validation" autocomplete="on">
									<div class="row text-start">
										<div class="col-md-12">
											<div class="form-group row text-center justify-content-center justify-items-stretch">
												<label for="categoria" class="col-lg-1 col-form-label">CARGA</label>
												<div class="col-lg-6">
													<input type="text" name="categoria" id="categoria" class="form-control" maxlength="50" autocomplete="off" value="<?= $carga["ID_CARGA"] . ' - ' . $carga["CLIENTE"] . ' - ' . $carga["FECHA_CARGA"] ?>" disabled />
												</div>
											</div>
										</div>
									</div>
								</form>

								<table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 50%; ">
									<thead>
										<tr>
											<th>RUT</th>
											<th>NOMBRE </th>
											<th>APELLIDO P </th>
											<th>APELLIDO M </th>
											<th>TIPO DOC </th>
											<th>N° DOC </th>
											<th>F_VCTO</th>
											<th>F_PROTESTO</th>
											<th>CAPITAL</th>
											<th>ABONO</th>
											<th>SALDO</th>
											<th>MOTIVO</th>
											<th>RUT GIRADOR</th>
											<th>APELLIDO P</th>
											<th>APELLIDO M</th>
											<th>NOMBRES</th>
											<th>BANCO</th>
											<th>SUCURSAL</th>
											<th>PLAZA</th>
											<th>CTA CTE</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$sql = "EXEC [_SP_SJUD_CARGA_DETALLE] $id";
										$stmt = sqlsrv_query($conn, $sql);
										if ($stmt === false) {
											die(print_r(sqlsrv_errors(), true));
										}
										while ($detalle = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
										?>
											<tr>
												<td><?php echo $detalle["RUT"]; ?></td>
												<td><?php echo $detalle["NOMBRES"]; ?></td>
												<td><?php echo $detalle["APELLIDO_PATERNO"]; ?></td>
												<td><?php echo $detalle["APELLIDO_MATERNO"]; ?></td>
												<td><?php echo $detalle["TIPO_DOC"]; ?></td>
												<td><?php echo $detalle["NUMERO_DOCUMENTO"]; ?></td>
												<td><?php echo $detalle["F_VCTO"]; ?></td>
												<td><?php echo $detalle["F_PROTESTO"]; ?></td>
												<td><?php echo $detalle["CAPITAL"]; ?></td>
												<td><?php echo $detalle["ABONOS"]; ?></td>
												<td><?php echo $detalle["TOTAL_MORA"]; ?></td>
												<td><?php echo $detalle["MOTIVO"]; ?></td>
												<td><?php echo $detalle["RUT_GIRADOR"]; ?></td>
												<td><?php echo $detalle["PATERNO_GIRADOR"]; ?></td>
												<td><?php echo $detalle["MATERNO_GIRADOR"]; ?></td>
												<td><?php echo $detalle["NOMBRE_GIRADOR"]; ?></td>
												<td><?php echo $detalle["N_BANCO"]; ?></td>
												<td><?php echo $detalle["N_SUCURSAL"]; ?></td>
												<td><?php echo $detalle["N_PLAZA"]; ?></td>
												<td><?php echo $detalle["CTA_CTE"]; ?></td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div><!-- end card-body -->
						</div><!-- end card -->
					</div><!-- end col -->
				</div><!-- end row -->
			</div><!-- container-fluid -->
		</div><!-- page-content -->
		<?php include('footer.php'); ?>
		<!-- page-wrapper -->
	</div>
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


<script>
	// Initialize Feather Icons
	feather.replace();

	// Initialize Bootstrap Tooltip
	$(function() {
		$('[data-toggle="tooltip"]').tooltip();
	});

	// DataTables Initialization
	$(document).ready(function() {
		$('#datatable2').DataTable({
			responsive: true,
			"aaSorting": [
				[2, "desc"]
			], // Sort by date column in descending order
		});
	});

	// Handle SweetAlert messages based on operation (op)
	<?php if ($op == 1) { ?>
		Swal.fire({
			width: 600,
			icon: 'success',
			title: 'SUB-CATEGORIA ACTUALIZADA',
			showConfirmButton: false,
			timer: 3000,
		});
	<?php } ?>

	<?php if ($op == 2) { ?>
		Swal.fire({
			width: 600,
			icon: 'success',
			title: 'SUB-CATEGORIA HABILITADA',
			showConfirmButton: false,
			timer: 3000,
		});
	<?php } ?>

	<?php if ($op == 3) { ?>
		Swal.fire({
			width: 600,
			icon: 'success',
			title: 'SUB-CATEGORIA DESHABILITADA',
			showConfirmButton: false,
			timer: 3000,
		});
	<?php } ?>

	<?php if ($op == 4) { ?>
		Swal.fire({
			width: 600,
			icon: 'success',
			title: 'SUB-CATEGORIA CREADA',
			showConfirmButton: false,
			timer: 3000,
		});
	<?php } ?>

	<?php if ($op == 5) { ?>
		Swal.fire({
			width: 600,
			icon: 'success',
			title: 'SUB-CATEGORIA ACTUALIZADA',
			showConfirmButton: false,
			timer: 3000,
		});
	<?php } ?>
</script>


</html>