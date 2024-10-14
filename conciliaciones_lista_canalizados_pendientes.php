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
    <title>Canalizados</title>
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
                                        <li class="breadcrumb-item active">Canalizados</li>
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
                            <b>Canalizados pendientes</b>
                        </h3>
                    </div>
                    <div class="row mr-2">
                        <div class="col-12 mx-2">
                            <p>
                                En este módulo se pueden visualizar los documentos que, aunque ya han sido cubiertos
                                en su totalidad, permanecen en estado pendiente porque fueron saldados mediante
                                un préstamo otorgado por la empresa. Hasta que dicho préstamo no sea pagado en su
                                totalidad, estos documentos seguirán apareciendo en la lista y no podrán ser
                                procesados ni avanzados dentro del flujo normal de conciliación.
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
                                <label class="col-12" for="fecha_ultima_cartola">ÚLT ACTUALIZACIÓN</label>
                                <input type="text" class="form-control col-8" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                            </div>
                            <div class="col-lg-3">
                                <div class="col-lg-9">
                                    <label for="canal_filtro" class="col-4">CANAL</label>
                                    <select name="canal_filtro" id="canal_filtro" class="form-control" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Mostrar todos</option>
                                        <?php
                                        $sql_canal = "{call [_SP_CONCILIACIONES_TIPOS_CANALIZACIONES_LISTA]}";
                                        $stmt_canal = sqlsrv_query($conn, $sql_canal);

                                        if ($stmt_canal === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        while ($canal = sqlsrv_fetch_array($stmt_canal, SQLSRV_FETCH_ASSOC)) {
                                        ?>
                                            <option value="<?php echo substr($canal['DESCRIPCION'], 0, 6); ?>"><?php echo $canal["DESCRIPCION"] ?></option>
                                        <?php }; ?>
                                    </select>
                                </div>
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
                                <a href="conciliaciones_exportar_canalizados_pendientes.php">
                                    <button type="button" class="btn btn-primary waves-effect waves-light mt-4" id="exportar_btn" disabled>
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
                                        <th>CANAL</th>
                                        <th>TRANSACCION</th>
                                        <th>CUENTA</th>
                                        <th>RUT CTE</th>
                                        <th>RUT DEU</th>
                                        <th>F. VENC</th>
                                        <th>OPERACIÓN</th>
                                        <th>TIPO</th>
                                        <th>MONTO</th>
                                        <th>DIFERENCIA</th>
                                        <th>ELIMINAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "EXEC [_SP_CONCILIACIONES_CANALIZADOS_DIFERENCIAS_LISTA]";
                                    $stmt = sqlsrv_query($conn, $sql);
                                    if ($stmt === false) {
                                        die(print_r(sqlsrv_errors(), true));
                                    }
                                    while ($conciliacion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

                                        $diferencia_doc     = $conciliacion['DIFERENCIA'];
                                        $id_documento       = $conciliacion['ID_DOCDEUDORES'];

                                        $sql_psistema = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_ID_DOC](?)}";
                                        $params_psistema = array(
                                            array($id_documento,        SQLSRV_PARAM_IN),
                                        );
                                        $stmt_psistema = sqlsrv_query($conn, $sql_psistema, $params_psistema);
                                        if ($stmt_psistema === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        // Procesar resultados de la consulta de detalles
                                        $psistema = sqlsrv_fetch_array($stmt_psistema, SQLSRV_FETCH_ASSOC);

                                        // Consulta para obtener el monto de abonos (solo si el estado no es '1')
                                        $sql_monto = "{call [_SP_CONCILIACIONES_MOVIMIENTO_DEBE](?)}";
                                        $params_monto = array($id_documento);
                                        $stmt_monto = sqlsrv_query($conn, $sql_monto, $params_monto);
                                        if ($stmt_monto === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        // Procesar resultados de la consulta de detalles
                                        $monto_consulta = sqlsrv_fetch_array($stmt_monto, SQLSRV_FETCH_ASSOC);

                                        // Consulta para obtener el monto de abonos (solo si el estado no es '1')
                                        $sql4 = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ID](?)}";
                                        $params4 = array($id_documento);
                                        $stmt4 = sqlsrv_query($conn, $sql4, $params4);
                                        if ($stmt4 === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        // Procesar resultados de la consulta de detalles
                                        $detalles = sqlsrv_fetch_array($stmt4, SQLSRV_FETCH_ASSOC);

                                        // Consulta para obtener el estado del documento
                                        $sql5 = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ESTADO](?)}";
                                        $params5 = array($id_documento);
                                        $stmt5 = sqlsrv_query($conn, $sql5, $params5);
                                        if ($stmt5 === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        $estado_pareo_text = 'N/A'; // Valor por defecto
                                        while ($estados = sqlsrv_fetch_array($stmt5, SQLSRV_FETCH_ASSOC)) {
                                            $estado_pareo = isset($estados['ID_ESTADO']) ? $estados['ID_ESTADO'] : NULL;
                                            switch ($estado_pareo) {
                                                case '1':
                                                    $estado_pareo_text = 'CONC';
                                                    break;
                                                case '2':
                                                    $estado_pareo_text = 'ABON';
                                                    break;
                                                case '3':
                                                    $estado_pareo_text = 'PEND';
                                                    break;
                                            }
                                        }

                                        $cte_rut        = $detalles["RUT_CLIENTE"];
                                        $deud_rut       = $detalles["RUT_DEUDOR"];
                                        $f_venc         = $detalles["F_VENC"];
                                        $operacion      = $detalles["N_DOC"];
                                        $transaccion    = $psistema["TRANSACCION"];
                                        $id_doc         = $detalles["ID_DOCDEUDORES"];

                                    ?>
                                        <tr>
                                            <td class="col-auto"><?php echo mb_substr(isset($detalles["CANALIZACION"]) ? $detalles["CANALIZACION"] : 'N/A', 0, 6); ?></td>
                                            <td class="col-auto"><?php echo isset($transaccion) ? $transaccion : 'Sin transacción'; ?></td>
                                            <td class="col-auto"><?php echo isset($detalles["CUENTA"]) ? $detalles["CUENTA"] : 'Sin cuenta'; ?></td>
                                            <td class="col-auto"><?php echo isset($detalles["RUT_CLIENTE"]) ? $detalles["RUT_CLIENTE"] : 'Sin RUT'; ?></td>
                                            <td class="col-auto"><?php echo isset($detalles["RUT_DEUDOR"]) ? $detalles["RUT_DEUDOR"] : 'Sin RUT'; ?></td>
                                            <td class="col-auto">
                                                <?php echo isset($detalles["F_VENC"]) && $detalles["F_VENC"] instanceof DateTime ? $detalles["F_VENC"]->format('Y/m/d') : 'Fecha no disponible'; ?>
                                            </td>
                                            <td class="col-auto"><?php echo isset($detalles["N_DOC"]) ? $detalles["N_DOC"] : 'Sin documento'; ?></td>
                                            <td class="col-auto"><?php echo isset($estado_pareo_text) ? $estado_pareo_text : 'Estado no definido'; ?></td>
                                            <td class="col-auto">
                                                $<?php echo isset($monto_consulta["MONTO"]) ? number_format($monto_consulta["MONTO"], 0, ',', '.') : '0'; ?>
                                            </td>
                                            <td class="col-auto">
                                                $<?php echo isset($diferencia_doc) ? number_format($diferencia_doc, 0, ',', '.') : '0'; ?>
                                            </td>
                                            <td class="col-1">
                                                <?php
                                                // Convertir DateTime a cadena en el formato deseado
                                                $f_venc = isset($detalles["F_VENC"]) && $detalles["F_VENC"] instanceof DateTime ? $detalles["F_VENC"]->format('Y-m-d') : 'Fecha no disponible';
                                                ?>
                                                <a data-toggle="tooltip" title="Eliminar" href="conciliaciones_canalizaciones_eliminar.php?r_cl=<?php echo $cte_rut; ?>&r_dd=<?php echo $deud_rut; ?>&f_venc=<?php echo urlencode($f_venc); ?>&ndoc=<?php echo urlencode($operacion); ?>&transaccion=<?php echo $transaccion; ?>&id_doc=<?php echo $id_doc; ?>" class="btn btn-icon btn-rounded btn-danger">
                                                    <i class="feather-24" data-feather="x"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php   };

                                    ?>
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
                [0, 'asc'],
                [4, 'asc'],
                [3, 'asc']
            ],
            columnDefs: [{
                targets: 0
                //orderable: false
            }]
        });

        var rowCount = table.rows().count();
        var exportButton = document.getElementById('exportar_btn');

        if (rowCount > 0) {
            exportButton.disabled = false;
        } else {
            exportButton.disabled = true;
        }


        // Function to apply filters based on stored values
        function applyFilters() {
            var storedCuentaValue = sessionStorage.getItem('selected_cuenta_4');
            var storedCanalValue = sessionStorage.getItem('selected_canal');
            var storedFiltroValue = sessionStorage.getItem('selected_diasmora');

            if (storedCanalValue && storedCanalValue !== "0") {
                $('#canal_filtro').val(storedCanalValue).change();
            } else {
                $('#canal_filtro').val("0").change(); // Reset to default
            }

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
            sessionStorage.setItem('selected_cuenta_4', filterValue);

            if (filterValue == "0") {
                table.column(1).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(1).search(filterValue).draw();
            }
        });

        $('#canal_filtro').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_canal', filterValue);

            if (filterValue == "0") {
                table.column(0).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(0).search(filterValue).draw();
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
            title: 'Conciliación realizada con éxito.',
            html: '<p>El proceso se completó satisfactoriamente. Puede revisar los detalles en "Conciliados".</p>',
            showConfirmButton: true
        });
    <?php } ?>

    <?php if ($op == 2) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Canalizacion eliminada.',
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