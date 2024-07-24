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
    <title>Conciliaciones</title>
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
                                        <li class="breadcrumb-item active">Conciliaciones</li>
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
                            <b>Conciliaciones Pareados</b>
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
                </div>
            </div>

            <div class="container-fluid px-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row text-start justify-content-start justify-items-stretch pl-4 mb-3">
                            <div class="col-lg-12">
                                <h4 class="mb-0">
                                    Transferencias
                                </h4>
                                <p class="text-secondary mb-0">
                                    Los campos RUT y NOMBRE hacen alusión al ordenante. El campo CUENTA hace alusión a la cuenta del beneficiario.
                                </p>

                            </div>
                        </div><!--end form-group-->
                    </div><!--end col-->
                </div>

                <div class="col-12 px-3">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable2" class="table dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>RUT ORD</th>
                                        <th>RUT DEUD</th>
                                        <th>MONTO</th>
                                        <th>TRANSACCION</th>
                                        <th>FECHA REC.</th>
                                        <th>CUENTA</th>
                                        <th>ASIGNAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "EXEC [_SP_CONCILIACIONES_TRANSFERENCIAS_PENDIENTES_LISTA]";
                                    $stmt = sqlsrv_query($conn, $sql);
                                    if ($stmt === false) {
                                        die(print_r(sqlsrv_errors(), true));
                                    }
                                    while ($transferencia = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    ?>
                                        <tr>
                                            <td class="col-2">    <?php echo $transferencia["RUT"];         ?></td>
                                            <td class="col-auto"> <?php echo $transferencia["NOMBRE"];      ?></td>
                                            <td class="col-auto">$<?php echo $transferencia["MONTO"];       ?></td>
                                            <td class="col-auto"> <?php echo $transferencia["TRANSACCION"]; ?></td>
                                            <td class="col-auto"> <?php echo $transferencia["FECHA"];       ?></td>
                                            <td class="col-auto"> <?php echo $transferencia["CUENTA"];      ?></td>
                                            <?php if ($transferencia["RUT_DEUDOR"] == NULL) { ?>
                                                <td class="col-1">
                                                    
                                                    <a data-toggle="tooltip" title="Ver gestiones" href="conciliaciones_documentos.php?transaccion=<?php echo $transferencia["TRANSACCION"]; ?>&rut_ordenante=<?php echo $transferencia["RUT"]; ?>" class="btn btn-icon btn-rounded btn-success ml-2">
                                                        <i class="feather-24" data-feather="plus"></i>
                                                    </a>
                                                </td>
                                            <?php } else { ?>
                                                <td class="col-1">
                                                    <a data-toggle="tooltip" title="Ver gestiones" href="conciliaciones_documentos_b.php?transaccion=<?php echo $transferencia["TRANSACCION"]; ?>&rut_ordenante=<?php echo $transferencia["RUT"]; ?>&rut_deudor=<?php echo $transferencia["RUT_DEUDOR"]; ?>" class="btn btn-icon btn-rounded btn-info ml-2">
                                                        <i class="feather-24" data-feather="folder"></i>
                                                    </a>
                                                </td>
                                            <?php }; ?>
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
            var table = $('#datatable2').DataTable({
            //    columnDefs: [
            //        { targets: [3], visible: false } // Ocultar la columna Transaccion
            //    ]
            });

            // Mantener un seguimiento de las transacciones que ya se han visto
            var seenTransactions = new Set();
            var transactionCounts = {};

            // Contar las transacciones
            table.rows().every(function() {
                var data = this.data();
                var transaccion = data[3]; // Índice de la columna Transaccion

                if (transactionCounts[transaccion]) {
                    transactionCounts[transaccion]++;
                } else {
                    transactionCounts[transaccion] = 1;
                }
            });

            // Ocultar filas duplicadas y agregar asterisco en el campo RUT de las filas agrupadas
            table.rows().every(function() {
                var data = this.data();
                var transaccion = data[3]; // Índice de la columna Transaccion
                var rut = data[0]; // Índice de la columna RUT

                if (seenTransactions.has(transaccion)) {
                    // Ocultar esta fila si la transacción ya ha sido vista
                    $(this.node()).hide();
                } else {
                    // Marcar esta transacción como vista
                    seenTransactions.add(transaccion);
                    // Solo agregar el asterisco si hay más de una fila para la transacción
                    if (transactionCounts[transaccion] > 1) {
                        data[0] = rut + ' <span><i class="far fa-bookmark text-primary pl-2"</i></span>'; // Añadir asterisco en rojo
                    }
                    this.data(data); // Actualizar la fila con el nuevo valor
                }
            });
        });    <?php if ($op == 1) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Conciliación realizada con éxito.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>

    <?php if ($op == 2) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Estado actualizado.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>

    <?php if ($op == 3) { ?>
        Swal.fire({
            width: 600,
            icon: 'error',
            title: 'Error: Ya existe una conciliación para esta transacción.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>

    <?php if ($op == 4) { ?>
        Swal.fire({
            width: 600,
            icon: 'error',
            title: 'Error: Los documentos seleccionados, ya están conciliados.',
            showConfirmButton: false,
            timer: 2000,
        });
    <?php } ?>
</script>

</html>