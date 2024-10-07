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
            <form id="form_concilia" method="post" class="mr-0" action="conciliaciones_canalizaciones_guardar.php" onsubmit="return valida_envia();return false;">
                <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="row">
                                    <div class="col">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
                                            <li class="breadcrumb-item active">Canalización</li>
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
                                <b>Conciliados</b>
                            </h3>
                        </div>
                        <div class="row mr-2">
                            <div class="col-12 mx-2">
                                <p>
                                    En este módulo se visualizan todos los casos a los que se les ha asignado un
                                    <b>N° CHEQUE</b> o <b>N° REMESA</b>, dependiendo de si fueron canalizados por cheque
                                    o por transferencia. Estos documentos ya han completado su proceso de conciliación
                                    y están listos para ser corroborados con la cartola bancaria en el siguiente paso.
                                    La asignación de estos números no solo facilita el seguimiento de las
                                    transacciones, sino que también asegura que cada documento esté adecuadamente
                                    clasificado y registrado, permitiendo una gestión más organizada y eficiente
                                    de los casos conciliados.
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
                                                die(print_r(sqlsrv_errors(), true));
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
                                    <button type="submit" class="btn btn-primary waves-effect waves-light mt-4" id="guardarButton" disabled>GUARDAR</button>
                                </div>
                            </div><!--end form-group-->
                        </div><!--end col-->
                    </div>

                    <div class="col-12 px-3">
                        <div class="card card_content">
                            <div class="card-body card_width">
                                <table id="datatable2" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="font_mini_header">
                                                <div class="d-flex flex-column align-items-center">
                                                    <input class="mb-2" type="checkbox" id="select_all_checkbox1" onclick="handleMasterCheckbox(1)">
                                                </div>
                                            </th>
                                            <th class="font_mini_header">ID CANAL</th>
                                            <th class="font_mini_header">ID</th>
                                            <th class="font_mini_header">N° REMESA/CHEQUE</th>
                                            <th class="font_mini_header">CANAL</th>
                                            <th class="font_mini_header">MONTO</th>
                                            <th class="font_mini_header">CUENTA</th>
                                            <th class="font_mini_header">CARTERA</th>
                                            <th class="font_mini_header">RUT DEU</th>
                                            <th class="font_mini_header">OPERACION</th>
                                            <th class="font_mini_header">F.VENC</th>
                                            <th class="font_mini_header">SUBPROD</th>
                                            <th class="font_mini_header">TIPO</th>
                                            <th class="font_mini_header">V.CUOTA</th>
                                            <th class="font_mini_header"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "EXEC [_SP_CONCILIACIONES_ASIGNADOS_LISTA]";
                                        $stmt = sqlsrv_query($conn, $sql);
                                        if ($stmt === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        while ($asignados = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

                                            $idpareo_sis    = $asignados['ID_PAREO_SISTEMA'];
                                            $iddoc          = $asignados['ID_DOCDEUDORES'];
                                            $id_asignacion  = $asignados['ID_ASIGNACION'];
                                            $n_cheque       = $asignados['N_CHEQUE'];
                                            $num_remesa       = $asignados['N_REMESA'];
                                            $disabled       = '';

                                            if (!($n_cheque == '' && $num_remesa == '')) {

                                                $sql_pd = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ID_PS](?)}";
                                                $params_pd = array(
                                                    array($idpareo_sis,     SQLSRV_PARAM_IN),
                                                );
                                                $stmt_pd = sqlsrv_query($conn, $sql_pd, $params_pd);
                                                if ($stmt_pd === false) {
                                                    die(print_r(sqlsrv_errors(), true));
                                                }
                                                $p_docs = sqlsrv_fetch_array($stmt_pd, SQLSRV_FETCH_ASSOC);

                                                $sql_row = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ID](?)}";
                                                $params_row = array(
                                                    array($iddoc,     SQLSRV_PARAM_IN),
                                                );
                                                $stmt_row = sqlsrv_query($conn, $sql_row, $params_row);
                                                if ($stmt_row === false) {
                                                    die(print_r(sqlsrv_errors(), true));
                                                }
                                                $row = sqlsrv_fetch_array($stmt_row, SQLSRV_FETCH_ASSOC);

                                                $cuenta = $p_docs['CUENTA_BENEFICIARIO'];

                                                $sql_qtydocs = "{call [_SP_CONCILIACIONES_CANALIZADOS_PROCESADOS_CANTIDAD_PAREO_SISTEMA](?)}";
                                                $params_qtydocs = array(
                                                    array($idpareo_sis,    SQLSRV_PARAM_IN),
                                                );
                                                $stmt_qtydocs = sqlsrv_query($conn, $sql_qtydocs, $params_qtydocs);
                                                if ($stmt_qtydocs === false) {
                                                    die(print_r(sqlsrv_errors(), true));
                                                }
                                                $qtydocs = sqlsrv_fetch_array($stmt_qtydocs, SQLSRV_FETCH_ASSOC);

                                                $sql_pagodocs = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_METODOS_PAGO](?)}";
                                                $params_pagodocs = array(
                                                    array($idpareo_sis,    SQLSRV_PARAM_IN),
                                                );
                                                $stmt_pagodocs = sqlsrv_query($conn, $sql_pagodocs, $params_pagodocs);
                                                if ($stmt_pagodocs === false) {
                                                    die(print_r(sqlsrv_errors(), true));
                                                }
                                                $pagodocs = sqlsrv_fetch_array($stmt_pagodocs, SQLSRV_FETCH_ASSOC);

                                                //Variables pareo sistema
                                                $deud_rut       = $asignados['DEUDOR_RUT'];
                                                $deud_dv        = $asignados['DEUDOR_DV'];
                                                $deud_nom       = $asignados['DEUDOR_NOMBRE'];
                                                $cant_docs      = $asignados['PAGO_DOCS'];
                                                $operacion      = $asignados['N_DOC'];
                                                $tipo_canal     = $asignados['ID_TIPO_CANALIZACION'];
                                                $monto_doc      = $asignados['MONTO_DOC'];
                                                $transaccion    = $asignados['TRANSACCION'];
                                                //Variables pareo docs
                                                $benef_cta      = $p_docs['CUENTA_BENEFICIARIO'];
                                                $cte_rut        = $p_docs['RUT_CLIENTE'];
                                                $f_recepcion    = $p_docs['FECHA_RECEPCION'];
                                                $f_venc         = isset($asignados['F_VENC']) ? $asignados['F_VENC']->format('Y-m-d') : '';
                                                $monto_tr       = $p_docs['MONTO_TRANSACCION'];
                                                $ord_rut        = $p_docs['ORDENANTE_RUT'];
                                                $ord_dv         = $p_docs['ORDENANTE_DV'];
                                                $ord_banco      = $p_docs['ORDENANTE_BANCO'];
                                                $ord_cta        = $p_docs['ORDENANTE_CUENTA'];

                                                $producto       = $row['SUBPRODUCTO'];
                                                $cartera        = $row['CARTERA'];
                                                $canal          = $p_docs['CANAL'];

                                                $pago_docs      = $pagodocs['DESCRIPCION_PAGOS'];

                                                if ($tipo_canal == 1) {

                                                    $sql_cheque = "{call [_SP_CONCILIACIONES_ASIGNACIONES_CHEQUES_CONSULTA](?)}";
                                                    $params_cheque = array(
                                                        array($id_asignacion,     SQLSRV_PARAM_IN),
                                                    );
                                                    $stmt_cheque = sqlsrv_query($conn, $sql_cheque, $params_cheque);
                                                    if ($stmt_cheque === false) {
                                                        die(print_r(sqlsrv_errors(), true));
                                                    }
                                                    $rowcheque = sqlsrv_fetch_array($stmt_cheque, SQLSRV_FETCH_ASSOC);

                                                    $n_cheque = isset($rowcheque['N_CHEQUE']) ? $rowcheque['N_CHEQUE'] : '';
                                                } elseif ($tipo_canal == 2) {

                                                    $sql_remesa = "{call [_SP_CONCILIACIONES_ASIGNACIONES_REMESAS_CONSULTA](?)}";
                                                    $params_remesa = array(
                                                        array($id_asignacion,     SQLSRV_PARAM_IN),
                                                    );
                                                    $stmt_remesa = sqlsrv_query($conn, $sql_remesa, $params_remesa);
                                                    if ($stmt_remesa === false) {
                                                        die(print_r(sqlsrv_errors(), true));
                                                    }
                                                    $rowremesa = sqlsrv_fetch_array($stmt_remesa, SQLSRV_FETCH_ASSOC);

                                                    $n_remesa = isset($rowremesa['N_REMESA']) ? $rowremesa['N_REMESA'] : '';
                                                }
                                        ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check d-flex justify-content-center align-items-center">
                                                            <input class="form-check-input ch_checkbox" name="ch_checkbox[]" type="checkbox" value="<?php echo $asignados["ID_DOCDEUDORES"]; ?>" data-column="1" onclick="toggleRowCheckbox(this)" <?php echo $disabled; ?>>
                                                            <input type="hidden" class="checkbox_type" value="ch">
                                                        </div>
                                                    </td>
                                                    <td class="col-auto font_mini"><?php echo $tipo_canal ?></td>
                                                    <td class="col-auto font_mini"><?php echo $id_asignacion ?></td>
                                                    <td class="interes col-auto font_mini" id="interes">
                                                        <input type="text" class="monto_ingresado font_mini_input form-control"
                                                            value="<?php echo ($tipo_canal == 1) ? $n_cheque : (($tipo_canal == 2) ? $num_remesa : ''); ?>"
                                                            disabled />
                                                    </td>
                                                    <td class="col-auto font_mini"><?php echo mb_substr($canal, 0, 6); ?></td>
                                                    <td class="col-auto font_mini">$<?php echo number_format($monto_tr, 0, ',', '.'); ?></td>
                                                    <td class="col-auto font_mini"><?php echo $benef_cta ?></td>
                                                    <td class="col-auto font_mini"><?php echo $cartera; ?></td>
                                                    <td class="col-auto font_mini"><?php echo $deud_rut ?></td>
                                                    <td class="col-auto font_mini"><?php echo $operacion ?></td>
                                                    <td class="col-auto font_mini"><?php echo $f_venc ?></td>
                                                    <td class="col-auto font_mini"><?php echo substr($producto, 0, 7) ?></td>
                                                    <td class="col-auto font_mini"><?php echo $cant_docs ?></td>
                                                    <td class="col-auto font_mini">$<?php echo number_format($monto_doc, 0, ',', '.'); ?></td>

                                                    <td class="font_mini"><!--
                                                        <a data-toggle="tooltip" title="Eliminar" href="conciliaciones_asignaciones_eliminar.php?id_asig=<?php echo $id_asignacion; ?>&iddoc=<?php echo $iddoc ?>&transaccion=<?php echo $transaccion ?>" class="btn btn-icon btn-rounded btn-danger">
                                                            <i class="feather-24" data-feather="x"></i>
                                                        </a>
                                                        -->
                                                    </td>

                                                </tr> <?php
                                                    }
                                                } ?>
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
                var checkboxes = row.find('input[data-column="' + column + '"]');

                checkboxes.each(function() {
                    if (!$(this).is(':disabled')) { // Solo marca los checkboxes habilitados
                        this.checked = isChecked;
                    }
                });

                // Desmarcar los checkboxes de la columna opuesta
                var otherColumn = column === 1 ? 2 : 1;
                row.find('input[data-column="' + otherColumn + '"]').each(function() {
                    if (!$(this).is(':disabled')) { // Solo desmarca los checkboxes habilitados
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

            // Comprobar si todos los checkboxes habilitados de la columna 1 están marcados
            var allCheckedColumn1 = table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="1"]').not(':disabled').length &&
                table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="1"]').filter(':checked').length ===
                table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="1"]').not(':disabled').length;

            // Comprobar si todos los checkboxes habilitados de la columna 2 están marcados
            var allCheckedColumn2 = table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="2"]').not(':disabled').length &&
                table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="2"]').filter(':checked').length ===
                table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="2"]').not(':disabled').length;

            // Actualizar el estado de los checkboxes maestros
            $('#select_all_checkbox1').prop('checked', allCheckedColumn1);
            $('#select_all_checkbox2').prop('checked', allCheckedColumn2);
        }
    </script>

    <script>
        function habilitarBoton() {
            // Verifica si hay al menos un checkbox con las clases 'ch_checkbox' o 'tr_checkbox' marcado
            const checkboxesCh = document.querySelectorAll('.ch_checkbox:checked');
            const checkboxesTr = document.querySelectorAll('.tr_checkbox:checked');

            // Verifica el estado de los master checkboxes
            const masterCheckbox1 = document.getElementById('select_all_checkbox1').checked;
            const masterCheckbox2 = document.getElementById('select_all_checkbox2').checked;

            const botonGuardar = document.getElementById('guardarButton');

            if (checkboxesCh.length > 0 || checkboxesTr.length > 0 || masterCheckbox1 || masterCheckbox2) {
                botonGuardar.disabled = false;
            } else {
                botonGuardar.disabled = true;
            }
        }

        // Agrega el evento change a todos los checkboxes
        document.querySelectorAll('.ch_checkbox, .tr_checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', habilitarBoton);
        });

        // Agrega el evento change a los master checkboxes
        document.querySelectorAll('#select_all_checkbox1, #select_all_checkbox2').forEach(checkbox => {
            checkbox.addEventListener('change', habilitarBoton);
        });

        // Inicializa el estado del botón al cargar la página
        document.addEventListener('DOMContentLoaded', habilitarBoton);
    </script>

    <script>
        function valida_envia() {
            var selectedIdsDocs = [];
            var selectedIdsPareoDoc = [];
            var selectedTypes = [];

            // Obtener los checkboxes seleccionados, excluyendo los checkboxes maestros
            document.querySelectorAll('input[type=checkbox]:checked:not(#select_all_checkbox1):not(#select_all_checkbox2)').forEach(function(checkbox) {
                var ids = checkbox.value.split(',');
                var idDoc = ids[0];
                var idPareoDoc = ids[1];

                // Obtener el valor de data-column
                var checkboxType = checkbox.getAttribute('data-column');

                // Agregar valores a los arreglos
                selectedIdsDocs.push(idDoc);
                selectedIdsPareoDoc.push(idPareoDoc);
                selectedTypes.push(checkboxType);
            });

            // Asignar los valores a los campos ocultos
            document.getElementById('selected_ids_docs').value = selectedIdsDocs.join(',');
            document.getElementById('selected_ids_pareodoc').value = selectedIdsPareoDoc.join(',');
            document.getElementById('selected_types').value = selectedTypes.join(',');

            return true; // Asegúrate de que el formulario se envíe
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
            "paging": false, // Deshabilita la paginación
            "searching": true, // Habilita la búsqueda
            "ordering": true, // Habilita el ordenamiento
            responsive: true,
            order: [
                [9, 'asc']
            ],
            columnDefs: [{
                    targets: [0, 3, 14],
                    orderable: false
                },
                {
                    targets: [1],
                    visible: false
                },
                {
                    targets: 3,
                    render: function(data, type, row, meta) {
                        // Verificar si el valor de la columna 17 es 1
                        if (row[16] == 1) {
                            // Aplicar estilo rojo al valor de la columna 3
                            return '<span class="text-danger"><b>' + data + '</b></span>';
                        }
                        return data;
                    }
                },
                {
                    targets: 10,
                    render: function(data, type, row, meta) {
                        if (data > 169) {
                            return '<span class="text-danger"><b>' + data + '</b></span>';
                        }
                        return data;
                    }
                }
            ],
            createdRow: function(row, data, dataIndex) {
                var value = data[10];

                if (value > 169) {
                    $(row).css('background-color', 'rgba(255, 0, 0, 0.06)');
                } else if (value < 0) {
                    $(row).css('background-color', 'rgba(255, 255, 0, 0.15)');
                }
            }
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