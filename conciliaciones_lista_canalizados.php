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


$op = 9;
if (isset($_GET["op"])) {
    $op = $_GET["op"];
};

$sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
        from dbo.Transferencias_Recibidas_Hist";

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
                                        <li class="breadcrumb-item active">Procesar Canalizados</li>
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
                            <b>Procesar Canalizados</b>
                        </h3>
                    </div>
                    <div class="row mr-2">
                        <div class="col-12 mx-2">
                            <p>
                                En este módulo se visualizan las canalizaciones realizadas en el módulo anterior,
                                que están listas para ser procesadas y a las cuales se les asignará un ID único.
                                Este ID será utilizado para el ingreso del número de cheque en los documentos
                                canalizados por cheque, y del número de remesa en los canalizados por transferencia.<br>
                                Para los casos de canalización por cheque, se debe exportar un documento Excel con
                                todos los cheques procesados, completar el campo de <b>N° CHEQUE</b> y luego cargar el
                                archivo en el siguiente módulo. Para los canalizados por transferencia, solo se
                                requiere cargar el documento de SISREC, el cual proporcionará el <b>N° REMESA</b> para
                                aquellos que hayan hecho match.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3">
                <form id="form_concilia" method="post" class="mr-0" action="conciliaciones_canalizados_procesar.php" onsubmit="return valida_envia();return false;">

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
                                            $sql_canal = "EXEC [_SP_CONCILIACIONES_TIPOS_CANALIZACIONES_LISTA] '1,2'";
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
                            </div><!--end form-group-->
                        </div><!--end col-->
                    </div>


                    <div class="card border-0">
                        <div class="card-header border-0">
                            <!-- Pestañas -->
                            <ul class="nav nav-pills nav-justified mb-3 mx-3 border-0 bg-light" id="pestañas" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="canalizados-tab" data-toggle="tab" href="#canalizados" role="tab" aria-controls="canalizados" aria-selected="true">CANALIZADOS</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="historial-tab" data-toggle="tab" href="#historial" role="tab" aria-controls="historial" aria-selected="false">HISTORIAL</a>
                                </li>
                            </ul>
                        </div>
                        <!-- Tab panes -->
                        <div class="container-fluid tab-content">
                            <div class="tab-pane fade show active" id="canalizados" role="tabpanel">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class='col-7'>
                                            <p class="mb-0">
                                                <b>CANALIZACIONES A PROCESAR</b>
                                                <b><span id="row-count" class="ms-2 text-primary">(0)</span></b>
                                            </p>
                                        </div> <!--
                                    <div class='col-2'>
                                        <a href="conciliaciones_exportar_canalizados.php">
                                            <button type="button" class="btn btn-secondary waves-effect waves-light d-flex align-items-center" id="exportar">
                                                <i class="feather feather-16 pr-1" data-feather="download"></i> <span class="ms-2">EXPORTAR</span>
                                            </button>
                                        </a>
                                    </div> -->
                                        <div class='col-3'>
                                            <button type="submit" class="btn btn-primary waves-effect waves-light mt-4" id="procesarBtn" disabled>
                                                <i class="feather feather-16 pr-1" data-feather="plus"></i> <span class="ms-2">PROCESAR</span>
                                            </button>
                                        </div>
                                    </div><!--end card-header-->
                                    <div class="card-body">
                                        <table id="datatable2" class="table dt-responsive nowrap table-hover" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th class="font_mini_header">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <input class="mb-2" type="checkbox" id="select_all_checkbox1" onclick="handleMasterCheckbox(1)">
                                                        </div>
                                                    </th>
                                                    <th>CANAL</th>
                                                    <th>TRANSACCION</th>
                                                    <th>MONTO</th>
                                                    <th>CUENTA</th>
                                                    <th>RUT CTE</th>
                                                    <th>RUT DEU</th>
                                                    <th>F. VENC</th>
                                                    <th>OPERACIÓN</th>
                                                    <th>SUBPROD</th>
                                                    <th>TIPO</th>
                                                    <th>V.CUOTA</th>
                                                    <th>ELIMINAR</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sql = "EXEC [_SP_CONCILIACIONES_CANALIZADOS_OPERACIONES_LISTA]";
                                                $stmt = sqlsrv_query($conn, $sql);
                                                if ($stmt === false) {
                                                    die(print_r(sqlsrv_errors(), true));
                                                }
                                                while ($p_sistema = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {


                                                    $n_doc              = $p_sistema['N_DOC'];
                                                    $monto_doc          = $p_sistema['MONTO_DOC'];

                                                    //print_r('n_doc: ' . $n_doc . '; ' . 'monto_doc: ' . $monto_doc . '; ');
                                                    //exit;

                                                    $sql_pd = "{call [_SP_CONCILIACIONES_CANALIZADOS_OPERACIONES_CONSULTA](?, ?)}";
                                                    $params_pd = array(
                                                        array($n_doc,     SQLSRV_PARAM_IN),
                                                        array($monto_doc, SQLSRV_PARAM_IN),
                                                    );
                                                    $stmt_pd = sqlsrv_query($conn, $sql_pd, $params_pd);
                                                    if ($stmt_pd === false) {
                                                        die(print_r(sqlsrv_errors(), true));
                                                    }
                                                    $p_docs = sqlsrv_fetch_array($stmt_pd, SQLSRV_FETCH_ASSOC);

                                                    $idpareo_sis        = $p_docs['ID_PAREO_SISTEMA'];
                                                    $id_doc             = $p_docs['ID_DOCDEUDORES'];
                                                    $entrecuenta        = $p_docs['ENTRE_CUENTAS'];

                                                    //print_r($idpareo_sis);
                                                    //exit;

                                                    $sql_contable = "{call [_SP_CONCILIACIONES_CANALIZADOS_CONTABLE](?)}";
                                                    $params_contable = array(
                                                        array($id_doc,    SQLSRV_PARAM_IN),
                                                    );
                                                    $stmt_contable = sqlsrv_query($conn, $sql_contable, $params_contable);
                                                    if ($stmt_contable === false) {
                                                        die(print_r(sqlsrv_errors(), true));
                                                    }
                                                    $contable = sqlsrv_fetch_array($stmt_contable, SQLSRV_FETCH_ASSOC);

                                                    $sql_contable = "{call [_SP_CONCILIACIONES_OPERACION_CANALIZACION_CONSULTA](?)}";
                                                    $params_contable = array(
                                                        array($id_doc,    SQLSRV_PARAM_IN),
                                                    );
                                                    $stmt_contable = sqlsrv_query($conn, $sql_contable, $params_contable);
                                                    if ($stmt_contable === false) {
                                                        die(print_r(sqlsrv_errors(), true));
                                                    }
                                                    $canalizacion = sqlsrv_fetch_array($stmt_contable, SQLSRV_FETCH_ASSOC);

                                                    $disabled           =  '';

                                                    if ($entrecuenta == 2) {
                                                        $benef_cta      = $p_docs['CUENTA_CORRESPONDIENTE'];
                                                        $transaccion    = $p_docs['ENTRE_CUENTAS_TRANSACCION'];
                                                    } else {
                                                        $benef_cta      = $p_docs['CUENTA_BENEFICIARIO'];
                                                        $transaccion    = $p_docs['TRANSACCION'];
                                                    }
                                                    if ($p_docs['MONTO_TRANSACCION_INGRESADO'] <> 0) {
                                                        $monto_tr       = $p_docs['MONTO_TRANSACCION_INGRESADO'];
                                                    } else {
                                                        $monto_tr       = $p_docs['MONTO_TRANSACCION_ORIGINAL'];
                                                    }



                                                    $sql_ps = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_TRANSACCION_CONSULTA](?)}";
                                                    $params_ps = array(
                                                        array($transaccion,    SQLSRV_PARAM_IN),
                                                    );
                                                    $stmt_ps = sqlsrv_query($conn, $sql_ps, $params_ps);
                                                    if ($stmt_ps === false) {
                                                        die(print_r(sqlsrv_errors(), true));
                                                    }
                                                    $ps = sqlsrv_fetch_array($stmt_ps, SQLSRV_FETCH_ASSOC);

                                                    $idpareo_sis = $ps['ID_PAREO_SISTEMA'];

                                                    $sql_pagodocs = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_CANALIZADOS_METODOS_PAGO](?)}";
                                                    $params_pagodocs = array(
                                                        array($idpareo_sis,    SQLSRV_PARAM_IN),
                                                    );
                                                    $stmt_pagodocs = sqlsrv_query($conn, $sql_pagodocs, $params_pagodocs);
                                                    if ($stmt_pagodocs === false) {
                                                        die(print_r(sqlsrv_errors(), true));
                                                    }
                                                    $pagodocs = sqlsrv_fetch_array($stmt_pagodocs, SQLSRV_FETCH_ASSOC);


                                                    //Variables pareo sistema
                                                    $operacion      = $p_sistema['N_DOC'];
                                                    $monto_doc      = $p_sistema['MONTO_DOC'];
                                                    //Variables pareo docs
                                                    $f_recepcion    = $p_docs['FECHA_RECEPCION'];
                                                    $deud_rut       = $p_docs['RUT_DEUDOR'];
                                                    $deud_dv        = $p_docs['DEUDOR_DV'];
                                                    $cte_rut        = $p_docs['RUT_CLIENTE'];
                                                    $deud_nom       = $p_docs['ORDENANTE_NOMBRE'];
                                                    $deud_nom       = strtoupper($p_docs['ORDENANTE_NOMBRE']);
                                                    $deud_nom       = preg_replace('/[^A-Z\s]/', '', $deud_nom);
                                                    $deud_nom       = preg_replace('/\s+/', ' ', $deud_nom);
                                                    $deud_nom       = trim($deud_nom);
                                                    $producto       = $p_docs['SUBPRODUCTO'];
                                                    $cartera        = $p_docs['CARTERA'];
                                                    $f_venc         = $p_docs['F_VENC'] instanceof DateTime ? $p_docs["F_VENC"]->format('Y-m-d') : $p_docs["F_VENC"];
                                                    $pago_docs      = $pagodocs['DESCRIPCION_PAGOS'] ?? '';
                                                    $monto_cubierto = $contable['MONTO_CUBIERTO'];
                                                    $tipo_canal     = $canalizacion['ID_TIPO_CANALIZACION'];
                                                    $canal          = $canalizacion['CANAL'];
                                                ?>

                                                    <tr>
                                                        <td>
                                                            <div class="form-check d-flex justify-content-center align-items-center">
                                                                <input class="form-check-input ch_checkbox"
                                                                    name="ch_checkbox[]"
                                                                    type="checkbox"
                                                                    value="<?php echo $idpareo_sis . ',' . $id_doc . ',' . $operacion . ',' . $transaccion . ',' . $deud_nom . ',' . $deud_rut . ',' . $deud_dv . ',' . $pago_docs . ',' . $tipo_canal . ',' . $benef_cta . ',' . $monto_doc . ',' . $f_venc; ?>"
                                                                    data-column="1"
                                                                    onclick="toggleRowCheckbox(this)"
                                                                    <?php echo $disabled; ?>>
                                                                <input type="hidden" class="checkbox_type" value="ch">
                                                            </div>
                                                        </td>
                                                        <td class="col-auto"><?php echo mb_substr($canal, 0, 6); ?></td>
                                                        <td class="col-auto"><?php echo $transaccion; ?></td>
                                                        <td class="col-auto">$<?php echo number_format($monto_tr, 0, ',', '.'); ?></td>
                                                        <td class="col-auto"><?php echo $benef_cta; ?></td>
                                                        <td class="col-auto"><?php echo $cte_rut; ?></td>
                                                        <td class="col-auto"><?php echo $deud_rut; ?></td>
                                                        <td class="col-auto"><?php echo $f_venc; ?></td>
                                                        <td class="col-auto"><?php echo $operacion; ?></td>
                                                        <td class="col-auto"><?php echo mb_substr($producto, 0, 7); ?></td>
                                                        <td class="col-auto"><?php echo $pago_docs; ?></td>
                                                        <td class="col-auto">$<?php echo number_format($monto_doc, 0, ',', '.'); ?></td>
                                                        <td class="col-1">
                                                            <a data-toggle="tooltip" title="Eliminar" href="conciliaciones_canalizaciones_eliminar.php?r_cl=<?php echo $cte_rut; ?>&r_dd=<?php echo $deud_rut; ?>&f_venc=<?php echo urlencode($f_venc); ?>&ndoc=<?php echo urlencode($operacion); ?>&transaccion=<?php echo $transaccion; ?>&id_doc=<?php echo $id_doc; ?>" class="btn btn-icon btn-rounded btn-danger">
                                                                <i class="feather-24" data-feather="x"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php   }; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!--final del tab -->
                        <div class="tab-pane fade" id="historial" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <table id="datatable3" class="table dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>FECHA</th>
                                                <th>USUARIO</th>
                                                <th>PROCESADOS</th>
                                                <th>DETALLE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "EXEC [_SP_CONCILIACIONES_PROCESOS_LISTA]";
                                            $stmt = sqlsrv_query($conn, $sql);
                                            if ($stmt === false) {
                                                die(print_r(sqlsrv_errors(), true));
                                            }
                                            while ($conciliacion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>

                                                <tr>
                                                    <td class="col-auto"><?php echo $conciliacion["ID_CANALIZACION_PROCESO"]; ?></td>
                                                    <td class="col-auto">
                                                        <?php
                                                        // Asegúrate de que $conciliacion["FUA"] sea un objeto DateTime
                                                        if ($conciliacion["FUA"] instanceof DateTime) {
                                                            echo $conciliacion["FUA"]->format('Y-m-d H:i:s');
                                                        } else {
                                                            echo 'Fecha no válida';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="col-auto"><?php echo $conciliacion["UA"]; ?></td>
                                                    <td class="col-auto"><?php echo $conciliacion["TOTAL_PROCESADOS"]; ?></td>
                                                    <td class="col-1">
                                                        <!-- <a data-toggle="tooltip" title="Ver detalle" href="conciliaciones_lista_procesos_detalles.php?id=<?php echo $conciliacion["ID_CANALIZACION_PROCESO"]; ?>" class="btn btn-icon btn-rounded btn-secondary"> -->
                                                        <a data-toggle="tooltip" title="Ver detalle" href="#" class="btn btn-icon btn-rounded btn-secondary">
                                                            <i class="feather-24" data-feather="eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php   }; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> <!-- final del tab -->
                    </div> <!--final de los tabs -->
                    <input type="hidden" id="selected_ids_docs" name="selected_ids_docs[]">
                    <input type="hidden" id="selected_ids_pareosis" name="selected_ids_pareosis[]">
                    <input type="hidden" id="selected_canalizaciones" name="selected_canalizaciones[]">
                </form>
            </div>

            <div class="col-12 px-3">
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
    function habilitarBoton() {
        // Verifica si hay al menos un checkbox con las clases 'ch_checkbox' o 'tr_checkbox' marcado
        const checkboxesCh = document.querySelectorAll('.ch_checkbox:checked');
        const checkboxesTr = document.querySelectorAll('.tr_checkbox:checked');

        // Verifica el estado de los master checkboxes
        const masterCheckbox1 = document.getElementById('select_all_checkbox1').checked;
        const masterCheckbox2 = document.getElementById('select_all_checkbox2').checked;
        const botonGuardar = document.getElementById('procesarBtn');

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
        var selectedIdsPareoSis = [];
        var selectedCanalizacion = [];

        // Obtener los checkboxes seleccionados, excluyendo los checkboxes maestros
        document.querySelectorAll('input[type=checkbox]:checked:not(#select_all_checkbox1):not(#select_all_checkbox2)').forEach(function(checkbox) {
            var ids = checkbox.value.split(',');
            var idDoc = ids[0];
            var idPareoSis = ids[1];
            var canalizacionTipo = ids[2];

            // Agregar valores a los arreglos
            selectedIdsDocs.push(idDoc);
            selectedIdsPareoSis.push(idPareoSis);
            selectedCanalizacion.push(canalizacionTipo);
        });

        // Asignar los valores a los campos ocultos
        document.getElementById('selected_ids_docs').value = selectedIdsDocs.join(',');
        document.getElementById('selected_ids_pareosis').value = selectedIdsPareoSis.join(',');
        document.getElementById('selected_canalizaciones').value = selectedCanalizacion.join(',');

        return true;
    }
</script>

<script>
    $(document).ready(function() {
        // Guardar la pestaña activa en localStorage
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var tabId = $(e.target).attr('href');
            localStorage.setItem('activeTab', tabId);
        });

        // Restaurar la pestaña activa al cargar la página
        var activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            $('#pestañas a[href="' + activeTab + '"]').tab('show');
        }

        // Restablecer a los valores por defecto al hacer clic en el botón de reinicio
        $('#volverbtn').on('click', function() {
            localStorage.removeItem('activeTab'); // Elimina la clave activeTab de localStorage

            // Mostrar la primera pestaña por defecto
            $('#pestañas a:first').tab('show');
        });

        // Verificar el valor de op y restablecer pestañas si es necesario
        <?php if ($op == 5 || $op == 9) : ?>
            localStorage.removeItem('activeTab');
            $('#pestañas a:first').tab('show');
        <?php endif; ?>
    });

    $(document).ready(function() {
        // Al hacer clic en una pestaña
        $('.nav-link').click(function() {
            var target = $(this).attr('href'); // Obtiene el ID del contenido
            $('.tab-pane').hide(); // Oculta todos los paneles
            $(target).show(); // Muestra el panel correspondiente
        });

        // Mostrar solo la primera pestaña al cargar la página
        $('.tab-pane').hide();
        $('.tab-pane:first').show();
    });
</script>

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


        var table = $('#datatable2').DataTable({
            "paging": false, // Deshabilita la paginación
            "searching": true, // Habilita la búsqueda
            "ordering": true, // Habilita el ordenamiento

            order: [
                [1, 'asc'],
                [4, 'asc'],
                [3, 'asc']
            ],
            columnDefs: [{
                targets: [0, 12],
                orderable: false
            }]
        });

        var table2 = $('#datatable3').DataTable({
            order: [
                [0, 'desc'],
            ],
            columnDefs: [{
                targets: 4,
                orderable: false
            }]
        });

        // Función para actualizar el conteo de filas y el estado del botón
        function updateRowCountAndButton() {
            var rowCount = table.rows().count();
            var exportButton = document.getElementById('procesarBtn');
            var rowCountElement = document.getElementById('row-count');

            // Actualiza el conteo de filas en el texto
            if (rowCountElement) {
                rowCountElement.textContent = `(${rowCount})`;
            }

            // Habilita o deshabilita el botón de exportación según el conteo de filas
            if (rowCount > 0) {
                exportButton.disabled = false;
            } else {
                exportButton.disabled = true;
            }
        }

        // Llama a la función para actualizar el conteo inicial
        updateRowCountAndButton();

        table.on('draw', function() {
            updateRowCountAndButton();
        });

        // Function to apply filters based on stored values
        function applyFilters() {
            var storedCuentaValue = sessionStorage.getItem('selected_cuenta_3');
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
            sessionStorage.setItem('selected_cuenta_3', filterValue);

            if (filterValue == "0") {
                table.column(4).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(4).search(filterValue).draw();
            }
        });

        $('#canal_filtro').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_canal', filterValue);

            if (filterValue == "0") {
                table.column(1).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(1).search(filterValue).draw();
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
            title: 'Proceso realizado con éxito.',
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