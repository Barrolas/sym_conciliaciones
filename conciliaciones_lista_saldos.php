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

$sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
        from Transferencias_Recibidas_Hist";

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
    <style>
        @media (min-width: 1000px) and (max-width: 1299px) {
            .font_mini {
                font-size: 12px !important;
            }

            .font_mini_header {
                font-size: 11px !important;
            }

            .card_width {
                width: 90% !important;
                overflow-x: scroll;
            }

            .card_content {
                width: 100% !important;
                overflow-x: visible;
            }

            @media (min-width: 1300px) {
                .font_mini {
                    font-size: 15px !important;
                }

                .font_mini_header {
                    font-size: 15px !important;
                }

                .card_width {
                    width: 100% !important;
                }
            }
        }
    </style>

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
            <form id="form_concilia" method="post" class="mr-0" action="#" onsubmit="return valida_envia();return false;">
                <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="row">
                                    <div class="col">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
                                            <li class="breadcrumb-item active">Saldos y Devoluciones</li>
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
                                <b>Saldos y Devoluciones</b>
                            </h3>
                        </div>
                        <div class="row mr-2">
                            <div class="col-12 mx-2">
                                <p>
                                    En este módulo se permite visualizar tanto los saldos por diferencias como
                                    las devoluciones completas de transferencias. Además, brinda la opción de
                                    reincorporar las devoluciones a la lista de Transferencias recibidas,
                                    permitiendo su procesamiento nuevamente. Este módulo facilita también el
                                    manejo de saldos pendientes y devoluciones, asegurando su correcta gestión
                                    para su posterior seguimiento y tratamiento.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid px-3">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row text-start justify-content-start justify-items-stretch pl-4 mb-3">
                                <div class="col-lg-2">
                                    <label class="col-12" for="fecha_ultima_cartola">ÚLT ACTUALIZACIÓN</label>
                                    <input type="text" class="form-control col-12" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                                </div>
                                <div class="col-lg-2">
                                    <label for="dias_mora" class="col-12">DIAS MORA</label>
                                    <select name="dias_mora" id="dias_mora" class="form-control col-12" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Mostrar todos</option>
                                        <option value="1">170 días o más</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <div class="col-lg-12">
                                        <label for="cuenta" class="col-4">CUENTA</label>
                                        <select name="cuenta" id="cuenta" class="form-control" maxlength="50" autocomplete="off">
                                            <option value="0" selected>Mostrar todas</option>
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
                                <div class="col-lg-2">
                                    <label for="estado_conc" class="col-12">ESTADO</label>
                                    <select name="estado_conc" id="estado_conc" class="form-control col-12" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Mostrar todos</option>
                                        <option value="CONC">CONCILIADO</option>
                                        <option value="ABON">ABONADO</option>
                                        <option value="PEND">PENDIENTE</option>
                                    </select>
                                </div>
                                <div class="col-lg-1">
                                    <a href="conciliaciones_devoluciones_asignar.php" class="btn btn-primary waves-effect waves-light mt-4" id="guardarButton" data-href="conciliaciones_devoluciones_asignar.php">PROCESAR</a>
                                </div>
                            </div><!--end form-group-->
                        </div><!--end col-->
                    </div>

                    <div class="col-12 px-3">
                        <div class="card card_content">
                            <div class="card-body card_width">
                                <table id="datatable2" class="table dt-responsive nowrap table-hover" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <!--<th class="font_mini_header">
                                                <div class="d-flex flex-column align-items-center">
                                                    <input class="mb-2" type="checkbox" id="select_all_checkbox1" onclick="handleMasterCheckbox(1)">
                                                </div>
                                            </th> -->
                                            <th>TIPO</th>
                                            <th>CTA BENEF</th>
                                            <th>F. RECEP</th>
                                            <th>TRANSACCION</th>
                                            <th>RUT ORD</th>
                                            <th>NOMBRE</th>
                                            <th>SALDO</th>
                                            <th class="font_mini_header"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_saldo = "EXEC [_SP_CONCILIACIONES_SALDOS_LISTA]";
                                        $stmt_saldo = sqlsrv_query($conn, $sql_saldo);
                                        if ($stmt_saldo === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        while ($conciliacion = sqlsrv_fetch_array($stmt_saldo, SQLSRV_FETCH_ASSOC)) {
                                            // Variables individuales
                                            $tipo_saldo     = $conciliacion["TIPO_SALDO"];
                                            $cuenta         = $conciliacion["CUENTA"];
                                            $fecha_original = $conciliacion["F_REC"];
                                            $transaccion    = $conciliacion["TRANSACCION"];
                                            $rut_ord        = trim($conciliacion["RUT_ORD"]);
                                            $dv             = $conciliacion["DV"];
                                            $nombre         = $conciliacion["NOMBRE"];
                                            $saldo          = $conciliacion["SALDO"];
                                            $disabled       = '';
                                            $id_pareo_sistema = $conciliacion["ID_PAREO_SISTEMA"];

                                            // Formateo de fecha
                                            $fecha = DateTime::createFromFormat('d/m/Y', $fecha_original);
                                            $fecha_formateada = $fecha ? $fecha->format('Y/m/d') : 'Fecha inválida';
                                        ?>
                                            <tr>
                                                <td class="col-auto"><?php echo $tipo_saldo; ?></td>
                                                <td class="col-auto"><?php echo $cuenta; ?></td>
                                                <td class="col-auto"><?php echo $fecha_formateada; ?></td>
                                                <td class="col-auto"><?php echo $transaccion; ?></td>
                                                <td class="col-auto"><?php echo $rut_ord . "-" . $dv; ?></td>
                                                <td class="col-auto"><?php echo $nombre; ?></td>
                                                <td class="col-auto">$<?php echo number_format($saldo, 0, ',', '.'); ?></td>
                                                <td class="font_mini">
                                                    <?php if ($tipo_saldo == 'DEVOLUCION') { ?>
                                                        <a data-toggle="tooltip" title="Eliminar" data-href="conciliaciones_devoluciones_eliminar.php?id_ps=<?php echo $id_pareo_sistema ?>" class="btn btn-icon btn-rounded btn-danger delete-btn">
                                                            <i class="feather-24" data-feather="x"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->
                <input type="hidden" id="selected_ids_docs" name="selected_ids_docs[]">
                <input type="hidden" id="selected_ids_pareodoc" name="selected_ids_pareodoc[]">
                <input type="hidden" id="selected_types" name="selected_types[]">
            </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Evita la navegación inmediata

                    const href = this.getAttribute('data-href'); // Obtén el enlace real

                    Swal.fire({
                        title: '¿Confirmas anular esta devolución?',
                        text: "No podrás revertir esta acción.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Navega al enlace si se confirma
                            window.location.href = href;
                        }
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const guardarButton = document.getElementById('guardarButton');

            guardarButton.addEventListener('click', function(event) {
                event.preventDefault(); // Evita la navegación inmediata

                // Verificar si hay registros en la tabla
                const tableBody = document.querySelector('#datatable2 tbody');
                const rows = tableBody.querySelectorAll('tr');
                const noDataMessage = tableBody.querySelector('.dataTables_empty'); // Clase usada por DataTables para "Sin datos"

                // Validar si hay registros visibles en la tabla
                if (rows.length === 0 || noDataMessage) {
                    Swal.fire({
                        title: 'No se puede procesar',
                        text: 'No hay registros disponibles para procesar.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                    return; // Salir de la función si no hay registros
                }

                // Continuar con el proceso si hay registros
                const href = this.getAttribute('data-href'); // Obtén el enlace real

                Swal.fire({
                    title: '¿Confirmar acción?',
                    text: "Se procesarán los saldos y devoluciones. Se recomienda realizar una validación previa de los datos.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, procesar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Navega al enlace si se confirma
                        window.location.href = href;
                    }
                });
            });
        });
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
            "paging": false, // Deshabilita la paginación
            "searching": true, // Habilita la búsqueda
            "ordering": true, // Habilita el ordenamiento
            responsive: true,
            order: [
                [3, 'asc']
            ],
            columnDefs: [{
                targets: [7],
                orderable: false
            }]
        });

        // Function to apply filters based on stored values
        function applyFilters() {
            var storedCuentaValue = sessionStorage.getItem('selected_cuenta_2');
            var storedFiltroValue = sessionStorage.getItem('selected_diasmora');
            var storedEstadoValue = sessionStorage.getItem('selected_estado_2');

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

            // Apply estado filter
            if (storedEstadoValue && storedEstadoValue !== "0") {
                $('#estado_conc').val(storedEstadoValue).change();
            } else {
                $('#estado_conc').val("0").change(); // Reset to default
            }
        }

        // Custom filter function for dias_mora and estado
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var diasMoraFilter = $('#dias_mora').val();
                var estadoFilter = $('#estado_conc').val();
                var diasMoraValue = parseFloat(data[9]) || 0; // Convert the value to a number
                var estadoValue = data[11]; // Assuming column 9 is the ESTADO column

                // Filter by dias_mora
                if (diasMoraFilter === "1") {
                    if (diasMoraValue < 169) {
                        return false; // Exclude rows that don't meet the criteria
                    }
                }

                // Filter by estado
                if (estadoFilter !== "0" && estadoValue != estadoFilter) {
                    return false; // Exclude rows that don't match the estado filter
                }

                return true; // Show all rows that pass the filters
            }
        );

        // Add event listener to the cuenta select element
        $('#cuenta').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_cuenta_2', filterValue);

            if (filterValue == "0") {
                table.column(3).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(3).search(filterValue).draw();
            }
        });

        // Add event listener to the dias_mora select element
        $('#dias_mora').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_diasmora', filterValue);

            // Redraw table to apply the dias_mora filter
            table.draw();
        });

        // Add event listener to the estado select element
        $('#estado_conc').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_estado_2', filterValue);

            // Redraw table to apply the estado filter
            table.draw();
        });

        // Apply filters on page load
        applyFilters();
    });



    <?php if ($op == 1) { ?>
        Swal.fire({
            width: 600,
            icon: 'info',
            title: 'Devolución anulada.',
            showConfirmButton: true
        });
    <?php } ?>

    <?php if ($op == 2) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Saldos y devoluciones procesados.',
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

    <?php if ($op == 5) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Pareo eliminado.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>
</script>

</html>