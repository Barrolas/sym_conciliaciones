<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Carga Conciliaciones</title>
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

        $idusuario = 1;

        if ($_FILES['archivo']['name'] != '') {
            //datos del arhivo
            $arr            = explode(".", $_FILES['archivo']['name']);
            $extension      = $arr[1];
            $nombre_archivo = generateRandomString(20) . '.' . $extension;
            $tipo_archivo   = $_FILES['archivo']['type'];
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
        $excelSheet     = $spreadSheet  ->getSheet($sheetIndex);
        $spreadSheetAry = $excelSheet   ->toArray();
        $sheetCount     = count($spreadSheetAry);

        $numeroMayorDeFila      = $excelSheet->getHighestRow(); // Numérico
        $letraMayorDeColumna    = $excelSheet->getHighestColumn(); // Letra

        // Insertar datos desde el archivo Excel
        $count      = 0;
        $idcarga    = 0;

        //Crear registro en la cheque_carga y recuperar ID con SCOPE
        $sql_carga = "{call [_SP_CONCILIACIONES_CARGA_SISREC_INSERTA](?, ?)}";
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

            $recaudador         = isset($spreadSheetAry[$i][0]) ? $spreadSheetAry[$i][0] : '';
            $producto           = isset($spreadSheetAry[$i][1]) ? $spreadSheetAry[$i][1] : '';
            $monto_remesa       = isset($spreadSheetAry[$i][2]) ? $spreadSheetAry[$i][2] : '';
            $fecha_creacion     = isset($spreadSheetAry[$i][3]) ? $spreadSheetAry[$i][3] : '';
            $fecha_cierre       = isset($spreadSheetAry[$i][4]) ? $spreadSheetAry[$i][4] : '';
            $fecha_proceso      = isset($spreadSheetAry[$i][5]) ? $spreadSheetAry[$i][5] : '';
            $fecha_pago_proceso = isset($spreadSheetAry[$i][6]) ? $spreadSheetAry[$i][6] : '';
            $estado1            = isset($spreadSheetAry[$i][7]) ? $spreadSheetAry[$i][7] : '';
            $id                 = isset($spreadSheetAry[$i][8]) ? $spreadSheetAry[$i][8] : '';
            $n_operacion        = isset($spreadSheetAry[$i][9]) ? $spreadSheetAry[$i][9] : '';
            $n_unico_cheque     = isset($spreadSheetAry[$i][10]) ? $spreadSheetAry[$i][10] : '';
            $medio_pago         = isset($spreadSheetAry[$i][11]) ? $spreadSheetAry[$i][11] : '';
            $monto_pago         = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $rut_titular        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $cod_banco          = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $banco              = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $n_cuenta           = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $n_documento        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $n_serie            = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $fecha_doc          = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $juzgado            = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $rol                = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $remesa_corrige     = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $id_corrige         = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $usuario_corrige    = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $id_corrige         = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $fecha_corrige      = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $observ_corrige     = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $atendido_por       = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $capital            = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $cod_autorizacion   = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $cod_transaccion    = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $cod_subprod        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $descr_subprod      = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $cod_subtipocargo   = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $cod_tipocargo      = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $costas_procesales  = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $estado2            = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $descr_estado       = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $fecha_cupon        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $fecha_id           = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $folio              = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $horario_abono      = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $interes            = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $juicio             = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $motivo             = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $nr_boleta          = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $nr_cheque          = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $rut_cliente        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $tel_girador        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $tipo_juicio        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $tipo_pago          = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $usuario_pndte      = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $fecha_pndte        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $usuario_valid      = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $fecha_valid        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $cod_agencia        = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $agencia            = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $requisidor         = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $fecha_1er_pago     = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';
            $monto_1er_pago     = isset($spreadSheetAry[$i][12]) ? $spreadSheetAry[$i][12] : '';

            // Convierte la fecha al formato Y-m-d compatible con SQL Server
            if (!empty($f_venc)) {
                // Intentamos crear el objeto DateTime con el formato que esperas recibir
                $dateTime = DateTime::createFromFormat('Y-m-d', $f_venc); // Ajusta el formato de entrada, por ejemplo, 'd/m/Y'

                // Verifica si la fecha fue correctamente creada
                if ($dateTime) {
                    // Convierte a Y-m-d, que es el formato compatible con SQL Server
                    $f_venc = $dateTime->format('Y-m-d');
                } else {
                    // Si el formato no es válido, puedes manejarlo de otra manera
                    $f_venc = null; // O puedes asignar un valor por defecto o simplemente manejar el error
                }
            } else {
                // Si no hay fecha, también puedes manejarlo asignando null u otro valor
                $f_venc = null; // O algún valor por defecto
            }

            //print_r($f_venc);
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

            $sql_carga = "{call [_SP_CONCILIACIONES_ASIGNACIONES_CHEQUES_ACTUALIZA](?, ?)}";
            $params_carga = array(
                array($idasignacion,    SQLSRV_PARAM_IN),
                array($idusuario,       SQLSRV_PARAM_IN)
            );
            $stmt_carga = sqlsrv_query($conn, $sql_carga, $params_carga);
            if ($stmt_carga === false) {
                echo "Error in executing statement carga.\n";
                die(print_r(sqlsrv_errors(), true));
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