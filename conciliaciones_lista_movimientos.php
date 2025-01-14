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



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Movimientos</title>
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
    <link rel="stylesheet" href="https://unpkg.com/simplebar@latest/dist/simplebar.min.css" />
    <script src="https://unpkg.com/simplebar@latest/dist/simplebar.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">


    <!-- Plugins -->
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>

    <style>
        @media (min-width: 1000px) and (max-width: 1299px) {

            .font_mini {
                font-size: 10px !important;
            }

            .font_mini_header {
                font-size: 10px !important;
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
            <div class="container-fluid">
                <!-- Page-Title -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="row">
                                <div class="col">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
                                        <li class="breadcrumb-item active">Movimientos</li>
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
                            <b>Movimientos</b>
                        </h3>
                    </div>
                    <div class="row mr-2">
                        <div class="col-12 mx-2">
                            <p>
                                Esta herramienta permite ingresar y gestionar cargas masivas de documentos asociados a deudores de clientes,
                                utilizando un formato pre-establecido con un archivo base en Excel.
                                Las <b>cargas</b> pueden ser revisadas para obtener el detalle de la cantidad de documentos que fueron leídos, cargados satisfactoriamente
                                y rechazados según los criterios de validación correspondientes (<strong><a href="#">ver aquí</a></strong>), con detalle disponible para ambos casos.
                                También se permite deshabilitar cargas en caso de errores en la asignación a clientes con el botón de <b>ESTADO</b>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3">
                <div class="row mb-4 align-items-end">
                    <!-- Cuenta -->
                    <div class="col-lg-2">
                        <label for="cuenta" class="form-label">Cuenta</label>
                        <select name="cuenta" id="cuenta" class="form-control" maxlength="50" autocomplete="off">
                            <option value="0" selected>Mostrar todas</option>
                            <?php
                            $sql_cuenta = "{call [_SP_CONCILIACIONES_LISTA_CUENTAS_BENEFICIARIOS]}";
                            $stmt_cuenta = sqlsrv_query($conn, $sql_cuenta);

                            if ($stmt_cuenta === false) {
                                mostrarError("Error al ejecutar la consulta de cuentas beneficiarios. -> stmt_cuenta");
                            }
                            while ($cuenta = sqlsrv_fetch_array($stmt_cuenta, SQLSRV_FETCH_ASSOC)) {
                            ?>
                                <option value="<?php echo $cuenta["CUENTA"]; ?>"><?php echo $cuenta["CUENTA"]; ?></option>
                            <?php }; ?>
                        </select>
                    </div>

                    <!-- Periodo Inicio -->
                    <div class="col-lg-2">
                        <label for="date_start" class="form-label">Periodo Inicio</label>
                        <div class="input-group">
                            <span class="input-group-text"><i data-feather="calendar"></i></span>
                            <input type="text" id="date_start" name="date_start" class="form-control datepicker" placeholder="Selecciona una fecha">
                        </div>
                    </div>

                    <!-- Periodo Fin -->
                    <div class="col-lg-2">
                        <label for="date_end" class="form-label">Periodo Fin</label>
                        <div class="input-group">
                            <span class="input-group-text"><i data-feather="calendar"></i></span>
                            <input type="text" id="date_end" name="date_end" class="form-control datepicker" placeholder="Selecciona una fecha">
                        </div>
                    </div>

                    <!-- Botones Buscar y Exportar -->
                    <div class="col-lg-2 d-flex justify-content-start ml-2">
                        <button type="button" class="btn btn-success" id="buscar_btn">Buscar</button>
                    </div>
                    <div class="col-lg-2 d-flex justify-content-end ml-2">
                            <button type="button" class="btn btn-primary" id="exportar_btn" disabled>Exportar</button>
                        </a>
                    </div>
                </div>

                <div class="col-12 px-3">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable2" class="table dt-responsive nowrap table-hover" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="font_mini">ID</th>
                                        <th class="font_mini">TKT</th>
                                        <th class="font_mini">MOVIMIENTO</th>
                                        <th class="font_mini">TRANSACCIÓN</th>
                                        <th class="font_mini">CTA BENEF</th>
                                        <th class="font_mini">F REC</th>
                                        <th class="font_mini">F VTO</th>
                                        <th class="font_mini">OPERACIÓN</th>
                                        <th class="font_mini">$ DOC</th>
                                        <th class="font_mini">HABER</th>
                                        <th class="font_mini">DEBE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Variables para los parámetros del SP
                                    $fecha_inicio   = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio']    : null;
                                    $fecha_fin      = isset($_POST['fecha_fin'])    ? $_POST['fecha_fin']       : null;

                                    // Preparar la llamada al SP
                                    $sql_mov = "{call [_SP_CONCILIACIONES_MOVIMIENTOS_LISTA](?, ?, ?)}";
                                    $params_mov = array(1, $fecha_inicio, $fecha_fin); // Estado fijo en 1 para este ejemplo
                                    $stmt_mov = sqlsrv_query($conn, $sql_mov, $params_mov);

                                    if ($stmt_mov === false) {
                                        mostrarError("Error al ejecutar la consulta de movimientos. -> stmt_mov");
                                    }

                                    while ($conciliacion = sqlsrv_fetch_array($stmt_mov, SQLSRV_FETCH_ASSOC)) {
                                    ?>
                                        <tr>
                                            <td class="font_mini"><?php echo $conciliacion["ID_PS"]; ?></td>
                                            <td class="font_mini"><?php echo $conciliacion["TICKET"]; ?></td>
                                            <td class="font_mini"><?php echo $conciliacion["MOVIMIENTO"]; ?></td>
                                            <td class="font_mini"><?php echo $conciliacion["TRANSACCION"]; ?></td>
                                            <td class="font_mini"><?php echo $conciliacion["CTA_BENEF"]; ?></td>
                                            <td class="font_mini"><?php echo $conciliacion["F_RECEPCION"]->format('Y/m/d'); ?></td>
                                            <td class="font_mini"><?php echo $conciliacion["F_VENC"]->format('Y/m/d'); ?></td>
                                            <td class="font_mini"><?php echo $conciliacion["N_DOC"] ?? ''; ?></td>
                                            <td class="font_mini">$<?php echo number_format($conciliacion["MONTO_DOC"], 0, ',', '.'); ?></td>
                                            <td class="font_mini">$<?php echo number_format($conciliacion["HABER"], 0, ',', '.'); ?></td>
                                            <td class="font_mini">$<?php echo number_format($conciliacion["DEBE"], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script>
    $(".datepicker").flatpickr({
        dateFormat: "Y-m-d", // Formato que se mostrará y usará
        locale: "es" // Configuración en español
    });

    // Inicializar Feather Icons
    feather.replace();

    $(document).ready(function () {
        $('#idcliente').select2();
    });

    // Inicializar Tooltip de Bootstrap
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Calcular las fechas predeterminadas (últimos 5 días)
    const today = new Date();
    const fiveDaysAgo = new Date();
    fiveDaysAgo.setDate(today.getDate() - 5);

    // Formatear las fechas a "YYYY-MM-DD"
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    const fechaInicioDefault = formatDate(fiveDaysAgo);
    const fechaFinDefault = formatDate(today);

    // Establecer las fechas predeterminadas en los campos de fecha
    $('#date_start').val(fechaInicioDefault);
    $('#date_end').val(fechaFinDefault);

    // DataTables Initialization
    $(document).ready(function () {
        var table = $('#datatable2').DataTable({
            processing: true,
            serverSide: false, // Procesamiento del lado cliente
            columns: [
                { data: 'ID_PS' },
                { data: 'TICKET' },
                { data: 'MOVIMIENTO' },
                { data: 'TRANSACCION' },
                { data: 'CTA_BENEF' }, // Índice 4
                { data: 'F_RECEPCION' },
                { data: 'F_VENC' },
                { data: 'N_DOC' },
                { data: 'MONTO_DOC' },
                { data: 'HABER' },
                { data: 'DEBE' }
            ],
            order: [[0, 'desc']]
        });

        var exportButton = document.getElementById('exportar_btn');

        // Habilitar/Deshabilitar botón exportar basado en el número de filas
        var rowCount = table.rows().count();
        if (rowCount > 0) {
            exportButton.disabled = true;
        } else {
            exportButton.disabled = true;
        }

        // Realizar una consulta inicial con las fechas predeterminadas
        cargarMovimientos(fechaInicioDefault, fechaFinDefault);

        // Evento para filtrar por Cuenta (Columna CTA_BENEF)
        $('#cuenta').on('change', function () {
            const cuentaValue = $(this).val();

            if (cuentaValue === "0") {
                // Limpiar filtro si se selecciona "Mostrar todas"
                table.column(4).search('').draw();
            } else {
                // Aplicar filtro en la columna 4
                table.column(4).search(cuentaValue).draw();
            }
        });

        // Event listener for Buscar button
        $('#buscar_btn').on('click', function () {
            const fechaInicio = $('#date_start').val();
            const fechaFin = $('#date_end').val();

            if (!fechaInicio || !fechaFin) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Faltan datos',
                    text: 'Por favor, selecciona el periodo de inicio y fin.'
                });
                return;
            }

            // Realizar la consulta con las fechas seleccionadas
            cargarMovimientos(fechaInicio, fechaFin);
        });

        // Función para cargar los movimientos
        function cargarMovimientos(fechaInicio, fechaFin) {
            $('#loading-screen').css('display', 'flex');

            $.ajax({
                url: 'get_movimientos.php',
                method: 'POST',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin
                },
                success: function (response) {
                    console.log("Respuesta del servidor:", response); // Inspeccionar estructura
                    try {
                        const jsonResponse = JSON.parse(response); // Asegúrate de que la respuesta sea un JSON válido
                        table.clear().rows.add(jsonResponse).draw();
                    } catch (error) {
                        console.error("Error procesando el JSON:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en la respuesta',
                            text: 'La respuesta del servidor no tiene un formato válido.'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error en la solicitud AJAX:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al buscar los datos.'
                    });
                },
                complete: function () {
                    $('#loading-screen').css('display', 'none'); // Ocultar pantalla de carga
                }
            });
        }

        // Function to apply filters based on stored values
        function applyFilters() {
            const storedCuentaValue = sessionStorage.getItem('selected_cuenta_3');
            if (storedCuentaValue && storedCuentaValue !== "0") {
                $('#cuenta').val(storedCuentaValue).change();
            } else {
                $('#cuenta').val("0").change(); // Reset to default
            }
        }

        // Apply filters on page load
        applyFilters();
    });
</script>

<script>
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