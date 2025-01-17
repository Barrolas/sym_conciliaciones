<?php
session_start();
include("permisos_adm.php");
include("funciones.php");
include("error_view.php");
include("conexiones.php");
validarConexion($conn);  
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

$idusuario = $_SESSION['ID_USUARIO'] ?? null;

if (!$idusuario) {
    mostrarError("No se pudo identificar al usuario. Por favor, inicie sesión nuevamente.");
}


$op = 0;
if (isset($_GET["op"])) {
    $op = $_GET["op"];
};

$matched = 0;

if ($sistema == 'desarrollo') {
    $sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
    from dbo.Transferencias_Recibidas_Hist";
} else {
    $sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
    from dbo.Transferencias_Recibidas_Hist";
}

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    mostrarError("Error al ejecutar la consulta 'ultima_cartola'.");
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$fecha_proceso = $row["FECHAPROCESO"];


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Cartola pareo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <link href="assets/css/filters.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                            <b>Conciliación</b>
                        </h3>
                    </div>
                    <div class="row mr-2">
                        <div class="col-12 mx-2">
                            <p>
                                Esta herramienta permite visualizar las transferencias que aún no han sido pareadas en el sistema. Las transferencias se identifican según el estado de su coincidencia a través de botones con distintos colores:
                            </p>
                            <ul>
                                <li><span class="text-info"><i class="feather-24 mr-2" data-feather="folder"></i></span><b>Rut coincidente:</b> Indica que la transferencia tiene coincidencia, ya que el RUT del ordenante coincide con el RUT del deudor.</li>
                                <li><span class="text-success"><i class="feather-24 mr-2" data-feather="plus"></i></span><b>Sin coincidencia:</b> Corresponde a transferencias donde el RUT del deudor no está determinado. Estas transferencias están disponibles para ser asignadas manualmente.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row text-start justify-content-start justify-items-stretch pl-4 mb-3">
                            <div class="col-lg-3">
                                <label class="col-9 mt-3" for="fecha_ultima_cartola">ÚLT ACTUALIZACIÓN</label>
                                <input type="text" class="form-control col-9" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                            </div>
                            <div class="col-lg-3 mt-3">
                                <div class="col-lg-9">
                                    <label for="cuenta_filter" class="col-3">CUENTA</label>
                                    <select name="cuenta_filter" id="cuenta_filter" class="form-control" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Todas las cuentas</option>
                                        <?php
                                        $sql_cuenta = "{call [_SP_CONCILIACIONES_LISTA_CUENTAS_BENEFICIARIOS]}";
                                        $stmt_cuenta = sqlsrv_query($conn, $sql_cuenta);

                                        if ($stmt_cuenta === false) {
                                            mostrarError("Error al ejecutar la consulta 'stmt_cuenta'.");
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
                                        <th class="font_mini_header">CUENTA</th>
                                        <th class="font_mini_header">FECHA</th>
                                        <th class="font_mini_header">DESCRIPCION</th>
                                        <th class="font_mini_header">N° DOCUMENTO</th>
                                        <th class="font_mini_header">MONTO</th>
                                        <th class="font_mini_header"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_conc    = "EXEC [_SP_CONCILIACIONES_CARTOLA_SALIDAS_LISTA]";
                                    $stmt_conc = sqlsrv_query($conn, $sql_conc);
                                    if ($stmt_conc === false) {
                                        mostrarError("Error al ejecutar la consulta 'stmt_conc'.");
                                    }
                                    while ($conciliacion = sqlsrv_fetch_array($stmt_conc, SQLSRV_FETCH_ASSOC)) {

                                        $cuenta         = $conciliacion['CUENTA'];
                                        $fecha          = $conciliacion['FECHA'];
                                        $descripcion    = $conciliacion['DESCRIPCION'];
                                        $n_documento    = $conciliacion['N_DOCUMENTO'];
                                        $monto_total    = $conciliacion['MONTO'];

                                    ?>
                                        <tr>
                                            <!-- Usamos la variable $remesa_cheque para mostrar solo un valor en la celda -->
                                            <td class="col-auto font_mini interes" id="interes"><?php echo $cuenta; ?></td>
                                            <td class="col-auto font_mini"><?php echo $fecha ?></td>
                                            <td class="col-auto font_mini"><?php echo $descripcion ?></td>
                                            <td class="col-auto font_mini"><?php echo $n_documento ?></td>
                                            <td class="col-auto font_mini">$<?php echo $monto_total; ?></td>
                                            <td class="col-1">
                                                <a data-toggle="tooltip" title="Parear" href="conciliaciones_cartola_pareo.php?n_doc=<?php echo $n_documento; ?>" class="btn btn-icon btn-rounded btn-warning ml-2">
                                                    <i class="feather-24" data-feather="minimize-2"></i>
                                                </a>
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

    $(document).ready(function() {

        var table = $('#datatable2').DataTable({
            "paging": false, // Deshabilita la paginación
            "searching": true, // Habilita la búsqueda
            "ordering": true, // Habilita el ordenamiento
            "order": [
                [4, 'desc']
            ], // Ordenar por la columna de índice 0 en orden ascendente
            "columnDefs": [{
                    "orderable": false,
                    "targets": [5]
                } // Deshabilitar el ordenamiento para la columna de índice 5
            ]
        });

        function applyFilters() {
            var storedCuentaValue = sessionStorage.getItem('selected_cuenta_3');
            var storedCanalValue = sessionStorage.getItem('selected_canal');
            var storedFiltroValue = sessionStorage.getItem('selected_diasmora');

            if (storedCanalValue && storedCanalValue !== "0") {
                $('#canal_filtro').val(storedCanalValue).change();
            } else {
                $('#canal_filtro').val("0").change(); // Reset to default
            }

            // Apply cuenta filter
            if (storedCuentaValue && storedCuentaValue !== "0") {
                $('#cuenta_filter').val(storedCuentaValue).change();
            } else {
                $('#cuenta_filter').val("0").change(); // Reset to default
            }


            // Apply dias_mora filter
            if (storedFiltroValue && storedFiltroValue !== "0") {
                $('#dias_mora').val(storedFiltroValue).change();
            } else {
                $('#dias_mora').val("0").change(); // Reset to default
            }
        }

        // Custom filter function for values >= 170
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var filterValue = $('#dias_mora').val();
                var columnValue = parseFloat(data[7]) || 0; // Convert the value to a number

                if (filterValue === "1") {
                    return columnValue >= 100; // Rango para dias de mora
                }
                return true; // Otherwise, show all rows
            }
        );

        // Add event listener to the cuenta select element
        $('#cuenta_filter').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_cuenta_3', filterValue);

            if (filterValue == "0") {
                table.column(0).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(0).search(filterValue).draw();
            }
        });

        $('#canal_filtro').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_canal', filterValue);

            if (filterValue == "0") {
                table.column(1).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(1).search(filterValue).draw();
            }
        });

        // Add event listener to the dias_mora select element
        $('#dias_mora').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_diasmora', filterValue);

            // Redraw table to apply the dias_mora filter
            table.draw();
        });

        // Apply filters on page load
        applyFilters();
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