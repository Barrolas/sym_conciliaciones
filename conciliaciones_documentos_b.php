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


$op             = isset($_GET["op"]) ? $_GET["op"] : 0;

$rut_ordenante  = $_GET["rut_ordenante"];
$transaccion    = $_GET["transaccion"];
$rut_deudor     = $_GET["rut_deudor"];
$cuenta         = $_GET["cuenta"];

$sql_tr_asig     = "{call [_SP_CONCILIACIONES_TRANSFERENCIAS_ASIGNADAS_CONSULTA](?, ?)}";
$params_tr_asig = array(
    array($rut_ordenante,   SQLSRV_PARAM_IN),
    array($transaccion,     SQLSRV_PARAM_IN)
);
//print_r($params1);
//exit;
$stmt_tr_asig = sqlsrv_query($conn, $sql_tr_asig, $params_tr_asig);
if ($stmt_tr_asig === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_tr_asig'.");
}
$gestion = sqlsrv_fetch_array($stmt_tr_asig, SQLSRV_FETCH_ASSOC);

$existe     = 0;
$idestado   = 0;
$estado     = '';
$monto_ingresado = 0;
$monto_diferencia = 0;

$sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
        from Transferencias_Recibidas_Hist";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    mostrarError("Error al ejecutar la consulta 'ultima_cartola'.");
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

        .form-control {
            height: 28px;
            /* Ajusta la altura según sea necesario */
        }
    </style>
    <style>
        @media (min-width: 1000px) and (max-width: 1299px) {
            .font_mini {
                font-size: 11px !important;
            }

            .font_mini_input {
                font-size: 12px !important;

            }

            .font_mini_header {
                font-size: 11px !important;
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
                                        <label class="col-12" for="fecha_ultima_cartola">ÚLT ACTUALIZACIÓN</label>
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

                        <form id="form_concilia" method="post" class="mr-0" action="conciliaciones_pareos_guardar.php?rut_ordenante=<?php echo $rut_ordenante ?>&transaccion=<?php echo $transaccion ?>&rut_deudor=<?php echo $rut_deudor ?>&cuenta=<?php echo $cuenta ?>&monto=<?php echo $gestion["MONTO"] ?>&fecha_rec=<?php echo $gestion["FECHA"] ?>&monto_diferencia=<?php echo isset($monto_diferencia) ? $monto_diferencia : '0' ?>&op=2" onsubmit="return valida_envia();return false;">
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
                                                    <div class="col-lg-8 d-flex align-items-center">
                                                        <label for="ordenante" class="col-lg-4 col-form-label">ORDENANTE</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="ordenante" id="ordenante" class="form-control" maxlength="50" autocomplete="off" value="<?= $gestion["RUT"] . ' - ' . htmlspecialchars($gestion["NOMBRE"], ENT_QUOTES, 'UTF-8') ?>"
                                                                disabled />
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 d-flex align-items-center justify-content-end">
                                                        <label for="estado" class="col-lg-3 col-form-label">ESTADO</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="estado" id="estado" class="form-control" maxlength="50" autocomplete="off" value="<?php echo htmlspecialchars($estado); ?>" disabled />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row text-center justify-content-between">
                                                    <div class="col-lg-4 d-flex align-items-center justify-content-end">
                                                        <label for="monto" class="col-lg-4 col-form-label">TRANSF</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="monto" id="monto" class="form-control" maxlength="50" autocomplete="off" value="$<?= $gestion["MONTO"] ?>" disabled />
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 d-flex align-items-center justify-content-end">
                                                        <label for="monto_diferencia" class="col-lg-3 col-form-label">MONTO</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="monto_diferencia" id="monto_diferencia" class="form-control monto_diferencia" maxlength="50" autocomplete="off" value="<?php echo $monto_diferencia; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 d-flex align-items-start">
                                                        <label for="total" class="col-lg-3 col-form-label">TOTAL</label>
                                                        <div class="col-lg-8">
                                                            <input type="text" name="total" id="total" class="form-control" maxlength="50" autocomplete="off" value=" " disabled style="display: none;" />
                                                            <input type="text" name="total2" id="total2" class="form-control" maxlength="50" autocomplete="off" value="$ " disabled />
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
                                                                    mostrarError("Error al ejecutar la consulta 'stmt_cliente'.");
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
                                                            <?php $sql_gest = "{call [_SP_CONCILIACIONES_GESTIONES_CONSULTA_TRANSFERENCIA](?)}";
                                                            $params_gest = array($transaccion);
                                                            $stmt_gest = sqlsrv_query($conn, $sql_gest, $params_gest);
                                                            if ($stmt_gest === false) {
                                                                mostrarError("Error al ejecutar la consulta 'stmt_gest'.");
                                                            }
                                                            while ($gestiones = sqlsrv_fetch_array($stmt_gest, SQLSRV_FETCH_ASSOC)) {
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
                                                <th class="col-1 font_mini_header"></th>
                                                <th class="font_mini_header">F. VENC</th>
                                                <th class="font_mini_header" style="display: none;">MONTO</th> <!-- Columna oculta -->
                                                <th class="font_mini_header">$ DOC</th>
                                                <th class="font_mini_header">$ INGRESADO</th>
                                                <th class="font_mini_header">OPERACIÓN</th>
                                                <th class="font_mini_header">RUT CTE</th>
                                                <th class="font_mini_header">CARTERA</th>
                                                <th class="font_mini_header">SUBPROD</th>
                                                <th class="font_mini_header">E° DOC</th>
                                                <th class="font_mini_header">E° PAREO</th>
                                                <th class="font_mini_header">$ AB/PD</th>
                                                <th class="font_mini_header" style="display: none;">$ PAREO</th> <!-- Columna oculta -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Consulta para obtener documentos asignados
                                            $sql_doc_asig = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ASIGNADAS](?)}";
                                            $params_doc_asig = array($rut_deudor);
                                            $stmt_doc_asig = sqlsrv_query($conn, $sql_doc_asig, $params_doc_asig);

                                            if ($stmt_doc_asig === false) {
                                                mostrarError("Error al ejecutar la consulta 'stmt_doc_asig'.");
                                            }

                                            while ($transferencia = sqlsrv_fetch_array($stmt_doc_asig, SQLSRV_FETCH_ASSOC)) {

                                                // Variables de documento
                                                $f_venc         = (new DateTime($transferencia["F_VENC"]))->format('Y/m/d');
                                                $id_documento   = isset($transferencia["ID_DOCUMENTO"]) ? $transferencia["ID_DOCUMENTO"] : '';
                                                $monto_doc      = isset($transferencia["MONTO"]) ? $transferencia["MONTO"] : '';
                                                $fecha_venc     = isset($transferencia["F_VENC"]) ? $transferencia["F_VENC"] : 'holi';
                                                $subproducto    = isset($transferencia["SUBPRODUCTO"]) ? $transferencia["SUBPRODUCTO"] : '';
                                                $n_doc          = isset($transferencia["N_DOC"]) ? $transferencia["N_DOC"] : '';

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

                                                // Consulta para obtener el monto de abonos (solo si el estado no es '1')
                                                $sql_doc_ab = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ABONOS](?)}";
                                                $params_doc_ab = array($id_documento);
                                                $stmt_doc_ab = sqlsrv_query($conn, $sql_doc_ab, $params_doc_ab);

                                                if ($stmt_doc_ab === false) {
                                                    mostrarError("Error al ejecutar la consulta 'stmt_doc_ab'.");
                                                $monto_pareo = 0; // Inicializa en 0
                                                while ($abonos = sqlsrv_fetch_array($stmt_doc_ab, SQLSRV_FETCH_ASSOC)) {
                                                    $monto_pareo = isset($abonos["MONTO_PAREO"]) ? $abonos["MONTO_PAREO"] : 0;
                                                }

                                                // Consulta para obtener el estado del documento
                                                $sql_doc_estado = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ESTADO](?)}";
                                                $params_doc_estado = array($id_documento);
                                                $stmt_doc_estado = sqlsrv_query($conn, $sql_doc_estado, $params_doc_estado);

                                                if ($stmt_doc_estado === false) {
                                                    mostrarError("Error al ejecutar la consulta 'stmt_doc_estado'.");
                                                }

                                                $estado_pareo_text = 'N/A'; // Valor por defecto
                                                while ($estados = sqlsrv_fetch_array($stmt_doc_estado, SQLSRV_FETCH_ASSOC)) {
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

                                                // Generar HTML
                                            ?>
                                                <tr>
                                                    <td class="col-1" style="text-align: center;">
                                                        <input type="checkbox" class="iddocumento_checkbox" name="iddocumento_checkbox[]" value="<?php echo $id_documento . ',' . $monto_doc . ',' . $fecha_venc . ',' . $subproducto . ',' . $monto_pareo . ',' . $monto_ingresado; ?>" data-n-doc="<?php echo htmlspecialchars($n_doc); ?>" />
                                                    </td>
                                                    <td class="f_venc col-auto font_mini" id="f_venc"><?php echo $f_venc; ?></td>
                                                    <td class="valor col-auto font_mini" id="valor" style="display: none;"><?php echo $transferencia["MONTO"]; ?></td>
                                                    <td class="valor2 col-auto font_mini" id="valor_cuota2">$<?php echo number_format($transferencia["MONTO"], 0, ',', '.'); ?></td>
                                                    <td class="interes col-auto font_mini" id="interes">
                                                        <input type="text" value="<?php echo $monto_ingresado; ?>" class="monto_ingresado font_mini_input form-control" disabled />
                                                    </td>
                                                    <td class="n_doc col-auto font_mini" id="n_doc"><?php echo htmlspecialchars($transferencia["N_DOC"]); ?></td>
                                                    <td class="rut_cliente col-auto font_mini" id="rut_cliente"><?php echo $transferencia["RUT_CLIENTE"]; ?></td>
                                                    <td class="nom_cliente col-auto font_mini" id="nom_cliente"><?php echo $transferencia["NOM_CLIENTE"]; ?></td>
                                                    <td class="subproducto col-auto font_mini" id="subproducto"><?php echo $transferencia["SUBPRODUCTO"]; ?></td>
                                                    <td class="estado_doc col-auto font_mini" id="estado_doc"><?php echo $estado_doc_text; ?></td>
                                                    <td class="estado_pareo col-auto font_mini" id="estado_pareo"><?php echo $estado_pareo_text; ?></td>
                                                    <td class="monto_pareo col-auto font_mini" id="monto_pareo">$<?php echo number_format($monto_pareo, 0, ',', '.'); ?></td>
                                                    <td class="monto_pareo_oculto col-auto font_mini" id="monto_pareo_oculto" style="display: none;"><?php echo $monto_pareo; ?></td>
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
$(document).ready(function() {
    // Función para formatear números con separadores de miles
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Función para limpiar el número formateado y obtener el valor numérico real
    function cleanNumber(numStr) {
        return parseFloat(numStr.replace(/\./g, '').replace(',', '.')) || 0;
    }

    // Inicializar DataTable
    var table = $('#datatable2').DataTable({
        responsive: true,
        columnDefs: [{
                targets: [0],
                orderable: false
            },
            {
                targets: [2, 12],
                visible: false
            }
        ],
        order: [
            [9, 'desc'],
            [1, 'asc']
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('id', 'row-' + dataIndex);
        }
    });

    // Actualizar Total
    function actualizarTotal() {
        let total = 0;

        $('#datatable2 .iddocumento_checkbox:checked').each(function() {
            var rowId = $(this).closest('tr').attr('id');
            var rowData = table.row('#' + rowId).data();
            var montoPareo = parseFloat(rowData[12]) || 0;

            var montoInteres = cleanNumber($('.monto_ingresado').filter(function() {
                return $(this).closest('tr').attr('id') === rowId;
            }).val());

            var valor = montoInteres > 0 ? montoInteres : parseFloat(rowData[2]) || 0;

            total += (valor - montoPareo);
        });

        total = Math.max(total, 0);

        $('#total2').val('$' + formatNumber(total));

        var montoOriginal = <?php echo intval(preg_replace('/[^0-9]/', '', $gestion['MONTO'])); ?>;
        var montoDiferencia = cleanNumber($('#monto_diferencia').val());

        if (montoDiferencia > 0) {
            montoOriginal = montoDiferencia;
        }

        var estado;
        if (montoOriginal > total && total > 0) {
            estado = "EXCEDIDO";
        } else if (montoOriginal < total) {
            estado = "ABONADO";
        } else if (montoOriginal == total) {
            estado = "CONCILIADO";
        } else {
            estado = "";
        }

        $('#estado').val(estado);
        updateConciliarButton();
    }

    // Manejar cambios en los checkboxes
    $('#datatable2').on('change', '.iddocumento_checkbox', function() {
        var rowId = $(this).closest('tr').attr('id');
        var isChecked = $(this).is(':checked');
        var $inputInteres = $('.monto_ingresado').filter(function() {
            return $(this).closest('tr').attr('id') === rowId;
        });

        if (isChecked) {
            $inputInteres.prop('disabled', false);
            if ($inputInteres.val().trim() === '') {
                $inputInteres.val('0');
            }
        } else {
            $inputInteres.prop('disabled', true).val('0');
        }

        actualizarTotal();
        updateConciliarButton();
    });

    // Manejar el formato de monto_ingresado
    $('#datatable2').on('input', '.monto_ingresado', function() {
        var $input = $(this);
        var cleanValue = cleanNumber($input.val());

        if ($input.val().trim() === '') {
            $input.val('0');
        } else {
            $input.val(formatNumber(cleanValue));
        }

        actualizarTotal();
    });

    // Manejar el formato y validación de monto_diferencia
    function validarMontoDiferencia() {
        var $input = $('#monto_diferencia');
        var montoOriginal = <?php echo intval(preg_replace('/[^0-9]/', '', $gestion['MONTO'])); ?>;
        var montoDiferencia = cleanNumber($input.val());

        if (montoDiferencia === 0) {
            $input.val('0');
            return;
        }

        if (montoDiferencia > (montoOriginal + 1000)) {
            montoDiferencia = montoOriginal + 1000;
        } else if (montoDiferencia < (montoOriginal - 1000)) {
            montoDiferencia = montoOriginal - 1000;
        }

        if (montoDiferencia === montoOriginal) {
            montoDiferencia = 0;
        }

        $input.val(formatNumber(montoDiferencia));
    }

    $('#monto_diferencia').on('input', function() {
        $(this).val(formatNumber(cleanNumber($(this).val())));
    });

    $('#monto_diferencia').on('blur', function() {
        validarMontoDiferencia();
        actualizarTotal();
    });

    function updateConciliarButton() {
        var checkedCount = $('#datatable2 .iddocumento_checkbox:checked').length;
        var clienteSeleccionado = $('#cliente').val();

        $('#conciliarButton').prop('disabled', !(checkedCount > 0 && clienteSeleccionado !== "0"));
    }

    updateConciliarButton();
    $('#datatable2').on('change', '.iddocumento_checkbox', updateConciliarButton);
    $('#cliente').on('change', updateConciliarButton);

    // Actualizar el valor de los checkboxes con los valores actuales de monto_ingresado
    $('#form_concilia').on('submit', function(event) {
        $('#datatable2 .iddocumento_checkbox:checked').each(function() {
            var $checkbox = $(this);
            var rowId = $checkbox.closest('tr').attr('id');
            var $inputInteres = $('.monto_ingresado').filter(function() {
                return $(this).closest('tr').attr('id') === rowId;
            });

            // Actualizar el valor del checkbox con el valor actual del input monto_ingresado
            var checkboxValue = $checkbox.val().split(',');
            checkboxValue[5] = cleanNumber($inputInteres.val()); // Actualizar monto_ingresado
            $checkbox.val(checkboxValue.join(','));
        });

        var montoDiferencia = cleanNumber($('#monto_diferencia').val());
        var formAction = $(this).attr('action');

        // Reemplazar el valor de monto_diferencia en el action del formulario
        var actionUpdated = formAction.replace(/&monto_diferencia=[^&]*/, '&monto_diferencia=' + encodeURIComponent(montoDiferencia));
        $(this).attr('action', actionUpdated);
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