<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

$sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
        from [192.168.1.193].conciliacion.dbo.Transferencias_Recibidas_Hist";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$fecha_proceso = $row["FECHAPROCESO"];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ndoc    = $_GET["n_doc"];

$sql_detalles   = "{call [_SP_CONCILIACIONES_CARTOLA_SALIDAS_CONSULTA](?)}";
$params_detalles = array(
    array($ndoc,     SQLSRV_PARAM_IN)
);
$stmt_detalles = sqlsrv_query($conn, $sql_detalles, $params_detalles);
if ($stmt_detalles === false) {
    echo "Error in executing statement detalles.\n";
    die(print_r(sqlsrv_errors(), true));
}
$detalles = sqlsrv_fetch_array($stmt_detalles, SQLSRV_FETCH_ASSOC);

$cuenta         = $detalles['CUENTA'];
$fecha          = $detalles['FECHA'];
$descripcion    = $detalles['DESCRIPCION'];
$n_documento    = $detalles['N_DOCUMENTO'];
$monto_total    = $detalles['MONTO'];
$monto_total_sanitizado   = preg_replace('/[^0-9]/', '', $detalles['MONTO']);

$existe             = 0;
$idestado           = 0;
$estado             = '';
$rut_deudor         = 0;
$monto_ingresado    = 0;
$monto_diferencia   = 0;

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
    <link href="assets/css/loading.css" rel="stylesheet" type="text/css" />
    <!-- Plugins -->
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
    <style>
        .custom-size {
            font-size: 15px;
            /* search button */
        }

        .scrollable-div {
            max-height: 72px;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .form-control {
            height: 28px;
            /* Ajusta la altura según sea necesario */
        }
    </style>
    <style>
        @media (min-width: 1000px) and (max-width: 1299px) {
            .font_mini {
                font-size: 12px !important;
            }

            .font_mini_input {
                font-size: 12px !important;

            }

            .font_mini_header {
                font-size: 12px !important;
            }


            @media (min-width: 1300px) {
                .font_mini {
                    font-size: 15px !important;
                }

                .font_mini_input {
                    font-size: 15px !important;
                }

                .font_mini_header {
                    font-size: 15px !important;
                }
            }
        }
    </style>
</head>

<body class="dark-sidenav">
    <!-- Left Sidenav -->
    <?php include("menu_izquierda.php"); ?>
    <!-- end left-sidenav-->
    <div class="page-wrapper w-75">
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

                                        <li class="breadcrumb-item"><a href="conciliaciones_transferencias_pendientes.php">Transferencias pendientes</a></li>
                                        <li class="breadcrumb-item active">Pareo Cartola</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid px-3 mb-2">
                    <div class="row">
                        <div class="col">
                            <h3>
                                <b>Pareo Cartola</b>
                            </h3>
                        </div>
                        <div class="row">
                            <div class="col-12 mx-2">
                                <p>
                                    Este módulo presenta de manera organizada todos los registros cargados exitosamente mediante el proceso de carga masiva. Cada fila de la tabla muestra información sobre cada registro, facilitando una vista rápida y clara de los datos importados.
                                    Al presionar el icono <i class="fa fa-plus-circle text-primary p-1" aria-hidden="true"></i> en cualquier fila, se expande un panel que revela detalles adicionales del registro seleccionado. Esta funcionalidad permite explorar información más detallada sin perder la estructura visual intuitiva de la tabla principal.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid px-2">
                    <div class="col-12 px-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row text-start justify-content-between justify-items-between pl-4 mb-3">
                                    <div class="col-lg-4">
                                        <label class="col-12" for="fecha_ultima_cartola">ÚLT ACTUALIZACIÓN</label>
                                        <input type="text" class="form-control col-12" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="col-4" for="fecha_ultima_cartola">CUENTA</label>
                                        <input type="text" name="cuenta" id="cuenta" class="form-control col-12" maxlength="50" autocomplete="off" value="<?= $cuenta ?>" disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="col-4" for="fecha_ultima_cartola">N° DOCUMENTO</label>
                                        <input type="text" name="transaccion" id="transaccion" class="form-control col-12" maxlength="50" autocomplete="off" value="<?= $ndoc . ' - ' . $fecha ?>" disabled />
                                    </div>
                                </div><!--end form-group-->
                            </div><!--end col-->
                        </div>


                        <form id="form_concilia" method="post" class="mr-0" action="conciliaciones_cartola_pareo_guardar.php?rut_ordenante=<?php  ?>">
                            <div class="card ">
                                <div class="card-header" style="background-color: #0055a6">
                                    <table width="100%" border="0" cellspacing="2" cellpadding="0">
                                        <tbody>
                                            <tr style="background-color:#0055a6">
                                                <td align="left">
                                                    <!-- Formulario de búsqueda -->

                                                    <div class="form-group row align-items-center mb-0">
                                                        <div class="col-auto mr-0">
                                                            <button type="submit" id="conciliarButton" class="btn btn-md btn-info mr-0" disabled><i class="fa fa-plus"></i> PAREAR</button>
                                                        </div>
                                                        <td align="right">
                                                            <a align="right" href="conciliaciones_cartola_pendientes.php?"><button type="button" class="btn btn-md btn-danger"><i class="fa fa-plus"></i> VOLVER</button></a>
                                                        </td>
                                                        <!--
                                                        <div class="col-auto">
                                                            <button type="button" class="btn btn-md btn-secondary" onclick="limpiarFormulario();"><i class="fa fa-times"></i> LIMPIAR</button>
                                                        </div>
                                                        -->
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div><!--end card-header-->
                                <div class="card-body ">
                                    <div class="row text-start">
                                        <div class="col-md-12">

                                            <div class="form-group row text-center justify-content-between">
                                                <div class="col-lg-4 d-flex align-items-center justify-content-end">
                                                    <label for="monto" class="col-lg-4 col-form-label">MONTO</label>
                                                    <div class="col-lg-8">
                                                        <input type="text" name="monto" id="monto" class="form-control" maxlength="50" autocomplete="off" value="$<?= $monto_total ?>" disabled />
                                                    </div>
                                                </div>
                                                <!--<div class="col-lg-4 d-flex align-items-start">
                                                    <label for="total" class="col-lg-3 col-form-label">TOTAL</label>
                                                    <div class="col-lg-8">
                                                        <input type="text" name="total" id="total" class="form-control" maxlength="50" autocomplete="off" value=" " disabled style="display: none;" />
                                                        <input type="text" name="total2" id="total2" class="form-control" maxlength="50" autocomplete="off" value="$ " disabled />
                                                        <input type="hidden" name="es_entrecuentas" id="es_entrecuentas">
                                                    </div>
                                                </div> -->
                                            </div>

                                            <hr>

                                        </div>
                                    </div>

                                    <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th class="col-1 font_mini_header"></th>
                                                <th class="font_mini_header">N° REMESA/DEVOLUCION</th>
                                                <th class="font_mini_header">CANAL</th>
                                                <th class="font_mini_header">MONTO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_conc    = "EXEC [_SP_CONCILIACIONES_CONCILIADOS_ENTRADA_LISTA]";
                                            $stmt_conc = sqlsrv_query($conn, $sql_conc);
                                            if ($stmt_conc === false) {
                                                die(print_r(sqlsrv_errors(), true));
                                            }
                                            while ($conciliados = sqlsrv_fetch_array($stmt_conc, SQLSRV_FETCH_ASSOC)) {

                                                $id_saldo       = $conciliados['ID_CONCILIACION_SALDO'];
                                                $tipo_canal     = $conciliados['ID_TIPO_CANALIZACION'];
                                                $canal          = $conciliados['CANAL'];
                                                $n_remesa       = $conciliados['N_REMESA'];
                                                $monto_entrada  = $conciliados['MONTO_TR'];
                                                $monto_entrada_sanitizado = preg_replace('/[^0-9]/', '', $conciliados['MONTO_TR']);
                                                $monto_saldo    = $conciliados['MONTO_SALDO'];

                                                $codigo = '';
                                                if ($tipo_canal == 1) {
                                                    $codigo = !empty($n_cheque) ? $n_cheque : ''; // Si es tipo 1, asigna el número de cheque si existe, de lo contrario, deja vacío
                                                } elseif ($tipo_canal == 2) {
                                                    $codigo = !empty($n_remesa) ? $n_remesa : ''; // Si es tipo 2, asigna el número de remesa si existe, de lo contrario, deja vacío
                                                } elseif ($tipo_canal == 3) {
                                                    $codigo = !empty($id_saldo) ? $id_saldo : ''; // Si es tipo 2, asigna el número de remesa si existe, de lo contrario, deja vacío
                                                }
                                                // Condición para deshabilitar dependiendo del tipo de canalización
                                                if ($tipo_canal == 2) {
                                                    // Comparar monto_entrada_sanitizado con monto_total_sanitizado
                                                    $isDisabled = ($monto_entrada_sanitizado <> $monto_total_sanitizado) ? 'disabled' : '';
                                                } elseif ($tipo_canal == 3) {
                                                    // Comparar monto_saldo_sanitizado con monto_total_sanitizado
                                                    $isDisabled = ($monto_saldo <> $monto_total_sanitizado) ? 'disabled' : '';
                                                }

                                            ?>
                                                <tr>
                                                    <td class="col-1" style="text-align: center;">
                                                        <input type="radio" class="iddocumento_radio" name="iddocumento_radio[]" value="<?php echo $n_documento . ',' . $fecha . ',' . $cuenta . ',' . $monto_total_sanitizado . ',' . $codigo. ',' . $tipo_canal ?>" <?php echo $isDisabled; ?>>
                                                    </td>
                                                    <td class="col-auto font_mini interes" id="interes">
                                                        <?php echo $codigo; ?>
                                                    </td>
                                                    <td class="col-auto font_mini"><?php echo $canal ?></td>
                                                    <td class="col-auto font_mini">
                                                        $<?php echo number_format($monto_entrada == 0 ? $monto_saldo : $monto_entrada, 0, ',', '.'); ?>
                                                    </td>
                                                </tr> <?php
                                                    } ?>
                                        </tbody>
                                    </table>
                                </div><!-- end card-body -->
                            </div><!-- end card -->
                        </form>
                    </div><!-- end col -->
                </div><!-- end row -->
            </div><!-- container-fluid -->
        </div><!-- page-content -->
        <?php include('footer.php'); ?>
        <!-- page-wrapper -->
    </div>
    <?php /* print_r($detalles);
exit; */ ?>
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

<script>
    function valida_envia() {
        // Función vacía, sin validaciones activas aun.
        alert('paso')
        return true;
    }
</script>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#datatable2').DataTable({
            responsive: true,
            columnDefs: [{
                targets: [0],
                orderable: false
            }, ],
            order: [
                [3, 'desc']
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).attr('id', 'row-' + dataIndex);
            }
        });

        // Actualizar Total basado en la columna 2 de la fila seleccionada
        function actualizarTotal(valor) {
            $('#total2').val('$' + (valor || 0));
        }

        // Manejar la selección/deselección de radios
        $('#datatable2').on('change', '.iddocumento_radio', function() {
            var isChecked = $(this).is(':checked');
            var rowId = $(this).closest('tr').attr('id');
            var rowData = table.row('#' + rowId).data();
            var valor = isChecked ? parseFloat(rowData[2]) || 0 : 0;

            actualizarTotal(valor);
            updateConciliarButton();
        });

        // Permitir deselección de radios
        $('#datatable2').on('click', '.iddocumento_radio', function() {
            if ($(this).data('waschecked') === true) {
                $(this).prop('checked', false).data('waschecked', false);
                actualizarTotal(0);
                updateConciliarButton();
            } else {
                $('input[name="' + $(this).attr('name') + '"]').data('waschecked', false);
                $(this).data('waschecked', true);
            }
        });

        // Habilitar/deshabilitar el botón de conciliar basado en la selección
        function updateConciliarButton() {
            var isRadioSelected = $('#datatable2 .iddocumento_radio:checked').length > 0;
            $('#conciliarButton').prop('disabled', !isRadioSelected);
        }

        updateConciliarButton();
    });
</script>

<script>
    function limpiarFormulario() {
        // Redireccionar para limpiar
        window.location.href = 'conciliaciones_documentos.php?rut_ordenante=<?php echo $rut_ordenante ?>&transaccion=<?php echo $transaccion ?>&cuenta=<?php echo $cuenta ?>&matched=3';
    }
</script>

<script>
    // Initialize Feather Icons
    feather.replace();
    // Initialize Bootstrap Tooltip
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<script>
    document.getElementById('gestion').addEventListener('wheel', function(e) {
        e.preventDefault(); // Previene el desplazamiento predeterminado

        var scrollSpeed = 1; // Ajusta este valor para cambiar la velocidad

        this.scrollTop += e.deltaY / scrollSpeed;
    });
</script>

<script>
    // Manejo de alertas
    <?php if ($op == 1) { ?>
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
            icon: 'error',
            title: 'Ya existe una conciliación para la transacción.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>
</script>

</html>