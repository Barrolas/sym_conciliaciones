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
	$op=$_GET["op"];
};

$perfil1 = 0;
if (isset($_GET["perfil1"])) {
	$perfil1=$_GET["perfil1"];
};

$modulo1 = 0;
if (isset($_GET["perfil1"])) {
	$perfil1=$_GET["perfil1"];
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
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


        <!-- Plugins -->
		<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        

        <script language="javascript">
		 function Optionselection(select) {
				window.location.href = "perfiles_modulos.php?perfil1=" + select;
				//alert("Option Chosen by you is " + chosen.value);
		}
		</script>
        	
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
                                            <li class="breadcrumb-item active">Gestión de Perfiles-Modulos</li>
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
                                </div><!--end card-header-->
                                
                                <div class="row">
                                    <div class="col-lg-9 offset-md-2">
                                        <div class="card">

                                            <div class="card-body"> 
                                                <form method="post" action="perfiles_modulos_insertar.php?perfil=<?php echo $perfil1; ?>" class="form-horizontal " id="validate" role="form" id="formulario" name="formulario" class="needs-validation" autocomplete="on" onsubmit="return valida_envia();return false;">	
                                                    
                                                                <!-- PERFIL -->

                                                    <div class="row"> 
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <label for="tipo_usuario" class="col-lg-3 col-form-label">PERFIL</label>
                                                                <div class="col-lg-5">
                                                                    <select name="idperfil" id="idperfil" class="select2 form-control mb-3 custom-select" style="width: 100%; height:36px;">
                                                                        <option value="0">Seleccione un Perfil</option>
                                                                    <?php 
                                                                        $sql = "EXEC [_SP_SJUD_PERFIL_LISTA]";

                                                                        $stmt = sqlsrv_query( $conn, $sql );
                                                                        //echo $sql ;	
                                                                        if( $stmt === false) {	
                                                                            die( print_r( sqlsrv_errors(), true) );
                                                                        }                                        
                                                                        while( $perfil = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                                                                    ?>
                                                                        <option <?php if ($perfil1 == $perfil["ID_PERFIL"]) {echo " selected ";};?> value="<?php echo $perfil["ID_PERFIL"]?>"><?php echo $perfil["DESCRIPCION"]?></option>
                                                                    <?php };?>	
                                                                    </select> 
                                                                </div>
                                                            </div><!--end form-group-->
                                                        </div><!--end col-->												
                                                    </div><!--end row-->
                                                    
                                                    

                                                    <!-- MODULOS -->

                                                    <div class="row">
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <label for="tipo_usuario" class="col-lg-3 col-form-label">MODULOS</label>
                                                                <div class="col-lg-5">
                                                                    <select name="idmodulo" id="idmodulo" class="select2 form-control mb-3 custom-select" style="width: 100%; height:36px;">
                                                                       

                                                                    </select> 
                                                                </div>
                                                            </div><!--end form-group-->
                                                        </div><!--end col-->												
                                                    </div><!--end row-->
                                                   
                                                    
                                                    

                                                <div class="mt-3" align="center">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">GUARDAR</button>
                                                    <button type="button" onClick="javascript:location.href='perfiles_modulos_insertar.php?perfil=<?php echo $perfil1; ?>';" class="btn btn-danger waves-effect waves-light">CANCELAR</button>
                                                </div>
                                            </form>		
                                            </div> <!-- end card-body -->
                                        </div> <!-- end card -->                                       
                                    </div> <!-- end col -->
                                </div> <!-- end row -->


                                <div class="card-body">  

                                    <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>ID</th>											
                                            <th>DESCRIPCION</th>
                                            <th>ELIMINAR</th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php 
										$sql = "EXEC [_SP_SJUD_PERFIL_MODULO_LISTA] $perfil1";

                                        $stmt = sqlsrv_query( $conn, $sql );
										//echo $sql ;	
										if( $stmt === false) {	
                                            die( print_r( sqlsrv_errors(), true) );
                                        }                                        
                                        while( $modulo = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
											
										?>

                                        <tr>
                                            <td><?php echo $modulo["ID_MODULO"]?></td>											
                                            <td><?php echo $modulo["DESCRIPCION"]?></td>
                                            <td>
													<a data-toggle="tooltip" title="Eliminar" href="perfiles_modulos_eliminar.php?perfil=<?php echo $perfil1; ?>&id=<?php echo $modulo["ID_MODULO"]?>" class=" btn btn-icon btn-rounded btn-danger"><i class="feather-24" data-feather="x">											    
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

        <script>
$(document).ready(function() {
    $('#idperfil').on('change', function() {
        var key = $(this).val();		
        var dataString = 'modulo='+key;        
        
        // Actualiza el select de módulos
	$.ajax({
            type: "POST",
            url: "perfiles_modulos_obtener.php",
            data: dataString,
            success: function(data) {                
                $('#idmodulo').fadeIn(1000).html(data);
            }
        });

        // Actualiza la datatable
        $.ajax({
            type: "POST",
            url: "perfiles_modulos_obtener_tabla.php",
            data: { perfil: key },
            success: function(data) {
                var table = $('#datatable2').DataTable();
                table.clear().draw();
                table.rows.add(data).draw();
            }
        });
    });

    // Inicializa la datatable
    $('#datatable2').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "perfiles_modulos_obtener_tabla.php",
            "type": "POST",
            "data": function ( d ) {
                d.perfil = $('#idperfil').val();
            }
        },
        "columns": [
            { "data": "ID_MODULO" },
            { "data": "DESCRIPCION" },
            { "data": "ELIMINAR" }
        ]
    });
}); 
</script>


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
	
	
    </body>
<?php if ($op == 1) {?>
				<div class="content">
					<script>
						Swal.fire({
						  width: 600,
						  icon: 'success',
						  title: 'PERFIL ACTUALIZADO',
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
						  title: 'PERFIL HABILITADO',
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
						  title: 'ESTADO DE PERFIL ACTUALIZADO',
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
						  title: 'PERFIL CREADO',
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
						  title: 'PERFIL ACTUALIZADO',
						  showConfirmButton: false,
						  timer: 3000,
						})
					</script>				
				</div>
<?php }; ?>
</html>