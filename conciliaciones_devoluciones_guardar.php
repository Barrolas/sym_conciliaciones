<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$transaccion        = $_GET['transaccion']; 
$rut_ordenante      = $_GET['rut_ordenante']; 
$cuenta             = $_GET['cuenta'];
$fecha_rec          = $_GET['fecha_rec'];
$monto_diferencia   = 0;
$rut_cliente        = '';
$rut_deudor         = '';
$estado_pareo       = 1;
$idusuario          = 1;
$es_entrecuentas    = 0;
$tipo_pareosistema  = 4;

$monto = isset($_GET['monto']) ? $_GET['monto'] : '';
$monto = str_replace('.', '', $monto);
$monto = is_numeric($monto) ? (int) $monto : 0;

$existe_pareo       = 0;
$idpareo_sistema    = 0;


$sql1 = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_INSERTA](?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
$params1 = array(
    array($rut_ordenante,       SQLSRV_PARAM_IN),
    array($rut_deudor,          SQLSRV_PARAM_IN),
    array($transaccion,         SQLSRV_PARAM_IN),
    array($monto_diferencia,    SQLSRV_PARAM_IN),
    array($rut_cliente,         SQLSRV_PARAM_IN),
    array($estado_pareo,        SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN),
    array($es_entrecuentas,     SQLSRV_PARAM_IN),
    array(&$existe_pareo,       SQLSRV_PARAM_OUT),
    array(&$idpareo_sistema,    SQLSRV_PARAM_OUT)
);
$stmt1 = sqlsrv_query($conn, $sql1, $params1);

if ($stmt1 === false) {
    echo "Error in executing statement 1.\n";
    die(print_r(sqlsrv_errors(), true));
}
// Verificar la variable de salida $existe
if ($existe_pareo == 1) {
    header("Location: conciliaciones_transferencias_pendientes.php?op=3");
}

$sql_tipo_ps = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_DETALLES_INSERTA](?, ?, ?, ?)}";
$params_tipo_ps = array(
    array($transaccion,         SQLSRV_PARAM_IN),
    array($idpareo_sistema,     SQLSRV_PARAM_IN),
    array($tipo_pareosistema,   SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN)
);
$stmt_tipo_ps = sqlsrv_query($conn, $sql_tipo_ps, $params_tipo_ps);

if ($stmt_tipo_ps === false) {
    echo "Error in executing statement tipo_ps.\n";
    die(print_r(sqlsrv_errors(), true));
}

$sql_saldo = "{call [_SP_CONCILIACIONES_SALDO_INSERTA] (?, ?)}";
$params_saldo = array(
    array($idpareo_sistema,     SQLSRV_PARAM_IN),
    array($monto,               SQLSRV_PARAM_IN)
);
$stmt_saldo = sqlsrv_query($conn, $sql_saldo, $params_saldo);

if ($stmt_saldo === false) {
    echo "Error in executing statement saldo.\n";
    die(print_r(sqlsrv_errors(), true));
}

$sql_devolucion = "{call [_SP_CONCILIACIONES_MOVIMIENTO_DEVOLUCION_INSERTA] (?, ?, ?, ?, ?, ?)}";
$params_devolucion = array(
    array($cuenta,              SQLSRV_PARAM_IN),
    array($transaccion,         SQLSRV_PARAM_IN),
    array($idpareo_sistema,     SQLSRV_PARAM_IN),
    array($fecha_rec,           SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN),
    array($monto,               SQLSRV_PARAM_IN)
);
$stmt_devolucion = sqlsrv_query($conn, $sql_devolucion, $params_devolucion);

if ($stmt_devolucion === false) {
    echo "Error in executing statement devolucion.\n";
    die(print_r(sqlsrv_errors(), true));
}

header("Location: conciliaciones_transferencias_pendientes.php?op=1");
exit;