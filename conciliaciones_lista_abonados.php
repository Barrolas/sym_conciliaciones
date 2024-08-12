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
                            <b>Abonos</b>
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
                            <div class="col-lg-3">
                                <label class="col-12" for="fecha_ultima_cartola">ÚLTIMA CARTOLA</label>
                                <input type="text" class="form-control col-8" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                            </div>
                            <div class="col-lg-3">
                                <label for="dias_mora" class="col-12">DIAS MORA</label>
                                <select name="dias_mora" id="dias_mora" class="form-control col-8" maxlength="50" autocomplete="off">
                                    <option value="0" selected>Mostrar todos</option>
                                    <option value="1">170 días o más</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <div class="col-lg-9">
                                    <label for="cuenta" class="col-4">CUENTA</label>
                                    <select name="cuenta" id="cuenta" class="form-control" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Mostrar todas</option>
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
                            <div class="col-lg-3">
                                <a href="conciliaciones_exportar_saldos.php">
                                    <button type="button" class="btn btn-primary waves-effect waves-light mt-4" disabled>
                                        EXPORTAR
                                    </button>
                                </a>
                            </div>
                        </div><!--end form-group-->
                    </div><!--end col-->
                </div>

                <div class="col-12 px-3">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <!--<th>
                                            <div class="d-flex flex-column align-items-center">
                                                <input class="mb-2" type="checkbox" id="select_all_checkbox1" onclick="handleMasterCheckbox(1)">
                                            </div>
                                        </th>
                                        !-->
                                        <th>CN</th>
                                        <th>CUENTA</th>
                                        <th>F. VENC</th>
                                        <th>F. RECEP</th>
                                        <th>TRANSACCIÓN</th>
                                        <th>RUT DEUD</th>
                                        <th>OPERACIÓN</th>
                                        <th>$ DOC</th>
                                        <th>ABONO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "EXEC [_SP_CONCILIACIONES_ABONOS_LISTA]";
                                    $stmt = sqlsrv_query($conn, $sql);
                                    if ($stmt === false) {
                                        die(print_r(sqlsrv_errors(), true));
                                    }
                                    while ($conciliacion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    ?>
                                        <tr>
                                            <!--<td class="col-1">
                                                <div class="form-check d-flex justify-content-center align-items-center">
                                                    <input class="form-check-input" type="checkbox" data-column="1" onclick="toggleRowCheckbox(this)">
                                                </div>
                                            </td> !-->
                                            <td class="col-auto"><?php echo $conciliacion["CANAL"]; ?></td>
                                            <td class="col-auto"><?php echo $conciliacion["CUENTA"]; ?></td>                                
                                            <td class="col-auto"><?php echo $conciliacion["F_VENC"]; ?></td>
                                            <td class="col-auto"><?php echo $conciliacion["F_REC"]; ?></td>
                                            <td class="col-auto"><?php echo $conciliacion["TRANSACCION"]; ?></td>
                                            <td class="col-auto"><?php echo trim($conciliacion["RUT_DEUDOR"]) . "-" . $conciliacion["DV"]; ?></td>
                                            <td class="col-auto"><?php echo $conciliacion["N_DOC"]; ?></td>
                                            <td class="col-auto">$<?php echo number_format($conciliacion["MONTO_DOCUMENTO"], 0, ',', '.'); ?></td>
                                            <td class="col-auto">$<?php echo number_format($conciliacion["ABONADO"], 0, ',', '.'); ?></td>
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


    <script>
        function handleMasterCheckbox(column) {
            // Determinar si el checkbox maestro está marcado o desmarcado
            var isChecked = $('#select_all_checkbox' + column).is(':checked');

            // Desmarcar todos los checkboxes maestros
            $('#select_all_checkbox1, #select_all_checkbox2').not('#select_all_checkbox' + column).prop('checked', false);

            // Obtener la instancia de DataTable
            var table = $('#datatable2').DataTable();

            // Marcar o desmarcar los checkboxes en la columna seleccionada
            table.rows({
                search: 'applied'
            }).nodes().to$().each(function() {
                var row = $(this);
                var checkboxes = row.find('input[type="checkbox"]');

                checkboxes.each(function() {
                    if ($(this).data('column') === column) {
                        this.checked = isChecked;
                    } else {
                        this.checked = false;
                    }
                });
            });

            // Actualizar el estado del encabezado después de cambiar los checkboxes en las filas
            updateHeaderCheckboxState();
        }

        function toggleRowCheckbox(checkbox) {
            var row = $(checkbox).closest('tr');
            var checkboxes = row.find('input[type="checkbox"]');

            // Marcar solo el checkbox clickeado y desmarcar los demás en la misma fila
            checkboxes.each(function() {
                if (this !== checkbox) {
                    this.checked = false;
                }
            });

            // Actualizar el estado de los checkboxes maestros
            updateHeaderCheckboxState();
        }

        function updateHeaderCheckboxState() {
            var table = $('#datatable2').DataTable();

            // Comprobar si todos los checkboxes de la columna 1 están marcados
            var allCheckedColumn1 = table.rows({
                search: 'applied'
            }).nodes().to$().find('input[data-column="1"]').length && table.rows({
                search: 'applied'
            }).nodes().to$().find('input[data-column="1"]').filter(':checked').length === table.rows({
                search: 'applied'
            }).nodes().to$().find('input[data-column="1"]').length;

            // Actualizar el estado de los checkboxes maestros
            $('#select_all_checkbox1').prop('checked', allCheckedColumn1);
        }
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

        $('#datatable2').on('change', '#select_all_checkbox1', function() {
            toggleAllCheckboxes(this, 1);
        });

        $('#datatable2').on('change', '#select_all_checkbox2', function() {
            toggleAllCheckboxes(this, 2);
        });

        var table = $('#datatable2').DataTable({
            order: [
                [1, 'asc']
            ],
            columnDefs: [{
                targets: 0
                //orderable: false
            }]
        });

        // Function to apply filters based on stored values
        function applyFilters() {
            var storedCuentaValue = localStorage.getItem('selected_cuenta');
            var storedFiltroValue = localStorage.getItem('selected_diasmora');

            // Apply cuenta filter
            if (storedCuentaValue && storedCuentaValue !== "0") {
                $('#cuenta').val(storedCuentaValue).change();
            } else {
                $('#cuenta').val("0").change(); // Reset to default
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
        $('#cuenta').on('change', function() {
            var filterValue = $(this).val();
            localStorage.setItem('selected_cuenta', filterValue);

            if (filterValue == "0") {
                table.column(2).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(2).search(filterValue).draw();
            }
        });

        // Add event listener to the dias_mora select element
        $('#dias_mora').on('change', function() {
            var filterValue = $(this).val();
            localStorage.setItem('selected_diasmora', filterValue);

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
            title: 'Conciliación realizada con éxito.',
            html: '<p>El proceso se completó satisfactoriamente. Puede revisar los detalles en "Conciliados".</p>',
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