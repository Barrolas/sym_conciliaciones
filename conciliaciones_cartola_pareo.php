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
        from Transferencias_Recibidas_Hist";

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
    <style>
        .sticky-container {
            position: sticky;
            top: 0;
            /* Se ajusta la distancia desde la parte superior */
            z-index: 10;
            /* Asegura que esté sobre otros elementos */
        }

        .table-responsive {
            overflow-y: auto;
            /* Habilita el desplazamiento vertical */
            max-height: 50vh;
            /* Altura máxima del contenedor */
        }

        .sticky-table {
            position: sticky;
            top: 0;
            /* Se ajusta la distancia desde la parte superior */
            background-color: white;
            /* Fondo blanco para la cabecera sticky */
            z-index: 10;
            /* Asegura que esté sobre otros elementos */
        }

        #loading-indicator {
            position: absolute;
            /* Puedes ajustar la posición */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 5px;
            z-index: 1000;
            /* Asegúrate de que esté encima de otros elementos */
        }




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
            <form id="form-conciliacion" method="post" class="mr-0" action="conciliaciones_cartola_pareo_guardar.php" onsubmit="return valida_envia(); return false;">
                <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="row">
                                    <div class="col">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
                                            <li class="breadcrumb-item active">Conciliar</li>
                                        </ol>
                                    </div><!--end col-->
                                </div><!--end row-->
                            </div><!--end page-title-box-->
                        </div><!--end col-->
                    </div><!--end row-->
                    <!-- end page title end breadcrumb -->

                    <div class="container-fluid mx-3">
                        <div class="row">
                            <div class="col">
                                <h3><b>Conciliar</b></h3>
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
                            <div class="col-md-5">
                                <div class="card card_content">
                                    <div class="card-body card_width">
                                        <h5 class="card-title pb-3">
                                            Salidas de Cartola Bancaria
                                            <!-- <button type="submit" class="btn btn-primary btn-sm ms-3" id="enviarSeleccionCartola">Conciliar</button> -->
                                        </h5>
                                        <table id="datatable_salidas" class="table table-striped table-bordered dt-responsive nowrap" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th></th> <!-- Encabezado vacío para el icono de colapso -->
                                                    <th>FECHA</th>
                                                    <th>CUENTA</th>
                                                    <th>SALIDAS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Consulta al procedimiento almacenado para obtener las filas de salida
                                                $sql_salidas = "EXEC [_SP_CONCILIACIONES_CARTOLA_SALIDAS_DIARIAS_LISTA]";
                                                $stmt_salidas = sqlsrv_query($conn, $sql_salidas);

                                                if ($stmt_salidas === false) {
                                                    die(print_r(sqlsrv_errors(), true));
                                                }

                                                // Generamos las filas principales
                                                while ($salida = sqlsrv_fetch_array($stmt_salidas, SQLSRV_FETCH_ASSOC)) {
                                                    $cuenta = $salida["CUENTA"];
                                                    $fecha = $salida["FECHA"];
                                                    $monto_salidas = $salida["SALIDAS"];

                                                    // Sanitizamos y formateamos el ID único para cada fila
                                                    $transaccionId = preg_replace('/[^\w]/', '', $cuenta . '_' . $fecha); // Sanitiza el ID

                                                    // Convertimos el monto a entero y lo formateamos
                                                    $monto_salidas_int = (int) str_replace('.', '', $monto_salidas); // Valor sanitizado como entero
                                                    $monto_salidas_formatted = number_format($monto_salidas_int, 0, ',', '.'); // Valor con separadores de miles
                                                ?>
                                                    <tr class="clickable-row" data-toggle="collapse" data-target="#details-<?php echo $transaccionId; ?>" aria-expanded="false" aria-controls="details-<?php echo $transaccionId; ?>">
                                                        <td style="display: flex; justify-content: center; align-items: center;"> <!-- Centrado del icono -->
                                                            <span class="toggle-icon" style="cursor:pointer;">
                                                                <i class="fas fa-plus"></i>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $fecha; ?></td>
                                                        <td><?php echo $cuenta; ?></td>
                                                        <td>
                                                            $<?php echo $monto_salidas_formatted; ?>
                                                        </td>
                                                    </tr>
                                                    <tr class="collapse" id="details-<?php echo $transaccionId; ?>">
                                                        <td colspan="4">
                                                            <div id="details-content-<?php echo $transaccionId; ?>">
                                                                <table class="table table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th></th> <!-- Columna para el checkbox -->
                                                                            <th>N° Documento</th> <!-- Columna para N° Documento -->
                                                                            <th>DETALLE</th>
                                                                            <th>MONTO</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        // Llamar al procedimiento almacenado para obtener detalles
                                                                        $sql_detalles = "EXEC [_SP_CONCILIACIONES_CARTOLA_SALIDAS_DETALLES_CONSULTA] '$cuenta', '$fecha'";
                                                                        $stmt_detalles = sqlsrv_query($conn, $sql_detalles);

                                                                        if ($stmt_detalles === false) {
                                                                            die(print_r(sqlsrv_errors(), true));
                                                                        }

                                                                        while ($detalle = sqlsrv_fetch_array($stmt_detalles, SQLSRV_FETCH_ASSOC)) {

                                                                            $n_documento                = $detalle["N_DOCUMENTO"];
                                                                            $monto_detalle              = $detalle["MONTO"];
                                                                            $monto_detalle_int          = (int)str_replace('.', '', $monto_detalle);
                                                                            $monto_detalle_formatted    = number_format($monto_detalle_int, 0, ',', '.');
                                                                            $descripcion                = $detalle["DESCRIPCION"];

                                                                        ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <!-- Modificación en el checkbox de cada fila detallada -->
                                                                                    <input type="checkbox" class="select-detail-checkbox"
                                                                                        name="selected_details[]"
                                                                                        data-value="DETAIL<?php echo $transaccionId . '-' . $n_documento; ?>"
                                                                                        data-monto="<?php echo $monto_detalle_int; ?>"
                                                                                        value="<?php echo $cuenta . ',' . $fecha . ',' . $n_documento . ',' . $descripcion . ',' . $monto_detalle_int ?>">
                                                                                </td> <!-- Checkbox a la izquierda -->
                                                                                <td><?php echo $n_documento; ?></td> <!-- Mostrar N° Documento -->
                                                                                <td><?php echo $descripcion; ?></td>
                                                                                <td>$<?php echo $monto_detalle_formatted; ?></td>
                                                                            </tr>
                                                                        <?php } ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Remesas (50% del ancho) -->
                            <div class="col-md-7">
                                <div class="card card_content sticky-container">
                                    <div class="card-body card_width">
                                        <h5 class="card-title pb-3">Respaldos</h5>
                                        <!-- Indicador de carga -->
                                        <div id="loading-indicator" style="display: none;">Cargando...</div>
                                        <div class="table-responsive sticky-table" id="remesas-container">
                                            <table id="datatable_remesas" class="table table-striped table-bordered dt-responsive nowrap" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>FECHA</th>
                                                        <th>CODIGO</th>
                                                        <th>CUENTA</th>
                                                        <th>DETALLE</th>
                                                        <th>MONTO</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Aquí se actualizarán los datos -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h4><strong>Total Cartola: $<span id="suma-cartola">0</span></strong></h4>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <h4><strong>Total Remesas: $<span id="suma-remesas">0</span></strong></h4>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h4><strong>Cantidad: <span id="cantidad_seleccionados_cartola">0</span></strong></h4>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <h4><strong>Cantidad: <span id="cantidad_seleccionados_remesas">0</span></strong></h4>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="enviarSeleccionCartola" <?php $disabled ?> >Conciliar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end container -->
                </div>
            </form>
        </div>
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

<!-- jQuery -->
<script src="assets/js/jquery.min.js"></script>
<!-- Bootstrap Bundle (includes Popper) -->
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
<script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- Asegúrate de tener Font Awesome -->

<script>

$(document).ready(function() {
    $('#datatable_salidas').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        columnDefs: [
            { orderable: false, targets: 0 }, // Deshabilitar ordenación para la primera columna (ícono de colapso)
        ]
    });
});

    $(document).ready(function() {
        // Variables para almacenar los totales seleccionados
        let totalCartola = 0;
        let totalRemesas = 0;
        let cantidadSeleccionadosCartola = 0;
        let cantidadSeleccionadosRemesas = 0;
        let currentFecha = '';
        let currentCuenta = '';

        // Función para actualizar el resumen y el estado del botón
        function actualizarResumen() {
            $('#suma-cartola').text(totalCartola.toLocaleString('es-CL'));
            $('#suma-remesas').text(totalRemesas.toLocaleString('es-CL'));
            $('#cantidad_seleccionados_cartola').text(cantidadSeleccionadosCartola);
            $('#cantidad_seleccionados_remesas').text(cantidadSeleccionadosRemesas);
            toggleButtonState();
        }

        // Función para habilitar o deshabilitar el botón "Conciliar"
        function toggleButtonState() {
            // Verificar si al menos un checkbox de cada tipo ha sido seleccionado
            const hayDetallesSeleccionados = cantidadSeleccionadosCartola > 0;
            const hayRemesasSeleccionadas = cantidadSeleccionadosRemesas > 0;

            if (hayDetallesSeleccionados && hayRemesasSeleccionadas && totalCartola === totalRemesas) {
                $('#enviarSeleccionCartola').prop('disabled', false);
            } else {
                $('#enviarSeleccionCartola').prop('disabled', true);
            }
        }

        // Manejo de checkboxes en los detalles de la tabla de Cartola
        $('#datatable_salidas').on('change', '.select-detail-checkbox', function() {
            let monto = parseInt($(this).data('monto')) || 0;

            if ($(this).is(':checked')) {
                totalCartola += monto;
                cantidadSeleccionadosCartola++;
            } else {
                totalCartola -= monto;
                cantidadSeleccionadosCartola--;
            }
            actualizarResumen();
        });

        // Manejo de checkboxes en los detalles de la tabla de Remesas
        $('#datatable_remesas').on('change', '.select-remesa-checkbox', function() {
            let monto = parseInt($(this).closest('tr').find('td:last').text().replace(/\D/g, '')) || 0;

            if ($(this).is(':checked')) {
                totalRemesas += monto;
                cantidadSeleccionadosRemesas++;
            } else {
                totalRemesas -= monto;
                cantidadSeleccionadosRemesas--;
            }
            actualizarResumen();
        });

        // Manejo de clic en las filas individuales de la tabla de Cartola
        $('.clickable-row').click(function() {
            const targetId = $(this).data('target');
            const isRowCollapsed = $(targetId).hasClass('show');

            currentFecha = $(this).find('td:nth-child(2)').text();
            currentCuenta = $(this).find('td:nth-child(3)').text();

            $('tr.collapse').collapse('hide');
            $('.toggle-icon').html('<i class="fas fa-plus"></i>');

            totalCartola = 0;
            totalRemesas = 0;
            cantidadSeleccionadosCartola = 0;
            cantidadSeleccionadosRemesas = 0;
            $('#datatable_remesas input[type=checkbox]').prop('checked', false);
            $('#datatable_salidas input[type=checkbox]').prop('checked', false);
            actualizarResumen();

            if (isRowCollapsed) {
                $(targetId).collapse('hide');
                $(this).find('.toggle-icon').html('<i class="fas fa-plus"></i>');
            } else {
                $(targetId).collapse('show');
                $(this).find('.toggle-icon').html('<i class="fas fa-minus"></i>');

                $.ajax({
                    url: 'get_remesas.php',
                    type: 'POST',
                    data: {
                        fecha: currentFecha,
                        cuenta: currentCuenta
                    },
                    beforeSend: function() {
                        $('#loading-indicator').show();
                    },
                    success: function(response) {
                        // Insertar el HTML directamente en la tabla de remesas
                        $('#datatable_remesas tbody').html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error al cargar remesas:", textStatus, errorThrown);
                        $('#datatable_remesas tbody').html("<tr><td colspan='6'>Error al cargar datos de remesas.</td></tr>");
                    },
                    complete: function() {
                        $('#loading-indicator').hide();
                    }
                });
            }
        });

        // Función para validar y enviar el formulario
        window.valida_envia = function(event) {
            event.preventDefault(); // Prevenir el envío del formulario

            // Recopilar los detalles seleccionados
            const selectedDetails = $('input[name="selected_details[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            // Recopilar las remesas seleccionadas
            const selectedRemesas = $('input[name="selected_remesas[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            // Aquí puedes hacer alguna validación si es necesario
            if (selectedDetails.length === 0) {
                alert("Por favor, seleccione al menos un detalle.");
                return false; // Detener el envío si no hay selección
            }

            console.log("Detalles seleccionados:", selectedDetails);
            console.log("Remesas seleccionadas:", selectedRemesas);

            // Si todo está bien, permite el envío del formulario
            document.getElementById("form-conciliacion").submit();
        };

        // Inicializar el estado del botón al cargar la página
        toggleButtonState();
    });
</script>

<script>
    <?php if ($op == 1) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Canalizacion realizada con éxito.',
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