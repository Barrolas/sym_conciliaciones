<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Carga Cheques</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="CRM" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="plugins/dropify/css/dropify.min.css" rel="stylesheet">
    <!-- Plugins css -->
    <link href="plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.css" rel="stylesheet" type="text/css" />
    <link href="plugins/timepicker/bootstrap-material-datetimepicker.css" rel="stylesheet">
    <link href="plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
    <!-- App css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body>

    <!-- Pantalla de Carga -->
    <div id="loading-screen">
        <div class="spinner"></div>
        <p>Cargando...</p>
    </div>

    <div class="container" id="content">

        <?php
        session_start();
        include("funciones.php");
        include("conexiones.php");
        noCache();

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        //$resultado_r($_FILES);
        //exit;

        $idusuario = $_SESSION['ID_USUARIO'];;

        if ($_FILES['archivo']['name'] != '') {
            //datos del arhivo
            $arr            = explode(".", $_FILES['archivo']['name']);
            $extension        = $arr[1];
            $nombre_archivo = generateRandomString(20) . '.' . $extension;
            $tipo_archivo     = $_FILES['archivo']['type'];
            $tamano_archivo = $_FILES['archivo']['size'];
            //echo $tipo_archivo ."<BR>";
            //compruebo si las características del archivo son las que deseo

            move_uploaded_file($_FILES['archivo']['tmp_name'], 'ChequesRecibidos.xlsx');
        };

        require_once('phpexcel2/vendor/autoload.php');
        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadSheet    = $Reader->load('ChequesRecibidos.xlsx');
        $sheetIndex     = 0;
        $excelSheet     = $spreadSheet->getSheet($sheetIndex);
        $spreadSheetAry = $excelSheet->toArray();
        $sheetCount     = count($spreadSheetAry);

        $numeroMayorDeFila      = $excelSheet->getHighestRow(); // Numérico
        $letraMayorDeColumna    = $excelSheet->getHighestColumn(); // Letra

        // Insertar datos desde el archivo Excel
        $count      = 0; // Contador de registros
        $errCount   = 0;
        $idcarga    = 0;
        $tipo_canal = 1;

        //Crear registro en la cheque_carga y recuperar ID con SCOPE
        $sql_carga = "{call [_SP_CONCILIACIONES_CARGA_CHEQUES_INSERTA](?, ?)}";
        $params_carga = array(
            array($idusuario,   SQLSRV_PARAM_IN),
            array(&$idcarga,    SQLSRV_PARAM_INOUT)
        );
        $stmt_carga = sqlsrv_query($conn, $sql_carga, $params_carga);
        if ($stmt_carga === false) {
            echo "Error in executing statement carga.\n";
            die(print_r(sqlsrv_errors(), true));
        }

        for ($i = 1; $i < $sheetCount; $i++) {

            $idasignacion    = isset($spreadSheetAry[$i][0]) ? $spreadSheetAry[$i][0] : '';
            $titular_rut     = isset($spreadSheetAry[$i][1]) ? $spreadSheetAry[$i][1] : '';
            $titular_dv      = isset($spreadSheetAry[$i][2]) ? $spreadSheetAry[$i][2] : '';
            $titular_nom     = isset($spreadSheetAry[$i][3]) ? $spreadSheetAry[$i][3] : '';
            $cuenta_benef    = isset($spreadSheetAry[$i][4]) ? $spreadSheetAry[$i][4] : '';
            $cliente_rut     = isset($spreadSheetAry[$i][5]) ? $spreadSheetAry[$i][5] : '';
            $operacion       = isset($spreadSheetAry[$i][6]) ? $spreadSheetAry[$i][6] : '';
            $monto_doc       = isset($spreadSheetAry[$i][7]) ? $spreadSheetAry[$i][7] : '';
            $f_venc          = isset($spreadSheetAry[$i][8]) ? $spreadSheetAry[$i][8] : '';
            $subprod         = isset($spreadSheetAry[$i][9]) ? $spreadSheetAry[$i][9] : '';
            $cartera         = isset($spreadSheetAry[$i][10]) ? $spreadSheetAry[$i][10] : '';
            $pagodocs        = isset($spreadSheetAry[$i][11]) ? $spreadSheetAry[$i][11] : '';
            $n_cheque        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';

            if (!empty($f_venc)) {
                $dateTime = DateTime::createFromFormat('Y-m-d', $f_venc);
                if ($dateTime) {
                    $f_venc = $dateTime->format('Y-m-d');
                } else {
                    $f_venc = null;
                }
            } else {
                $f_venc = null;
            }
            $cheque_valida = 0;
            $n_cheque = trim($n_cheque);
            //print_r($n_cheque);
            //exit;

            $sql_detalles = "{call [_SP_CONCILIACIONES_CANALIZACION_CARGA_CHEQUES_DETALLES_INSERTA](?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
            $params_detalles = array(
                array($idcarga,         SQLSRV_PARAM_IN),
                array($idasignacion,    SQLSRV_PARAM_IN),
                array($titular_rut,     SQLSRV_PARAM_IN),
                array($titular_dv,      SQLSRV_PARAM_IN),
                array($titular_nom,     SQLSRV_PARAM_IN),
                array($cuenta_benef,    SQLSRV_PARAM_IN),
                array($cliente_rut,     SQLSRV_PARAM_IN),
                array($operacion,       SQLSRV_PARAM_IN),
                array($monto_doc,       SQLSRV_PARAM_IN),
                array($f_venc,          SQLSRV_PARAM_IN),
                array($subprod,         SQLSRV_PARAM_IN),
                array($cartera,         SQLSRV_PARAM_IN),
                array($pagodocs,        SQLSRV_PARAM_IN),
                array($n_cheque,        SQLSRV_PARAM_IN),
            );
            $stmt_detalles = sqlsrv_query($conn, $sql_detalles, $params_detalles);
            if ($stmt_detalles === false) {
                echo "Error in executing statement detalles.\n";
                die(print_r(sqlsrv_errors(), true));
            }



            $sql_carga = "{call [_SP_CONCILIACIONES_ASIGNACIONES_CHEQUES_ACTUALIZA](?, ?, ?, ?)}";
            $params_carga = array(
                array($idasignacion,    SQLSRV_PARAM_IN),
                array($n_cheque,        SQLSRV_PARAM_IN),
                array($idusuario,       SQLSRV_PARAM_IN),
                array(&$cheque_valida,  SQLSRV_PARAM_INOUT)
            );
            $stmt_carga = sqlsrv_query($conn, $sql_carga, $params_carga);
            if ($stmt_carga === false) {
                echo "Error in executing statement carga.\n";
                die(print_r(sqlsrv_errors(), true));
            }

            if ($cheque_valida <> 0) {

                $estado1 = 2;
                $estado2 = 3;
                $sql_asign    = "EXEC [_SP_CONCILIACIONES_ASIGNADOS_CONSULTA] ?, ?, ?";
                $params_asign = array(
                    array($idasignacion,    SQLSRV_PARAM_IN),
                    array($estado1,         SQLSRV_PARAM_IN),
                    array($estado2,         SQLSRV_PARAM_IN),
                );
                $stmt_asign = sqlsrv_query($conn, $sql_asign, $params_asign);
                if ($stmt_asign === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                $asignacion = sqlsrv_fetch_array($stmt_asign, SQLSRV_FETCH_ASSOC);

                if (!empty($asignacion)) {

                    $iddoc          = $asignacion['ID_DOCDEUDORES'];
                    $transaccion    = $asignacion['TRANSACCION'];

                    //print_r($iddoc . '; ');
                    //print_r($transaccion . '; ');


                    $sql_asociados = "{call [_SP_CONCILIACIONES_OPERACIONES_ASOCIADAS_IDENTIFICAR](?, ?, ?, ?)}";
                    $params_asociados = array(
                        array($iddoc,           SQLSRV_PARAM_IN),
                        array($transaccion,     SQLSRV_PARAM_IN),
                        array(1,                SQLSRV_PARAM_IN), // ID_ESTADO
                        array(3,                SQLSRV_PARAM_IN)  // ID_ETAPA       
                    );
                    $stmt_asociados = sqlsrv_query($conn, $sql_asociados, $params_asociados);
                    if ($stmt_asociados === false) {
                        echo "Error in executing statement asociados.\n";
                        die(print_r(sqlsrv_errors(), true));
                    }
                    while ($asociados = sqlsrv_fetch_array($stmt_asociados, SQLSRV_FETCH_ASSOC)) {

                        $iddoc_asoc = $asociados['ID_DOCDEUDORES'];

                        $sql_operacion = "{call [_SP_CONCILIACIONES_OPERACION_ASIGNACION_INSERTA] (?, ?)}";
                        $params_operacion = array(
                            array($iddoc_asoc,  SQLSRV_PARAM_IN),
                            array($idusuario,   SQLSRV_PARAM_IN)
                        );
                        $stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
                        if ($stmt_operacion === false) {
                            echo "Error en la ejecución de la declaración _operacion.\n";
                            die(print_r(sqlsrv_errors(), true));
                        }
                    }

                    $sql_ch    = "EXEC [_SP_CONCILIACIONES_CARTOLA_CHEQUES_CONSULTA] ?";
                    $params_ch = array(
                        array($n_cheque,     SQLSRV_PARAM_IN),
                    );
                    $stmt_ch = sqlsrv_query($conn, $sql_ch, $params_ch);
                    if ($stmt_ch === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                    $cheque = sqlsrv_fetch_array($stmt_ch, SQLSRV_FETCH_ASSOC);

                    $n_documento    = isset($cheque['N_DOCUMENTO']) ? $cheque['N_DOCUMENTO']    : null;
                    $cuenta         = isset($cheque['CUENTA'])      ? $cheque['CUENTA']         : null;
                    $fecha_cartola  = isset($cheque['FECHA'])       ? $cheque['FECHA']          : null;

                    $fecha_cartola = isset($cheque['FECHA'])
                        ? DateTime::createFromFormat('d/m/Y', $cheque['FECHA'])->format('Y-m-d')
                        : null;

                    /*if (!empty($fecha_cartola)) {
                    $dateTime1 = DateTime::createFromFormat('Y-m-d', $fecha_cartola);
                    if ($dateTime1) {
                        $fecha_cartola = $dateTime1->format('Y-m-d');
                    } else {
                        $fecha_cartola = null;
                    }
                } else {
                    $fecha_cartola = null;
                }

                if (!empty($fecha)) {
                    $dateTime2 = DateTime::createFromFormat('Y-m-d', $fecha);
                    if ($dateTime2) {
                        $fecha = $dateTime2->format('Y-m-d');
                    } else {
                        $fecha = null;
                    }
                } else {
                    $fecha = null;
                }*/

                    $monto       = isset($cheque['MONTO']) ? $cheque['MONTO'] : null;

                    /*print_r('n_documento: ' . $n_documento);
                print_r('cuenta: ' . $cuenta);
                print_r('fecha: ' . $fecha);
                print_r('monto: ' . $monto);*/

                    if ($monto !== null) {
                        $monto = preg_replace('/[^\d]/', '', $monto);
                    }
                    /*
                print_r('ncheque: ' . $n_cheque);
                print_r('ndoc: ' . $n_documento);
                exit;
*/

                    $id_conciliacion        = 0;
                    $descripcion            = 'CHEQUE COBRADO POR OTRO BANCO';
                    $descripcion_respaldo   = $titular_rut . '-' . $titular_dv . ' ' . $titular_nom;

                    if ($n_cheque == $n_documento) {

                        $query = "{CALL [_SP_CONCILIACIONES_CONCILIACION_OBTENER_ID](?)}";
                        $params = array(array(&$id_conciliacion, SQLSRV_PARAM_OUT));
                        $stmt = sqlsrv_query($conn, $query, $params);
                        if ($stmt === false) {
                            die("Error al obtener ID_CONCILIACION: " . print_r(sqlsrv_errors(), true));
                        }
                        sqlsrv_free_stmt($stmt);

                        $sql_cartola = "{CALL [_SP_CONCILIACIONES_CONCILIACION_CARTOLA_INSERTAR](?, ?, ?, ?, ?, ?, ?)}";
                        $params_cartola = array(
                            array($id_conciliacion,         SQLSRV_PARAM_IN),
                            array($cuenta,                  SQLSRV_PARAM_IN),
                            array($fecha_cartola,           SQLSRV_PARAM_IN),
                            array($n_cheque,                SQLSRV_PARAM_IN),
                            array($descripcion,             SQLSRV_PARAM_IN),
                            array($monto,                   SQLSRV_PARAM_IN),
                            array($idusuario,               SQLSRV_PARAM_IN)
                        );
                        $stmt_cartola = sqlsrv_query($conn, $sql_cartola, $params_cartola);
                        if ($stmt_cartola === false) {
                            echo "Error in executing statement cartola.\n";
                            die(print_r(sqlsrv_errors(), true));
                        }

                        $sql_respaldos = "{CALL [_SP_CONCILIACIONES_CONCILIACION_RESPALDO_INSERTAR](?, ?, ?, ?, ?, ?, ?, ?)}";
                        $params_respaldos = array(
                            array($id_conciliacion,         SQLSRV_PARAM_IN),
                            array($cuenta,                  SQLSRV_PARAM_IN),
                            array($fecha_cartola,           SQLSRV_PARAM_IN),
                            array($n_cheque,                SQLSRV_PARAM_IN),
                            array($descripcion_respaldo,    SQLSRV_PARAM_IN),
                            array($monto,                   SQLSRV_PARAM_IN),
                            array($tipo_canal,              SQLSRV_PARAM_IN),
                            array($idusuario,               SQLSRV_PARAM_IN)
                        );
                        $stmt_respaldos = sqlsrv_query($conn, $sql_respaldos, $params_respaldos);
                        if ($stmt_respaldos === false) {
                            echo "Error in executing statement respaldos.\n";
                            die(print_r(sqlsrv_errors(), true));
                        }

                        $sql_conciliacion = "{call [_SP_CONCILIACIONES_CONCILIACION_INSERTAR](?, ?, ?, ?, ?, ?, ?)}";
                        $params_conciliacion = array(
                            array($n_documento,             SQLSRV_PARAM_IN),
                            array($cuenta,                  SQLSRV_PARAM_IN),
                            array($fecha_cartola,           SQLSRV_PARAM_IN),
                            array($n_cheque,                SQLSRV_PARAM_IN),
                            array($tipo_canal,              SQLSRV_PARAM_IN),
                            array($monto,                   SQLSRV_PARAM_IN),
                            array($idusuario,               SQLSRV_PARAM_IN)
                        );
                        $stmt_conciliacion = sqlsrv_query($conn, $sql_conciliacion, $params_conciliacion);
                        if ($stmt_conciliacion === false) {
                            echo "Error in executing statement conciliacion.\n";
                            die(print_r(sqlsrv_errors(), true));
                        }
                    }
                }
            } else {
                $errCount++;
            }
            $count++;
        }
        /*
        print_r($idcarga . ';');
        print_r($count . ';');
        print_r($idusuario . ';');
        exit;
        */

        $sql_actualiza = "{call [_SP_CONCILIACIONES_CARGA_CHEQUES_ACTUALIZA](?, ?, ?)}";
        $params_actualiza = array(
            array($idcarga,     SQLSRV_PARAM_IN),
            array($count,       SQLSRV_PARAM_IN),
            array($idusuario,   SQLSRV_PARAM_IN)
        );
        $stmt_actualiza = sqlsrv_query($conn, $sql_actualiza, $params_actualiza);
        if ($stmt_actualiza === false) {
            echo "Error in executing statement actualiza.\n";
            die(print_r(sqlsrv_errors(), true));
        }

        $nombre_archivo = 'ChequesRecibidos_' . $hoy_formateado . '.xlsx';
        move_uploaded_file('ChequesRecibidos.xlsx', '\archivos\\' . $nombre_archivo);
        header("Location: cargas_cheques.php?op=1");
        ?>

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

</html>