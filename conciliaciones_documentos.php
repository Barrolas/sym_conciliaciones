<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$op             = isset($_GET["op"]) ? $_GET["op"] : 0;
$idusuario      = $_SESSION['ID_USUARIO'];
$rut_ordenante  = $_GET["rut_ordenante"];
$transaccion    = $_GET["transaccion"];
$rut_deudor     = isset($_POST["rut_deudor"]) ? $_POST["rut_deudor"] : '';

$sql1     = "{call [_SP_CONCILIACIONES_TRANSFERENCIAS_PENDIENTES_CONSULTA](?, ?)}";

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

$sql2     = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_EXISTE](?, ?)}";

$params2 = array(
    array($rut_deudor,   SQLSRV_PARAM_IN),
    array(&$existe,      SQLSRV_PARAM_OUT)
);
//print_r($params1);
//exit;
$stmt2 = sqlsrv_query($conn, $sql2, $params2);
if ($stmt2 === false) {
    echo "Error in executing statement 2.\n";
    die(print_r(sqlsrv_errors(), true));
}
$rut_existe = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);

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
    <!-- Plugins -->
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
    <style>
        .custom-size {
            font-size: 15px;
            /* search button */
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

        <!-- Page Content-->
        <div class="page-content">
            <div class="container-fluid">
                <!-- Page-Title -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="row">
                                <div class="col">
                                    <ol class="breadcrumb">
                                        <?php if ($_SESSION["TIPO"] == 1) { ?>
                                            <li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
                                        <?php } else { ?>
                                            <li class="breadcrumb-item"><a href="menu_gestor.php">Inicio</a></li>
                                        <?php }; ?>
                                        <li class="breadcrumb-item"><a href="conciliaciones_transferencias_pendientes.php">Transferencias pendientes</a></li>
                                        <li class="breadcrumb-item active">Asignar conciliación</li>
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
                                <b>Asignar conciliación</b>
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
                        <div class="row text-start">
                            <div class="col-md-12">
                            </div>
                        </div>

                        <?php if ($existe == 1) { ?>
                            <form id="form_concilia" method="post" class="mr-0" action="conciliaciones_guardar.php?rut_ordenante=<?php echo $rut_ordenante ?>&transaccion=<?php echo $transaccion ?>&rut_deudor=<?php echo $rut_deudor ?>&op=1" onsubmit="return valida_envia2();return false;">
                                <div class="card ">
                                    <div class="card-header" style="background-color: #0055a6">
                                        <table width="100%" border="0" cellspacing="2" cellpadding="0">
                                            <tbody>
                                                <tr style="background-color:#0055a6">
                                                    <td align="left">
                                                        <!-- Formulario de búsqueda -->
                                                        <div class="form-group row align-items-center mb-0">
                                                            <label for="rut_deudor" class="col-form-label text-white px-3 mb-0"><b>INGRESE RUT DEUDOR</b></label>
                                                            <div class="col-auto py-0 pr-0">
                                                                <input id="rut_deudor" type="text" name="rut_deudor" maxlength="8" class="form-control mb-0" placeholder="Sin dígito verificador" value="<?php echo htmlspecialchars($rut_deudor); ?>">
                                                            </div>
                                                            <div class="col-auto mr-0">
                                                                <button type="submit" id="conciliarButton" class="btn btn-md btn-info mr-0" disabled><i class="fa fa-plus"></i> CONCILIAR</button>
                                                            </div>
                                                            <div class="col-auto">
                                                                <button type="button" class="btn btn-md btn-secondary" onclick="limpiarFormulario();"><i class="fa fa-times"></i> LIMPIAR</button>
                                                            </div>
                                                        </div>
                                                    </td>

                                                <?php } else { ?>
                                                    <form id="form_busqueda" method="post" action="conciliaciones_documentos.php?rut_ordenante=<?php echo $rut_ordenante ?>&transaccion=<?php echo $transaccion ?>" onsubmit="return valida_envia1();">
                                                        <div class="card ">
                                                            <div class="card-header" style="background-color: #0055a6">
                                                                <table width="100%" border="0" cellspacing="2" cellpadding="0">
                                                                    <tbody>
                                                                        <tr style="background-color:#0055a6">
                                                                            <td align="left">
                                                                                <!-- Formulario de búsqueda -->
                                                                                <div class="form-group row align-items-center mb-0">
                                                                                    <label for="rut_deudor" class="col-form-label text-white px-3 mb-0"><b>INGRESE RUT DEUDOR</b></label>
                                                                                    <div class="col-auto py-0 pr-0">
                                                                                        <input id="rut_deudor" type="text" name="rut_deudor" maxlength="8" class="form-control mb-0" placeholder="Sin dígito verificador" value="<?php echo htmlspecialchars($rut_deudor); ?>">
                                                                                    </div>
                                                                                    <div class="col-auto p-0">
                                                                                        <button type="submit" class="btn btn-md btn-secondary ml-0 mb-0" id="search_button" style="transform: translateX(-6px);"><i class="fa fa-search custom-size py-1"></i></button>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                        <?php }; ?>
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
                                                                                <div class="col-lg-7 d-flex align-items-center">
                                                                                    <label for="ordenante" class="col-lg-4 col-form-label">ORDENANTE</label>
                                                                                    <div class="col-lg-8">
                                                                                        <input type="text" name="ordenante" id="ordenante" class="form-control" maxlength="50" autocomplete="off" value="<?= $gestion["RUT"] . ' - ' . $gestion["NOMBRE"] ?>" disabled />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-5 d-flex align-items-center justify-content-end">
                                                                                    <label for="cliente" class="col-lg-4 col-form-label">TRANSACCION</label>
                                                                                    <div class="col-lg-8">
                                                                                        <input type="text" name="cliente" id="cliente" class="form-control" maxlength="50" autocomplete="off" value="<?= $gestion["TRANSACCION"] . ' - ' . $gestion["FECHA"] ?>" disabled />
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
                                                                                    <label for="total" class="col-lg-2 col-form-label">TOTAL</label>
                                                                                    <div class="col-lg-8">
                                                                                        <input type="text" name="total" id="total" class="form-control" maxlength="50" autocomplete="off" value=" " disabled style="display: none;" />
                                                                                        <input type="text" name="total2" id="total2" class="form-control" maxlength="50" autocomplete="off" value="$ " disabled />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-4 d-flex align-items-center justify-content-end">
                                                                                    <label for="estado" class="col-lg-4 col-form-label">ESTADO</label>
                                                                                    <div class="col-lg-8">
                                                                                        <input type="text" name="estado" id="estado" class="form-control" maxlength="50" autocomplete="off" value="<?php echo htmlspecialchars($estado); ?>" disabled />
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                </form>

                                                                <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>F. VENC</th>
                                                                            <th style="display: none;">MONTO</th> <!-- Columna oculta -->
                                                                            <th>MONTO</th>
                                                                            <th>N° DOC</th>
                                                                            <th>RUT CLIENTE</th>
                                                                            <th>CLIENTE</th>
                                                                            <th>ESTADO</th>
                                                                            <th class="col-1">SELECCIÓN</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        if ($rut_deudor != 0) {
                                                                            $sql = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES](?)}";
                                                                            $params = array($rut_deudor);
                                                                            $stmt = sqlsrv_query($conn, $sql, $params);
                                                                            if ($stmt === false) {
                                                                                die(print_r(sqlsrv_errors(), true));
                                                                            }
                                                                            while ($transferencia = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
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

                                                                                $f_venc = $transferencia["F_VENC"];
                                                                                if ($f_venc instanceof DateTime) {
                                                                                    $f_venc = $f_venc->format('Y-m-d');
                                                                                }
                                                                        ?>
                                                                                <tr>
                                                                                    <td class="f_venc" id="f_venc"><?php echo $f_venc; ?></td>
                                                                                    <td class="valor" id="valor" style="display: none;"><?php echo $transferencia["MONTO"]; ?></td> <!-- Celda oculta -->
                                                                                    <td class="valor2" id="valor_cuota2">$<?php echo number_format($transferencia["MONTO"], 0, ',', '.'); ?></td>
                                                                                    <td class="n_doc" id="n_doc"><?php echo htmlspecialchars($transferencia["N_DOC"]); ?></td>
                                                                                    <td class="rut_cliente" id="rut_cliente"><?php echo $transferencia["RUT_CLIENTE"]; ?></td>
                                                                                    <td class="nom_cliente" id="nom_cliente"><?php echo $transferencia["NOM_CLIENTE"]; ?></td>
                                                                                    <td class="estado_doc" id="estado_doc"><?php echo $estado_doc_text; ?></td>
                                                                                    <td style="text-align: center;">
                                                                                        <input type="checkbox" class="iddocumento_checkbox" name="iddocumento_checkbox[]" id="iddocumento_checkbox[]" value="<?php echo $transferencia["ID_DOCUMENTO"].','.$transferencia["MONTO"]; ?>" />
                                                                                    </td>
                                                                                </tr>
                                                                        <?php
                                                                            }
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
    function valida_envia1() {
        var rutDeudor = document.getElementById('rut_deudor').value;
        if (rutDeudor.length < 7 || rutDeudor.length > 8) {
            Swal.fire({
                width: 600,
                icon: 'error',
                title: 'Debe ingresar un RUT entre 7 y 8 dígitos.',
                showConfirmButton: false,
                timer: 2000
            });
            return false; // Devuelve false para cancelar el envío del formulario
        }
        return true; // Devuelve true para permitir el envío del formulario
    }
</script>

<script>
    // Variable para mantener el total de los valores seleccionados
    // Función para formatear números con separadores de miles
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // DataTables Initialization
    $(document).ready(function() {
        var table = $('#datatable2').DataTable({
            responsive: true,
            columnDefs: [{
                    targets: [1],
                    visible: false
                } 
            ],
            order: [
                [6, 'desc'],
                [0, 'asc']
            ]
        });

        // Evento para los checkboxes
        var total = 0;
        $('#datatable2').on('change', '.iddocumento_checkbox', function() {
            // Parsea el monto PHP a entero en JavaScript
            var montoParseado = <?php echo intval(preg_replace('/[^0-9]/', '', $gestion['MONTO'])); ?>;
            // Obtener el índice de la fila
            var rowIndex = $(this).closest('tr').index();
            // Obtener los datos de la fila desde DataTables
            var rowData = table.row(rowIndex).data();
            // Obtener el valor de la columna oculta (suponiendo que es numérico)
            var valor = parseFloat(rowData[1]); // Suponiendo que la columna oculta es la segunda columna (índice 1)

            if ($(this).is(':checked')) {
                // Sumar el valor al total si el checkbox está marcado
                total += valor;
            } else {
                // Restar el valor del total si el checkbox está desmarcado
                total -= valor;
            }

            // Comparar el total con montoParseado y actualizar el estado
            var estado;
            //alert(montoParseado)
            //alert(total)
            if (montoParseado > total && total > 0) {
                estado = "EXCEDIDO";
            } else if (montoParseado < total) {
                estado = "ABONADO";
            } else if (montoParseado = total) {
                estado = "CONCILIADO";
            }

            // Actualizar el valor de la entrada total en la interfaz con formato
            $('#total2').val('$' + formatNumber(total));

            // Actualizar el valor del campo de entrada de estado
            $('#estado').val(estado);

        });
    });
</script>

<script>
    $('#datatable2').on('change', '.iddocumento_checkbox', function() {
        // Obtener el índice de la fila
        var rowIndex = $(this).closest('tr').index();

        // Obtener el monto_checkbox de la misma fila y marcarlo si iddocumento_checkbox está marcado
        var montoCheckbox = $(this).closest('tr').find('.monto_checkbox');
        montoCheckbox.prop('checked', this.checked);
    });
</script>


<script>
    $(document).ready(function() {
        // Función para actualizar el estado del botón de CONCILIAR
        function updateConciliarButton() {
            var checkboxes = $('#datatable2 .iddocumento_checkbox');
            var checkedCount = checkboxes.filter(':checked').length;
            $('#conciliarButton').prop('disabled', checkedCount === 0);
        }

        // Llamar a la función al cargar la página
        updateConciliarButton();

        // Añadir evento change a todos los checkboxes para actualizar el botón cuando cambien
        $('#datatable2').on('change', '.iddocumento_checkbox', function() {
            updateConciliarButton();
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