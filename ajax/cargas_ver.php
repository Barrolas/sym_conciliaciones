<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$op = 0;
if (isset($_GET["op"])) {
	$op=$_GET["op"];
};
$id = $_GET["id"];

$sql       = "select ID_CARGA,date_format(FECHA_CARGA, '%d-%m-%Y %H:%m:%s') as FECHA_CARGA 
				from los_cargas
				where ID_CARGA = '$id'"; 
$resultado = $conn->query($sql);
$carga = $resultado->fetch();
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?= $sistema?></title>
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
        	<?php include("menu_izquierda.php");?>
        <!-- end left-sidenav-->
        

        <div class="page-wrapper">
            <!-- Top Bar Start -->
				<?php include("menu_top.php");?>
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
                                           <?php if ($_SESSION["TIPO"] == 1) {?>
                                            	<li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
											<?php } else {?>
                                            	<li class="breadcrumb-item"><a href="menu_gestor.php">Inicio</a></li>
											<?php } ;?>
                                           	<li class="breadcrumb-item"><a href="cargas.php">Cargas</a></li>
                                            <li class="breadcrumb-item active">Gestión de Cargas</li>
                                        </ol>
                                    </div><!--end col-->
                                 </div><!--end col-->  
                                </div><!--end row-->                                                              
                            </div><!--end page-title-box-->
                        </div><!--end col-->
                 </div><!--end row-->
                 <!-- end page title end breadcrumb -->
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header" style="background-color:#1D77F9">
									<table width="100%" border="0" cellspacing="2" cellpadding="0">
									  <tbody>
										<tr style="background-color:#1D77F9">
										  <td>
										  </td>
										  <td align="right">
											 
											 <a aling="rigth" href="cargas.php?"><button type="button" class="btn btn-md btn-danger"><i class="fa fa-plus"></i> VOLVER</button></a></td>
										</tr>
									  </tbody>
									</table>
                                </div><!--end card-header-->
                                <div class="card-body"> 
									<form class="form-horizontal " id="validate" role="form" class="needs-validation" autocomplete="on">	

										<div class="row">
											<div class="col-md-6">
												<div class="form-group row">
													<label for="categoria" class="col-lg-3 col-form-label">CARGAS</label>
													<div class="col-lg-9">
														<input type="text" name="categoria" id="categoria" class="form-control" maxlength="50"  autocomplete="off" value="<?= $carga["ID_CARGA"].' - '.$carga["FECHA_CARGA"]?>" disabled/> 
													</div>
												</div><!--end form-group-->
											</div><!--end col-->																							
										</div><!--end row-->										
										
										
								</form>

                                    <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>NOMINA</th>
                                            <th>RUT</th>
											<th>NOMBRE </th>
                                            <th>CONTENCION</th>
                                            <th>SALDO</th>
											<th>CUOTA</th>
                                            <th>F_VCTO_MENOR</th>
											<th>USUARIO</th>
											<th>SUBCLIENTE</th>
											<th>ESTADO</th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php 																						 
										$sql ="SELECT `ID_CARGA_DETALLE`, `ID_CARGA`, `USUARIO`, `NOMINA`, CD.`RUT`, `DV`, CD.`NOMBRE`, `CONTENCION`, `SALDO`, `CUOTA`, DATE_FORMAT(`F_VCTO_MENOR`,'%d-%m-%Y') as `F_VCTO_MENOR`, DATE_FORMAT(`F_CASTIGO`,'%d-%m-%Y') as  `F_CASTIGO`, `DIRECCION`, `COMUNA`, `REGION`, `USUARIO_ASIGNADO`, `CAMPANA`, `RUTCLIENTE`,CS.NOMBRE AS SUBCLIENTE 
										FROM `los_cargas_detalle` CD
										left join los_clientes_servicio CS ON CS.RUT = CD.RUTCLIENTE
												where ID_CARGA = $id";		 
										// echo $sql ;	
										$resultado = $conn->query($sql);
										while($detalle   = $resultado->fetch()) {	
											$sql2 ="SELECT * 
														FROM `los_cargas_rechazados` CR 
														join los_cargas_rechazados_tipo CRT ON CRT.ID_TIPO_RECHAZO=CR.ID_TIPO_RECHAZO
														WHERE CR.ID_CARGA_DETALLE= ". $detalle["ID_CARGA_DETALLE"];
											//echo $sql ;	
											$resultado2 	= $conn->query($sql2);
											$rechazado    = $resultado2->fetch();
										?>
                                        <tr>
                                            
											<td><?php echo $detalle["NOMINA"]?></td>
											<td><?php echo $detalle["RUT"].'-'.$detalle["DV"]?></td>
											<td><?php echo $detalle["NOMBRE"]?></td>
											<td><?php echo '$'.my_number_format($detalle["CONTENCION"],'','.')?></td>
											<td><?php echo '$'.my_number_format($detalle["SALDO"],'','.')?></td>
											<td><?php echo '$'.my_number_format($detalle["CUOTA"],'','.')?></td>
											<td><?php echo $detalle["F_VCTO_MENOR"]?></td>
											
											<td><?php echo $detalle["USUARIO_ASIGNADO"]?></td>
											<td><?php echo $detalle["SUBCLIENTE"]?></td>
											<td>
												<?php 
													if ($rechazado) {
														echo $rechazado["DESCRIPCION"];
													};
												?>
											</td>
                                        </tr>
										<?php };?>
                                        </tbody>
                                    </table>        
                                </div>
                            </div>
                        </div> <!-- end col -->
                    </div> <!-- end row -->

                </div><!-- container -->

                 <?php include('footer.php');?>
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
	
<script language="javascript">
//DataTables Initialization
		$(document).ready(function() {
			$('#datatable2').dataTable(
			{
				
				"aaSorting": [[ 2, "asc" ]],

			}
			);
		});	
</script>	
    </body>
<?php if ($op == 1) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'SUB-CATEGORIA ACTUALIZADA',
						  showConfirmButton: false,
						  timer: 3000,
						})
					</script>				
				</div>
<?php }; ?>

<?php if ($op == 2) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'SUB-CATEGORIA HABILITADA',
						  showConfirmButton: false,
						  timer: 3000,
						})
					</script>				
				</div>
<?php }; ?>

<?php if ($op == 3) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'SUB-CATEGORIA DESHABILITADA',
						  showConfirmButton: false,
						  timer: 3000,
						})
					</script>				
				</div>
<?php }; ?>

<?php if ($op == 4) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'SUB-CATEGORIA CREADA',
						  showConfirmButton: false,
						  timer: 3000,
						})
					</script>				
				</div>
<?php }; ?>

<?php if ($op == 5) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'SUB-CATEGORIA ACTUALIZADA',
						  showConfirmButton: false,
						  timer: 3000,
						})
					</script>				
				</div>
<?php }; ?>
</html>