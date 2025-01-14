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

// Verificar archivo cargado
if ($_FILES['archivo']['name'] != '') {
    $arr = explode(".", $_FILES['archivo']['name']);
    $extension = $arr[1] ?? '';
    $nombre_archivo = generateRandomString(20) . '.' . $extension;

    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], 'ChequesRecibidos.xlsx')) {
        mostrarError("No se pudo cargar el archivo. Verifique los permisos de escritura en el servidor.");
    }
} else {
    mostrarError("No se seleccionó ningún archivo para cargar.");
}

// Cargar y procesar el archivo Excel
require_once('phpexcel2/vendor/autoload.php');

try {
    $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadSheet = $Reader->load('ChequesRecibidos.xlsx');
} catch (Exception $e) {
    mostrarError("Error al cargar el archivo Excel: " . $e->getMessage());
}

if (!$spreadSheet->sheetNameExists('Cheques')) {
    mostrarError("La hoja 'Cheques' no se encuentra en el archivo cargado.");
}

$excelSheet = $spreadSheet->getSheetByName('Cheques');
$spreadSheetAry = $excelSheet->toArray();
$sheetCount = count($spreadSheetAry);

// Insertar registro de carga
$stmt_carga = "{call [_SP_CONCILIACIONES_CARGA_CHEQUES_INSERTA](?, ?)}";
$params_carga = [
    [$idusuario, SQLSRV_PARAM_IN],
    [&$idcarga, SQLSRV_PARAM_INOUT]
];
$carga_result = sqlsrv_query($conn, $stmt_carga, $params_carga);
if ($carga_result === false) {
    mostrarError("Error en stmt_carga | No se pudo insertar el registro de carga.");
}

// Procesar cada fila del Excel
for ($i = 1; $i < $sheetCount; $i++) {
    $idasignacion   = $spreadSheetAry[$i][0] ?? null;
    $titular_rut    = $spreadSheetAry[$i][1] ?? null;
    $titular_dv     = $spreadSheetAry[$i][2] ?? null;
    $titular_nom    = $spreadSheetAry[$i][3] ?? null;
    $cuenta_benef   = $spreadSheetAry[$i][4] ?? null;
    $cliente_rut    = $spreadSheetAry[$i][5] ?? null;
    $operacion      = $spreadSheetAry[$i][6] ?? null;
    $monto_doc      = $spreadSheetAry[$i][7] ?? null;
    $f_venc         = $spreadSheetAry[$i][8] ?? null;
    $subprod        = $spreadSheetAry[$i][9] ?? null;
    $cartera        = $spreadSheetAry[$i][10] ?? null;
    $pagodocs       = $spreadSheetAry[$i][11] ?? null;
    $n_cheque       = $spreadSheetAry[$i][12] ?? null;

    // Formatear fecha
    if (!empty($f_venc)) {
        $dateTime = DateTime::createFromFormat('Y-m-d', $f_venc);
        $f_venc = $dateTime ? $dateTime->format('Y-m-d') : null;
    }

    // Insertar detalles del cheque
    $stmt_detalles = "{call [_SP_CONCILIACIONES_CANALIZACION_CARGA_CHEQUES_DETALLES_INSERTA](?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
    $params_detalles = [
        [intval($idcarga),      SQLSRV_PARAM_IN],
        [intval($idasignacion), SQLSRV_PARAM_IN],
        [intval($titular_rut),  SQLSRV_PARAM_IN],
        [$titular_dv,           SQLSRV_PARAM_IN],
        [$titular_nom,          SQLSRV_PARAM_IN],
        [$cuenta_benef,         SQLSRV_PARAM_IN],
        [$cliente_rut,          SQLSRV_PARAM_IN],
        [$operacion,            SQLSRV_PARAM_IN],
        [intval($monto_doc),    SQLSRV_PARAM_IN],
        [$f_venc,               SQLSRV_PARAM_IN],
        [$subprod,              SQLSRV_PARAM_IN],
        [$cartera,              SQLSRV_PARAM_IN],
        [$pagodocs,             SQLSRV_PARAM_IN],
        [$n_cheque,             SQLSRV_PARAM_IN],
    ];
    $detalles_result = sqlsrv_query($conn, $stmt_detalles, $params_detalles);
    if ($detalles_result === false) {
        mostrarError("Error en stmt_detalles | No se pudieron insertar los detalles del cheque.");
    }
}

// Actualizar registro de carga
$stmt_actualiza = "{call [_SP_CONCILIACIONES_CARGA_CHEQUES_ACTUALIZA](?, ?, ?)}";
$params_actualiza = [
    [$idcarga,      SQLSRV_PARAM_IN],
    [$sheetCount,   SQLSRV_PARAM_IN],
    [$idusuario,    SQLSRV_PARAM_IN]
];
$actualiza_result = sqlsrv_query($conn, $stmt_actualiza, $params_actualiza);
if ($actualiza_result === false) {
    mostrarError("Error en stmt_actualiza | No se pudo actualizar el registro de carga.");
}

// Redirigir al finalizar
header("Location: cargas_cheques.php?op=4");
?>
