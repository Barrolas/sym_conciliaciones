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

$matched = 0;

$sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
        from [192.168.1.193].conciliacion.dbo.Transferencias_Recibidas_Hist";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$fecha_proceso = $row["FECHAPROCESO"];


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
    <link href="assets/css/loading.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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

        <!-- Pantalla de Carga -->
        <div id="loading-screen">
            <div class="spinner mr-3"></div>
            <p>Cargando...</p>
        </div>


        <!-- Page Content-->
        <div class="page-content" id="content">
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
                            <b>Transferencias pendientes</b>
                        </h3>
                    </div>
                    <div class="row mr-2">
                        <div class="col-12 mx-2">
                            <p>
                                Esta herramienta permite visualizar las transferencias aun no pareadas en el sistema. Las trasnferencias con
                                con botón celeste tienen match de que el rut ordenante es el mismo del rut deudor, en el caso de aquellas 
                                con botón verde, son transferencias con rut deudor indeterminado <b>(No en uso aun)</b>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row text-start justify-content-start justify-items-stretch pl-4 mb-3">
                            <div class="col-lg-6">
                                <label class="col-4" for="fecha_ultima_cartola">ÚLT ACTUALIZACIÓN</label>
                                <input type="text" class="form-control col-6" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                            </div>
                            <div class="col-lg-6">
                                <div class="col-lg-9">
                                    <label for="cuenta" class="col-4">CUENTA</label>
                                    <select name="cuenta" id="cuenta" class="form-control" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Todas las cuentas</option>
                                        <?php
                                        $sql_cuenta = "{call [_SP_CONCILIACIONES_LISTA_CUENTAS_BENEFICIARIOS]}";
                                        $stmt_cuenta = sqlsrv_query($conn, $sql_cuenta);

                                        if ($stmt_cuenta === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        while ($cuenta = sqlsrv_fetch_array($stmt_cuenta, SQLSRV_FETCH_ASSOC)) {
                                        ?>
                                            <option value="<?php echo $cuenta["CUENTA"] ?>"><?php echo $cuenta["CUENTA"] ?></option>
                                        <?php }; ?>
                                    </select>
                                </div>
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
                                        <th>NOMBRE</th>
                                        <th>MONTO</th>
                                        <th>TRANSACCION</th>
                                        <th>FECHA REC.</th>
                                        <th>CTA. BENEF</th>
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
                                            <td class="col-2">  <?php echo $transferencia["RUT"];         ?></td>
                                            <td class="col-auto"> <?php echo $transferencia["NOMBRE"];      ?></td>
                                            <td class="col-auto">$<?php echo $transferencia["MONTO"];       ?></td>
                                            <td class="col-auto"> <?php echo $transferencia["TRANSACCION"]; ?></td>
                                            <td class="col-auto"> <?php echo $transferencia["FECHA"];       ?></td>
                                            <td class="col-auto"><?php echo $transferencia["CUENTA"];      ?></td>
                                            <?php if ($transferencia["RUT_DEUDOR"] == NULL) { ?>
                                                <td class="col-1">

                                                    <a data-toggle="tooltip" title="Ver gestiones" href="conciliaciones_documentos.php?transaccion=<?php echo $transferencia["TRANSACCION"]; ?>&rut_ordenante=<?php echo $transferencia["RUT"]; ?>&cuenta=<?php echo $transferencia["CUENTA"]; ?>&matched=0" class="btn btn-icon btn-rounded btn-success ml-2"> 
                                                        <i class="feather-24" data-feather="plus"></i>
                                                    </a>
                                                </td>
                                            <?php } else { ?>
                                                <td class="col-1">
                                                    <a data-toggle="tooltip" title="Ver gestiones" href="conciliaciones_documentos.php?transaccion=<?php echo $transferencia["TRANSACCION"]; ?>&rut_ordenante=<?php echo $transferencia["RUT"]; ?>&rut_deudor=<?php echo $transferencia["RUT_DEUDOR"]; ?>&cuenta=<?php echo $transferencia["CUENTA"]; ?>&matched=1" class="btn btn-icon btn-rounded btn-info ml-2">
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

    <script>
        window.onload = function() {
            // Oculta la pantalla de carga y muestra el contenido principal
            document.getElementById('loading-screen').style.display = 'none';
            document.getElementById('content').style.display = 'block';
        };
    </script>


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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>



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
            order: [
                [4, 'desc'],
                [0, 'asc']
            ]

            //    columnDefs: [
            //        { targets: [3], visible: false } // Ocultar la columna Transaccion
            //    ]
        });

        // Function to apply filter based on the stored value
        $(document).ready(function() {
            var table = $('#datatable2').DataTable();

            function applyFilter() {
                var storedCuentaValue = sessionStorage.getItem('selected_cuenta');
                var storedPageLength = sessionStorage.getItem('page_length');

                if (storedCuentaValue && storedCuentaValue !== "0") {
                    $('#cuenta').val(storedCuentaValue).change();
                } else {
                    $('#cuenta').val("0").change(); // Reset to default
                }

                if (storedPageLength) {
                    table.page.len(parseInt(storedPageLength)).draw(); 
                }
            }

            $('#cuenta').on('change', function() {
                var filterValue = $(this).val();
                sessionStorage.setItem('selected_cuenta', filterValue);

                if (filterValue === "0") {
                    table.search('').columns().search('').draw();
                } else {
                    table.column(5).search(filterValue).draw();
                }
            });

            $('#datatable2_length select').on('change', function() {
                var pageLength = $(this).val();
                sessionStorage.setItem('page_length', pageLength);
                table.page.len(parseInt(pageLength)).draw(); // Cambia el número de resultados por página
            });

            // Apply the filter and page length on page load
            applyFilter();
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
    });



    <?php if ($op == 1) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Pareo realizado con éxito.',
            showConfirmButton: true
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