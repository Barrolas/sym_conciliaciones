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
											  <a aling="rigth" href="cargas_crear.php"><button type="button" class="btn btn-md btn-primary"><i class="fa fa-plus"></i> CREAR CARGA</button></a></td>
										</tr>
									  </tbody>
									</table>
                                </div><!--end card-header-->
                                <div class="card-body">  

                                    <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
											<th>FECHA CARGA</th>
											<th>LEIDOS</th>
											<th>CARGADOS</th>
											<th>RECHAZADOS</th>
                                            <th>USUARIO</th>
                                            <th>VER CARGA</th>											
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php 
										$sql ="SELECT C.ID_CARGA,date_format(C.FECHA_CARGA, '%d-%m-%Y %H:%m:%s') AS FECHA,C.ID_CARGA,CONCAT(U.NOMBRE,' ',U.APELLIDO) AS USUARIO
												FROM `los_cargas` C 
												join los_usuarios U ON U.ID_USUARIO=C.ID_USUARIO
												order by C.ID_CARGA DESC
												limit 15";
										//echo $sql ;	
										$resultado = $conn->query($sql);
										while($usuario   = $resultado->fetch()) {
											$sql2 = "SELECT COUNT(*) as TOTAL 
														FROM `los_cargas` C 
														join los_cargas_detalle CD ON CD.ID_CARGA = C.ID_CARGA
														join los_usuarios_gestiones UG ON UG.ID_CARGA_DETALLE = CD.ID_CARGA_DETALLE
														WHERE C.ID_CARGA=".$usuario["ID_CARGA"]." and UG.ESTADO=1"; 
														
											$resultado2 = $conn->query($sql2);
											$pendientes = $resultado2->fetch();
											
											$sql2 = "SELECT COUNT(*) as TOTAL 
														FROM `los_cargas` C 
														join los_cargas_detalle CD ON CD.ID_CARGA = C.ID_CARGA
														join los_usuarios_gestiones UG ON UG.ID_CARGA_DETALLE = CD.ID_CARGA_DETALLE
														WHERE C.ID_CARGA=".$usuario["ID_CARGA"]." and UG.ESTADO=2"; 
														
											$resultado2 = $conn->query($sql2);
											$gestionados = $resultado2->fetch();
											
											$sql2 = "SELECT COUNT(*) as TOTAL 
														FROM `los_cargas` C 
														join los_cargas_detalle CD ON CD.ID_CARGA = C.ID_CARGA
														join los_usuarios_gestiones UG ON UG.ID_CARGA_DETALLE = CD.ID_CARGA_DETALLE
														WHERE C.ID_CARGA=".$usuario["ID_CARGA"]." and UG.ESTADO=4"; 
														
											$resultado2 = $conn->query($sql2);
											$debaja 	= $resultado2->fetch();
											
											$sql2 = "SELECT COUNT(*) as TOTAL 
														FROM `los_cargas` C 
														join los_cargas_detalle CD ON CD.ID_CARGA = C.ID_CARGA
														join los_usuarios_gestiones UG ON UG.ID_CARGA_DETALLE = CD.ID_CARGA_DETALLE
														WHERE C.ID_CARGA=".$usuario["ID_CARGA"]." and UG.ESTADO=3"; 
														
											$resultado2 = $conn->query($sql2);
											$bajados = $resultado2->fetch();
											
											$sql2 = "select COUNT(*) AS TOTAL 
														from los_cargas_rechazados CR WHERE
														ID_CARGA_DETALLE IN (
														SELECT CD.ID_CARGA_DETALLE
														from los_cargas_detalle CD 
														WHERE CD.ID_CARGA=".$usuario["ID_CARGA"].");"; 
														
											$resultado2 = $conn->query($sql2);
											$rechazados = $resultado2->fetch();
											
											$sql2 = "SELECT COUNT(*) as TOTAL FROM `los_cargas_detalle` WHERE ID_CARGA=".$usuario["ID_CARGA"]; 
														
											$resultado2 = $conn->query($sql2);
											$cargados 	= $resultado2->fetch();
										?>
                                        <tr>
											<td><?php echo $usuario["ID_CARGA"]?></td>
                                            <td><?php echo $usuario["FECHA"]?></td>   
											<td><?php echo $cargados["TOTAL"] ?></td>
											
											<td><?php echo $cargados["TOTAL"]  - $rechazados["TOTAL"]?></td>
											<td><?php echo $rechazados["TOTAL"]?></td>
											<td><?php echo $gestionados["TOTAL"]?></td>
											<td><?php echo $bajados["TOTAL"]?></td>
											<td><?php echo $debaja["TOTAL"]?></td>
                                            <td><?php echo $usuario["USUARIO"]?></td>
											<td>
												<a data-toggle="tooltip" title="Ver Carga" href="cargas_ver.php?id=<?php echo $usuario["ID_CARGA"]?>" class=" btn btn-icon btn-rounded btn-secondary"><i class="feather-24" data-feather="eye"></i>&nbsp;&nbsp;<span class="badge badge-danger badge-pill noti-icon-badge"><?= $cargados["TOTAL"];?></span></a> 
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
				
				"aaSorting": [[ 0, "desc" ]],

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
						  title: 'USUARIO ACTUALIZADO',
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
						  title: 'USUARIO HABILITADO',
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
						  title: 'USUARIO DESHABILITADO',
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
						  title: 'CARGA REALIZADA',
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
						  icon: 'error',
						  title: 'ARCHIVO INVALIDO',
						  showConfirmButton: false,
						  timer: 3000,
						})
					</script>				
				</div>
<?php }; ?>

<?php if ($op == 6) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'error',
						  title: 'ARCHIVO ERRONEO',
						  showConfirmButton: false,
						  timer: 3000,
						})
					</script>				
				</div>
<?php }; ?>
</html>