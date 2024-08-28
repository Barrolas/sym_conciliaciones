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

/*
$matched        = isset($_GET["matched"]) ? $_GET["matched"] : 0;
$op             = isset($_GET["op"]) ? $_GET["op"] : 0;
*/

$transaccion    = $_GET["transaccion"];

$sql_detalles   = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_TRANSACCION](?)}";

$params_detalles = array(
    array($transaccion,     SQLSRV_PARAM_IN)
);
$stmt_detalles = sqlsrv_query($conn, $sql_detalles, $params_detalles);
if ($stmt_detalles === false) {
    echo "Error in executing statement detalles.\n";
    die(print_r(sqlsrv_errors(), true));
}
$detalles = sqlsrv_fetch_array($stmt_detalles, SQLSRV_FETCH_ASSOC);

/*
$sql1     = "{call [_SP_CONCILIACIONES_DIFERENCIAS_LISTA]}";
$stmt1 = sqlsrv_query($conn, $sql1, $params1);
if ($stmt1 === false) {
    echo "Error in executing statement 1.\n";
    die(print_r(sqlsrv_errors(), true));
}
$detalles = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC);
*/

$cuenta         = $detalles['CUENTA_BENEF'];
//$rut_deudor     = $detalles['RUT_DEUDOR'];
$nom_ordenante  = $detalles['NOMBRE_ORDENANTE'];
$fecha_rec      = $detalles['FECHA_RECEP'];
$rut_ordenante  = $detalles['RUT_ORDENANTE'];
//$rut_cliente    = $detalles['RUT_CLIENTE'];

$existe             = 0;
$idestado           = 0;
$estado             = '';
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
                                        <li class="breadcrumb-item active">Diferencias</li>
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
                                <b>Diferencias</b>
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
                                        <label class="col-4" for="fecha_ultima_cartola">TRANSACCIÓN</label>
                                        <input type="text" name="transaccion" id="transaccion" class="form-control col-12" maxlength="50" autocomplete="off" value="<?= $transaccion . ' - ' . $fecha_rec ?>" disabled />
                                    </div>
                                </div><!--end form-group-->
                            </div><!--end col-->
                        </div>


                        <form id="form_concilia" method="post" class="mr-0" action="conciliaciones_diferencias_guardar.php?rut_ordenante=<?php echo $rut_ordenante ?>&transaccion=<?php echo $transaccion ?>&rut_deudor=<?php echo $rut_deudor ?>&cuenta=<?php echo $cuenta ?>&monto=<?php $detalles['MONTO_TRANSACCION'] ?>&fecha_rec=<?php echo $fecha_rec ?>" onsubmit="return valida_envia();return false;">
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

                                                        <div class="col-auto">
                                                            <button type="button" class="btn btn-md btn-secondary" onclick="limpiarFormulario();"><i class="fa fa-times"></i> LIMPIAR</button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td align="right">
                                                    <a align="right" href="conciliaciones_transferencias_pendientes.php?"><button type="button" class="btn btn-md btn-danger"><i class="fa fa-plus"></i> VOLVER</button></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div><!--end card-header-->
                                <div class="card-body ">
                                    <form class="form-horizontal " id="validate" role="form" class="needs-validation" autocomplete="on">
                                        <div class="row text-start">
                                            <div class="col-md-12">

                                                <div class="form-group row text-center justify-content-between">
                                                    <div class="col-lg-8 d-flex align-items-center">
                                                        <label for="ordenante" class="col-lg-4 col-form-label">ORDENANTE</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="ordenante" id="ordenante" class="form-control" maxlength="50" autocomplete="off" value="<?= $rut_ordenante . ' - ' . htmlspecialchars($nom_ordenante, ENT_QUOTES, 'UTF-8') ?>"
                                                                disabled />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row text-center justify-content-between">
                                                    <div class="col-lg-4 d-flex align-items-center justify-content-end">
                                                        <label for="monto" class="col-lg-4 col-form-label">TRANSF</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="monto" id="monto" class="form-control" maxlength="50" autocomplete="off" value="$<?= $detalles['MONTO_TRANSACCION'] ?>" disabled />
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 d-flex align-items-start">
                                                        <label for="total" class="col-lg-3 col-form-label">TOTAL</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="total" id="total" class="form-control" maxlength="50" autocomplete="off" value=" " disabled style="display: none;" />
                                                            <input type="text" name="total2" id="total2" class="form-control" maxlength="50" autocomplete="off" value="$ " disabled />
                                                            <input type="hidden" name="es_entrecuentas" id="es_entrecuentas">
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                            </div>
                                        </div>
                                    </form>

                                    <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th class="col-1 font_mini_header"></th>
                                                <th class="font_mini_header">F. VENC</th>
                                                <th class="font_mini_header" style="display: none;">MONTO</th> <!-- Columna oculta -->
                                                <th class="font_mini_header">$ DOC</th>
                                                <th class="font_mini_header">OPERACIÓN</th>
                                                <th class="font_mini_header">RUT CTE</th>
                                                <th class="font_mini_header">CARTERA</th>
                                                <th class="font_mini_header">RUT DEUD</th>
                                                <th class="font_mini_header">DIF</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Consulta para obtener documentos asignados
                                            $sql3 = "{call [_SP_CONCILIACIONES_DIFERENCIAS_LISTA]}";
                                            $stmt3 = sqlsrv_query($conn, $sql3);

                                            if ($stmt3 === false) {
                                                die(print_r(sqlsrv_errors(), true));
                                            }

                                            while ($transferencia = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {

                                                // Variables de documento
                                                $f_venc         = isset($transferencia["F_VENC"]) ? $transferencia["F_VENC"]->format('Y-m-d') : 'holi';
                                                $id_documento   = isset($transferencia['ID_DOCDEUDORES']) ? $transferencia['ID_DOCDEUDORES'] : '';
                                                $monto_doc      = isset($transferencia['MONTO']) ? $transferencia['MONTO'] : '';
                                                $subproducto    = isset($transferencia["SUBPRODUCTO"]) ? $transferencia["SUBPRODUCTO"] : '';
                                                $n_doc          = isset($transferencia["N_DOC"]) ? $transferencia["N_DOC"] : '';
                                                $rut_deudor     = isset($transferencia["RUT_DEUDOR"]) ? $transferencia["RUT_DEUDOR"] : '';
                                                $prestamos      = isset($transferencia["DIFERENCIA"]) ? $transferencia["DIFERENCIA"] : '';

                                                // Generar HTML
                                            ?>
                                                <tr>
                                                    <td class="col-1" style="text-align: center;">
                                                        <input type="radio" class="iddocumento_radio" name="iddocumento_radio[]" value="<?php echo $id_documento . ',' . $prestamos; ?>" data-n-doc="<?php echo htmlspecialchars($n_doc); ?>" />
                                                    </td>
                                                    <td class="f_venc col-auto font_mini" id="f_venc"><?php echo $f_venc; ?></td>
                                                    <td class="valor col-auto font_mini" id="valor" style="display: none;"><?php echo $prestamos; ?></td>
                                                    <td class="monto_doc col-auto font_mini" id="monto_doc">$<?php echo number_format($monto_doc, 0, ',', '.'); ?></td>
                                                    <td class="n_doc col-auto font_mini" id="n_doc"><?php echo htmlspecialchars($transferencia["N_DOC"]); ?></td>
                                                    <td class="rut_cliente col-auto font_mini" id="rut_cliente"><?php echo $transferencia["RUT_CLIENTE"]; ?></td>
                                                    <td class="nom_cliente col-auto font_mini" id="nom_cliente"><?php echo $transferencia["NOM_CLIENTE"]; ?></td>
                                                    <td class="rut_deudor col-auto  font_mini" id="rut_deudor"><?php echo $transferencia["RUT_DEUDOR"]; ?></td>
                                                    <td class="monto_pareo col-auto font_mini" id="monto_pareo">$<?php echo number_format($prestamos, 0, ',', '.'); ?></td>
                                                </tr> <?php
                                                    }
                                                        ?>
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

<script language="javascript">
    // Incrusta el valor de PHP en una variable de JavaScript
    var cuenta = '<?= $cuenta ?>';

    $(document).ready(function() {
        $("#cliente").on('change', function() {
            $("#cliente option:selected").each(function() {
                var rut_cliente = $(this).val();
                var cuenta = '<?= $cuenta ?>'; // Verifica que esta variable esté definida en el contexto PHP

                if (rut_cliente && cuenta) {
                    $.post("get_cuentas_validar.php", {
                        cuenta: cuenta,
                        rut_cliente: rut_cliente
                    }, function(response) {
                        console.log("Respuesta del servidor: ", response); // Verifica la respuesta del servidor

                        // Si la respuesta ya es un objeto, no es necesario usar JSON.parse
                        var data = response;

                        // Asegúrate de que data sea un objeto
                        if (typeof data === 'object') {
                            var es_entrecuentas = data.es_entrecuentas;
                            var cuenta_correspondiente = data.cuenta_correspondiente;

                            document.getElementById('es_entrecuentas').value = es_entrecuentas;
                            console.log("es_entrecuentas: ", es_entrecuentas);
                            console.log("cuenta_correspondiente: ", cuenta_correspondiente);

                            if (es_entrecuentas == 1) {
                                Swal.fire({
                                    title: 'Advertencia',
                                    text: `El cliente elegido no corresponde a la cuenta donde se realizó la transferencia. El movimiento quedará como "entre-cuentas". La cuenta correspondiente es: ${cuenta_correspondiente}.`,
                                    icon: 'warning',
                                    confirmButtonText: 'OK'
                                });
                            }
                        } else {
                            console.error("La respuesta del servidor no es un objeto válido");
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        console.error("Error en la solicitud AJAX:", textStatus, errorThrown);
                    });
                } else {
                    console.error("rut_cliente o cuenta están indefinidos");
                }
            });
        });
    });
</script>

<script>
    function valida_envia() {
        var cliente = document.getElementById('cliente').value; // Obtén el RUT del cliente
        var checkboxes = document.querySelectorAll('.iddocumento_radio:checked');
        var nDocs = new Set();
        var estadoFinal = $('#estado').val(); // Obtén el estado calculado

        // Define la fecha actual
        var fechaActual = new Date();
        fechaActual.setHours(0, 0, 0, 0); // Establece la hora a 00:00:00 para comparar solo las fechas

        // Verificar si el cliente RUT es 96509669
        if (cliente == 96509669) {
            // Validación: Verificar que no haya checkboxes seleccionados con el mismo número de operación
            for (var checkbox of checkboxes) {
                var nDoc = checkbox.getAttribute('data-n-doc');
                if (nDocs.has(nDoc)) {
                    Swal.fire({
                        width: 600,
                        icon: 'error',
                        title: 'El tipo cartera sólo permite seleccionar más de un documento cuando estos tienen distinto numero de operación.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    return false; // Cancelar el envío del formulario
                }
                nDocs.add(nDoc);
            }

            // Validación adicional basada en el estado calculado
            if (estadoFinal === "ABONADO") {
                Swal.fire({
                    title: 'Confirmación',
                    text: 'Este tipo de cartera no permite abonos, ¿Desea continuar con el envío del formulario de todos modos?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return false; // Cancelar el envío del formulario si el usuario cancela
                    } else {
                        // Si se confirma, continuar con el envío del formulario
                        document.getElementById('form_concilia').submit();
                    }
                });
                return false; // Evitar el envío inmediato del formulario
            }
        }

        // Nueva validación: Confirmación del usuario si hay fechas de vencimiento futuras
        var confirmacionRequerida_fecha = false;

        for (var checkbox of checkboxes) {
            var fechaVencimientoStr = checkbox.value.split(',')[2]; // Extrae la fecha de vencimiento como cadena
            var fechaVencimiento = new Date(fechaVencimientoStr); // Convierte la cadena en un objeto Date
            fechaVencimiento.setHours(0, 0, 0, 0); // Establece la hora en 00:00:00 para comparaciones de solo fecha

            // Obtener la fecha actual
            var fechaActual = new Date();
            fechaActual.setHours(0, 0, 0, 0); // Establece la hora en 00:00:00 para comparaciones de solo fecha

            if (fechaVencimiento > fechaActual) { // Compara con la fecha actual
                confirmacionRequerida_fecha = true;
                break; // Salir del bucle si se encuentra una fecha futura
            }
        }

        if (confirmacionRequerida_fecha) {
            Swal.fire({
                title: 'Confirmación',
                text: 'Algunos documentos tienen fechas de vencimiento futuras. ¿Desea continuar con el envío del formulario?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return false; // Cancelar el envío del formulario si el usuario cancela
                } else {
                    // Si se confirma, continuar con el envío del formulario
                    document.getElementById('form_concilia').submit();
                }
            });
            return false; // Evitar el envío inmediato del formulario
        }

        // Si no se requiere confirmación, permitir el envío del formulario
        return true;

        // Nueva validación: Confirmación del usuario si el estado del documento es PAGADO o NO VIGENTE
        var confirmacionRequerida = false;

        for (var checkbox of checkboxes) {
            // Obtener la fila del checkbox seleccionado
            var fila = checkbox.closest('tr');
            var estadoDoc = fila.querySelector('.estado_doc').textContent.trim(); // Obtén el estado del documento de la celda correspondiente

            if (estadoDoc === 'PAGADO' || estadoDoc === 'NO VIGENTE') {
                confirmacionRequerida = true;
                break;
            }
        }

        if (confirmacionRequerida) {
            Swal.fire({
                title: 'Confirmación',
                text: 'Algunos documentos están en estado PAGADO o NO VIGENTE. ¿Desea continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return false; // Cancelar el envío del formulario si el usuario cancela
                } else {
                    // Si se confirma, continuar con el envío del formulario
                    document.getElementById('form_concilia').submit();
                }
            });
            return false; // Evitar el envío inmediato del formulario
        }
        // Si no se requiere confirmación, permitir el envío del formulario
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
                },
                {
                    targets: [2],
                    visible: false
                }
            ],
            order: [
                [1, 'asc']
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