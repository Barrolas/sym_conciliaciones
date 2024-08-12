<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$op             = isset($_GET["op"]) ? $_GET["op"] : 0;

$rut_ordenante  = $_GET["rut_ordenante"];
$transaccion    = $_GET["transaccion"];
$rut_deudor     = $_GET["rut_deudor"];
$cuenta         = $_GET["cuenta"];

$sql1     = "{call [_SP_CONCILIACIONES_TRANSFERENCIAS_ASIGNADAS_CONSULTA](?, ?)}";

$params1 = array(
    array($rut_ordenante,   SQLSRV_PARAM_IN),
    array($transaccion,     SQLSRV_PARAM_IN)
);
//print_r($params1);
//exit;
$stmt1 = sqlsrv_query($conn, $sql1, $params1);
if ($stmt1 === false) {
    echo "Error in executing statement 1.\n";
    die(print_r(sqlsrv_errors(), true));
}
$gestion = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC);

$existe     = 0;
$idestado   = 0;
$estado     = '';

$sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
        from [192.168.1.193].conciliacion.dbo.Transferencias_Recibidas_Hist";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$fecha_proceso = $row["FECHAPROCESO"];


//print($rut_deudor);
//exit;

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
                                        <li class="breadcrumb-item active">Parear documentos</li>
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
                                <b>Parear documentos</b>
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
                                        <label class="col-12" for="fecha_ultima_cartola">ÚLTIMA CARTOLA</label>
                                        <input type="text" class="form-control col-12" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="col-4" for="fecha_ultima_cartola">CUENTA</label>
                                        <input type="text" name="cuenta" id="cuenta" class="form-control col-12" maxlength="50" autocomplete="off" value="<?= $cuenta ?>" disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="col-4" for="fecha_ultima_cartola">TRANSACCIÓN</label>
                                        <input type="text" name="transaccion" id="transaccion" class="form-control col-12" maxlength="50" autocomplete="off" value="<?= $gestion["TRANSACCION"] . ' - ' . $gestion["FECHA"] ?>" disabled />
                                    </div>
                                </div><!--end form-group-->
                            </div><!--end col-->
                        </div>

                        <form id="form_concilia" method="post" class="mr-0" action="conciliaciones_guardar.php?rut_ordenante=<?php echo $rut_ordenante ?>&transaccion=<?php echo $transaccion ?>&rut_deudor=<?php echo $rut_deudor ?>&cuenta=<?php echo $cuenta ?>&monto=<?= $gestion["MONTO"] ?>&op=2" onsubmit="return valida_envia();return false;">
                            <div class="card ">
                                <div class="card-header" style="background-color: #0055a6">
                                    <table width="100%" border="0" cellspacing="2" cellpadding="0">
                                        <tbody>
                                            <tr style="background-color:#0055a6">
                                                <td align="left">
                                                    <!-- Formulario de búsqueda -->
                                                    <div class="form-group row align-items-center mb-0">
                                                        <label for="rut_deudor" class="col-form-label text-white px-3 mb-0"><b>RUT DEUDOR</b></label>
                                                        <div class="col-auto py-0 pr-0">
                                                            <input id="rut_deudor" type="text" name="rut_deudor" maxlength="8" class="form-control mb-0" placeholder="Sin dígito verificador" value="<?php echo htmlspecialchars($rut_deudor); ?>" disabled>
                                                        </div>
                                                        <div class="col-auto mr-0">
                                                            <button type="submit" id="conciliarButton" class="btn btn-md btn-info mr-0" disabled><i class="fa fa-plus"></i> PAREAR</button>
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
                                    <form class="form-asignado" id="validate" role="form" class="needs-validation" autocomplete="on">
                                        <div class="row text-start">
                                            <div class="col-md-12">

                                                <div class="form-group row text-center justify-content-between">
                                                    <div class="col-lg-7 d-flex align-items-center">
                                                        <label for="ordenante" class="col-lg-4 col-form-label">ORDENANTE</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="ordenante" id="ordenante" class="form-control" maxlength="50" autocomplete="off" value="<?= $gestion["RUT"] . ' - ' . $gestion["NOMBRE"] ?>" disabled />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row text-center justify-content-between">
                                                    <div class="col-lg-4 d-flex align-items-center justify-content-end">
                                                        <label for="monto" class="col-lg-4 col-form-label">MONTO</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="monto" id="monto" class="form-control" maxlength="50" autocomplete="off" value="$<?= $gestion["MONTO"] ?>" disabled />
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 d-flex align-items-start">
                                                        <label for="total" class="col-lg-3 col-form-label">TOTAL</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="total" id="total" class="form-control" maxlength="50" autocomplete="off" value=" " disabled style="display: none;" />
                                                            <input type="text" name="total2" id="total2" class="form-control" maxlength="50" autocomplete="off" value="$ " disabled />
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 d-flex align-items-center justify-content-end">
                                                        <label for="estado" class="col-lg-3 col-form-label">ESTADO</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="estado" id="estado" class="form-control" maxlength="50" autocomplete="off" value="<?php echo htmlspecialchars($estado); ?>" disabled />
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <div class="form-group row  justify-content-between">
                                                    <div class="col-lg-6 d-flex align-items-center">
                                                        <label for="cliente" class="col-lg-3 col-form-label">CLIENTE</label>
                                                        <div class="col-lg-9">
                                                            <select name="cliente" id="cliente" class="form-control" maxlength="50" autocomplete="off">
                                                                <option value="0" selected>Seleccione cliente a conciliar</option>
                                                                <?php
                                                                $sql_cliente = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_CLIENTES](?)}";
                                                                $params_cliente = array($rut_deudor);
                                                                $stmt_cliente = sqlsrv_query($conn, $sql_cliente, $params_cliente);

                                                                if ($stmt_cliente === false) {
                                                                    die(print_r(sqlsrv_errors(), true));
                                                                }
                                                                while ($cliente = sqlsrv_fetch_array($stmt_cliente, SQLSRV_FETCH_ASSOC)) {
                                                                ?>
                                                                    <option value="<?php echo $cliente["RUT_CLIENTE"] ?>"><?php echo $cliente["RUT_CLIENTE"] . " - " . $cliente["NOM_CLIENTE"] ?></option>
                                                                <?php }; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                                                        <label for="gestion" class="col-lg-2 col-form-label">GESTION</label>
                                                        <div id="gestion" class="scrollable-div border p-3 col-10 rounded">
                                                            <?php $sql4 = "{call [_SP_CONCILIACIONES_GESTIONES_CONSULTA_TRANSFERENCIA](?)}";
                                                            $params4 = array($transaccion);
                                                            $stmt4 = sqlsrv_query($conn, $sql4, $params4);
                                                            if ($stmt4 === false) {
                                                                die(print_r(sqlsrv_errors(), true));
                                                            }
                                                            while ($gestiones = sqlsrv_fetch_array($stmt4, SQLSRV_FETCH_ASSOC)) {
                                                                echo "<strong>" . $gestiones['FECHA_GESTION'] . ": </strong>" . $gestiones['OBSERVACION'] . "<br><hr>";
                                                            }; ?>
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
                                                <th class="col-1"></th>
                                                <th>F. VENC</th>
                                                <th style="display: none;">MONTO</th> <!-- Columna oculta -->
                                                <th>$ DOC</th>
                                                <th>OPERACIÓN</th>
                                                <th>RUT CTE</th>
                                                <th>CARTERA</th>
                                                <th>SUBPROD</th>
                                                <th>E° DOC</th>
                                                <th>E° PAREO</th>
                                                <th>$ AB/PD</th>
                                                <th style="display: none;">MONTO PAREO</th> <!-- Columna oculta -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql3 = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ASIGNADAS](?)}";
                                            $params3 = array($rut_deudor);
                                            $stmt3 = sqlsrv_query($conn, $sql3, $params3);
                                            if ($stmt3 === false) {
                                                die(print_r(sqlsrv_errors(), true));
                                            }
                                            while ($transferencia = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {
                                                $estado_doc = $transferencia["ESTADO_DOC"];
                                                switch ($estado_doc) {
                                                    case '001':
                                                        $estado_doc_text = 'VIGENTE';
                                                        break;
                                                    case '014':
                                                        $estado_doc_text = 'PAGADO';
                                                        break;
                                                    case '333':
                                                        $estado_doc_text = 'NO VIGENTE';
                                                        break;
                                                    default:
                                                        $estado_doc_text = $estado_doc;
                                                        break;
                                                }

                                                $f_venc         = (new DateTime($transferencia["F_VENC"]))->format('Y/m/d');
                                                $monto_pareo    = $transferencia["MONTO_PAREO"];
                                                $id_documento   = isset($transferencia["ID_DOCUMENTO"]) ?   $transferencia["ID_DOCUMENTO"] : '';
                                                $monto_doc      = isset($transferencia["MONTO"]) ?          $transferencia["MONTO"] : '';
                                                $fecha_venc     = isset($transferencia["F_VENC"]) ?         $transferencia["F_VENC"] : '';
                                                $subproducto    = isset($transferencia["SUBPRODUCTO"]) ?    $transferencia["SUBPRODUCTO"] : '';
                                                $estado_pareo   = isset($transferencia["ESTADO_PAREO"]) ?   $transferencia["ESTADO_PAREO"] : '';
                                                $monto_pareo    = isset($transferencia["MONTO_PAREO"]) ?    $transferencia["MONTO_PAREO"] : '';
                                                $n_doc          = isset($transferencia["N_DOC"]) ?          $transferencia["N_DOC"] : '';

                                            ?>

                                                <tr>
                                                    <td class="col-1" style="text-align: center;">
                                                        <input type="checkbox" class="iddocumento_checkbox" name="iddocumento_checkbox[]" value="<?php echo $id_documento . ',' . $monto_doc . ',' . $fecha_venc . ',' . $subproducto . ',' . $estado_pareo . ',' . $monto_pareo; ?>" data-n-doc="<?php echo htmlspecialchars($n_doc); ?>" />
                                                    </td>
                                                    <td class="f_venc col-auto" id="f_venc"><?php echo $f_venc; ?></td>
                                                    <td class="valor col-auto" id="valor" style="display: none;"><?php echo $transferencia["MONTO"]; ?></td> <!-- Celda oculta -->
                                                    <td class="valor2 col-auto" id="valor_cuota2">$<?php echo number_format($transferencia["MONTO"], 0, ',', '.'); ?></td>
                                                    <td class="n_doc col-auto" id="n_doc"><?php echo htmlspecialchars($transferencia["N_DOC"]); ?></td>
                                                    <td class="rut_cliente col-auto" id="rut_cliente"><?php echo $transferencia["RUT_CLIENTE"]; ?></td>
                                                    <td class="nom_cliente col-auto" id="nom_cliente"><?php echo $transferencia["NOM_CLIENTE"]; ?></td>
                                                    <td class="subproducto col-auto" id="subproducto"><?php echo $transferencia["SUBPRODUCTO"]; ?></td>
                                                    <td class="estado_doc col-auto" id="estado_doc"><?php echo $estado_doc_text; ?></td>
                                                    <td class="estado_pareo col-auto" id="estado_pareo"><?php echo $transferencia["ESTADO_PAREO"]; ?></td>
                                                    <td class="monto_pareo col-auto" id="monto_pareo">$<?php echo number_format($transferencia["MONTO_PAREO"], 0, ',', '.'); ?></td>
                                                    <td class="monto_pareo_oculto col-auto" id="monto_pareo_oculto" style="display: none;"><?php echo $monto_pareo; ?></td> <!-- Columna oculta -->
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div><!-- end card-body -->
                            </div><!-- end card -->
                        </form>
                    </div><!-- end col -->
                </div><!-- end row -->
            </div><!-- container-fluid -->
        </div><!-- page-content -->
        <!-- page-wrapper -->
        <?php include('footer.php'); ?>
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

<script>
    function valida_envia() {
        var cliente = document.getElementById('cliente').value; // Obtén el RUT del cliente
        var checkboxes = document.querySelectorAll('.iddocumento_checkbox:checked');
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
    // Variable para mantener el total de los valores seleccionados
    // Función para formatear números con separadores de miles
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    $(document).ready(function() {
        // Inicialización de DataTables
        var table = $('#datatable2').DataTable({
            responsive: true,
            columnDefs: [{
                    targets: [0], // Índice de la columna que no será ordenable
                    orderable: false
                },
                {
                    targets: [2, 11],
                    visible: false
                }
            ],
            order: [
                [8, 'desc'],
                [1, 'asc']
            ],
            createdRow: function(row, data, dataIndex) {
                // Asignar un ID único a cada fila basado en el índice
                $(row).attr('id', 'row-' + dataIndex);
            }
        });

        // Evento para los checkboxes
        var total = 0; // Inicializar el total en 0
        $('#datatable2').on('change', '.iddocumento_checkbox', function() {
            // Obtener el ID de la fila
            var rowId = $(this).closest('tr').attr('id');
            // Obtener los datos de la fila desde DataTables
            var rowData = table.row('#' + rowId).data();
            // Obtener el valor de la columna oculta (suponiendo que es numérico)
            var valor = parseFloat(rowData[2]); // Suponiendo que la columna oculta es la tercera columna (índice 2)
            var montoPareo = parseFloat(rowData[11]); // Obtener el valor del monto_pareo (índice 11)

            if ($(this).is(':checked')) {
                // Sumar el valor al total si el checkbox está marcado
                total += valor - montoPareo;
            } else {
                // Restar el valor del total si el checkbox está desmarcado
                total -= valor - montoPareo;
            }

            // Asegurarse de que el total no sea menor de cero
            if (total < 0) {
                total = 0;
            }

            // Actualizar el valor de la entrada total en la interfaz con formato
            $('#total2').val('$' + formatNumber(total));

            // Actualizar el estado del campo de entrada
            var estado;
            var montoParseado = <?php echo intval(preg_replace('/[^0-9]/', '', $gestion['MONTO'])); ?>;
            if (montoParseado > total && total > 0) {
                estado = "EXCEDIDO";
            } else if (montoParseado < total) {
                estado = "ABONADO";
            } else if (montoParseado == total) { // Corregir = a ==
                estado = "CONCILIADO";
            } else {
                estado = ""; // Estado vacío si no se cumple ninguna condición
            }

            // Actualizar el valor del campo de entrada de estado
            $('#estado').val(estado);

            // Actualizar el monto_checkbox de la misma fila
            var montoCheckbox = $(this).closest('tr').find('.monto_checkbox');
            montoCheckbox.prop('checked', this.checked);

            // Actualizar el botón CONCILIAR
            updateConciliarButton();
        });

        // Función para actualizar el estado del botón de CONCILIAR
        function updateConciliarButton() {
            var checkboxes = $('#datatable2 .iddocumento_checkbox');
            var checkedCount = checkboxes.filter(':checked').length;
            var clienteSeleccionado = $('#cliente').val(); // Usar .val() para obtener el valor seleccionado

            console.log("Checkboxes seleccionados: " + checkedCount); // Depuración
            console.log("Cliente seleccionado: " + clienteSeleccionado); // Depuración

            // Habilitar el botón solo si hay al menos un checkbox marcado y un cliente seleccionado distinto de 0
            var shouldEnableButton = checkedCount > 0 && clienteSeleccionado !== "0";
            console.log("Botón habilitado: " + shouldEnableButton); // Depuración

            $('#conciliarButton').prop('disabled', !shouldEnableButton);
        }

        // Llamar a la función al cargar la página
        updateConciliarButton();

        // Añadir evento change a todos los checkboxes y al select de cliente para actualizar el botón cuando cambien
        $('#datatable2').on('change', '.iddocumento_checkbox', function() {
            updateConciliarButton();
        });

        $('#cliente').on('change', function() {
            updateConciliarButton();
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Initialize the DataTable
        var table = $('#datatable2').DataTable();

        // Add event listener to the select element
        $('#cliente').on('change', function() {
            var filterValue = $(this).val();

            if (filterValue == "0") {
                // Clear all filters
                table.search('').columns().search('').draw();
            } else {
                // Use DataTables search() function to filter the table
                table.column(5).search(filterValue).draw();
            }
        });
    });
</script>

<script>
    function limpiarFormulario() {
        // Redireccionar para limpiar
        window.location.href = 'conciliaciones_documentos.php?rut_ordenante=<?php echo $rut_ordenante ?>&transaccion=<?php echo $transaccion ?>';
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