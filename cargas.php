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
    <link href="plugins/dropify/css/dropify.min.css" rel="stylesheet">
    <!-- Plugins -->
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
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
                                        <li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
                                        <li class="breadcrumb-item active">Cargas</li>
                                    </ol>
                                </div><!--end col-->
                            </div><!--end col-->
                        </div><!--end row-->
                    </div><!--end page-title-box-->
                </div><!--end col-->
            </div><!--end row-->
            <!-- end page title end breadcrumb -->

            <div class="container-fluid mx-3">
                <div class="row">
                    <div class="col">
                        <h3>
                            <b>Gestión de Cargas</b>
                        </h3>
                    </div>
                    <div class="row mr-2">
                        <div class="col-12 mx-2">
                            <p>
                                Esta herramienta permite ingresar y gestionar cargas masivas de documentos asociados a deudores de clientes,
                                utilizando un formato pre-establecido con un archivo base en Excel.
                                Las <b>cargas</b> pueden ser revisadas para obtener el detalle de la cantidad de documentos que fueron leídos, cargados satisfactoriamente
                                y rechazados según los criterios de validación correspondientes (<strong><a href="cargas_crear.php">ver aquí</a></strong>), con detalle disponible para ambos casos.
                                También se permite deshabilitar cargas en caso de errores en la asignación a clientes con el botón de <b>ESTADO</b>.
                            </p>
                        </div>
                    </div>
                    <div class="row mr-2">
                        <div class="col-12 mx-2">
                            <p>
                                <b>Revise el paso a paso de como ingresar su carga aquí <a href="#"><i class="fa fa-question-circle p-1 text-primary" data-toggle="collapse" data-target="#demo" aria-hidden="true"></i></a></b>
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <div id="demo" class="container px-3 mt-2 collapse">
                <div class="row">
                    <!-- Tarjeta Paso 1 -->
                    <div class="col">
                        <div class="card bg-light rounded h-75 pb-3 " style="width: 100%;">
                            <div class="card-body">
                                <h5 class="card-title" style="text-transform: none;"><i class="fas fa-download pr-2 text-info"></i>Paso 1: Descargar formulario</h5>
                                <p class="card-text">Descargue el formulario necesario desde el botón <b>ARCHIVO BASE</b> para comenzar con el proceso de creación de carga masiva.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Paso 2 -->
                    <div class="col">
                        <div class="card bg-light rounded h-75 pb-3 " style="width: 100%;">
                            <div class="card-body">
                                <h5 class="card-title" style="text-transform: none;"> <i class="fas fa-upload pr-2 text-info"></i>Paso 2: Subir archivo</h5>
                                <p class="card-text">Una vez completado el formulario según las validaciones, haga clic en <b>CREAR CARGA</b> para continuar.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Paso 3 -->
                    <div class="col">
                        <div class="card bg-light rounded h-75 pb-3 " style="width: 100%;">
                            <div class="card-body">
                                <h5 class="card-title" style="text-transform: none;"><i class="fas fa-check-circle pr-2 text-info"></i>Paso 3: Revisión de detalles</h5>
                                <p class="card-text">Revise los documentos cargados y los rechazados para verificar errores u omisiones utilizando los botones correspondientes.</p>
                            </div>
                        </div>
                    </div>
                </div>            
            </div>

            <div class="container-fluid px-3">
                <div class="col-12 px-3">
                    <div class="card">
                        <div class="card-header" style="background-color: #0055a6">
                            <table width="100%" border="0" cellspacing="2" cellpadding="0">
                                <tbody>
                                    <tr style="background-color: #0055a6">
                                        <td align="right">
                                            <a align="right" href="assets/base.xlsx"><button type="button" class="btn btn-md btn-success"><i class="fa fa-plus"></i> ARCHIVO BASE</button></a>
                                            <a align="right" href="cargas_crear.php"><button type="button" class="btn btn-md btn-secondary"><i class="fa fa-plus"></i> CREAR CARGA</button></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div><!--end card-header-->
                        <div class="card-body">
                            <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>CLIENTE</th>
                                        <th>FECHA CARGA</th>
                                        <th>USUARIO</th>
                                        <th>LEIDOS</th>
                                        <th>RECHAZADOS</th>
                                        <th>CARGADOS</th>
                                        <th>ARCHIVO</th>
                                        <th>ESTADO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "EXEC [_SP_SJUD_CARGA_LISTA]";
                                    $stmt = sqlsrv_query($conn, $sql);
                                    if ($stmt === false) {
                                        die(print_r(sqlsrv_errors(), true));
                                    }
                                    while ($carga = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    ?>
                                        <tr>
                                            <td class="col-auto"> <?php echo $carga["ID_CARGA"]; ?></td>
                                            <td class="col-auto"> <?php echo $carga["NOMBRE"]; ?></td>
                                            <td class="col-auto"> <?php echo $carga["FUA"]->format('Y-m-d H:i:s'); ?></td>
                                            <td class="col-auto"> <?php echo $carga["USUARIO"]; ?></td>
                                            <td class="col-1"> <?php echo $carga["TOTAL_LEIDOS"]; ?></td>
                                            <td class="col-1">
                                                <a data-toggle="tooltip" title="Ver rechazados" href="cargas_rechazos_ver.php?id=<?php echo $carga["ID_CARGA"]; ?>" class="btn btn-icon btn-rounded btn-secondary">
                                                    <i class="feather-24" data-feather="eye"></i>
                                                    <span class="badge bg-danger rounded-pill"><?php echo $carga["TOTAL_RECHAZADOS"]; ?></span>
                                                </a>
                                            </td>
                                            <td class="col-1">
                                                <a data-toggle="tooltip" title="Ver carga" href="cargas_ver.php?id=<?php echo $carga["ID_CARGA"]; ?>" class="btn btn-icon btn-rounded btn-secondary">
                                                    <i class="feather-24" data-feather="eye"></i>
                                                    <span class="badge bg-danger rounded-pill"><?php echo $carga["TOTAL_CARGADOS"]; ?></span>
                                                </a>
                                            </td>
                                            <td class="col-1">
                                                <a data-toggle="tooltip" title="Descargar" href="archivos/<?php echo $carga["NOMBRE_ARCHIVO"]; ?>.xlsx" class="btn btn-icon btn-rounded btn-primary">
                                                    <i class="feather-24" data-feather="file"></i>
                                                </a>
                                            </td>
                                            <td class="col-1">
                                                <?php if ($carga["ID_ESTADO"] == 1) { ?>
                                                    <a data-toggle="tooltip" title="Cambia Estado" href="cargas_cambiar.php?id=<?php echo $carga["ID_CARGA"] ?>" class=" btn btn-icon btn-rounded btn-success"><i class="feather-24" data-feather="thumbs-up"></i></a>
                                                <?php } else { ?>
                                                    <a data-toggle="tooltip" title="Cambia Estado" href="cargas_cambiar.php?id=<?php echo $carga["ID_CARGA"] ?>" class=" btn btn-icon btn-rounded btn-danger"><i class="feather-24" data-feather="thumbs-down">
                                                        <?php }; ?>

                                            </td>

                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->
        </div><!-- container -->
        <?php include('footer.php'); ?>
    </div>
    <!-- end page content -->
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
    // Inicializar Feather Icons
    feather.replace();

    $(document).ready(function() {
        $('#idcliente').select2();
    });

    // Inicializar Tooltip de Bootstrap
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // DataTables Initialization
    $(document).ready(function() {
        $('#datatable2').dataTable({
            "aaSorting": [
                [2, "desc"]
            ], // Ordena la columna fecha en orden descendente
        });
    });

    <?php if ($op == 1) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Carga realizada con éxito.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>

    <?php if ($op == 3) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Estado actualizado.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>

    <?php if ($op == 5) { ?>
        Swal.fire({
            width: 600,
            icon: 'error',
            title: 'Error: Archivo inválido.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>

    <?php if ($op == 6) { ?>
        Swal.fire({
            width: 600,
            icon: 'error',
            title: 'Error: Archivo con formato erróneo.',
            showConfirmButton: false,
            timer: 2000,
        });
    <?php } ?>
</script>

</html>