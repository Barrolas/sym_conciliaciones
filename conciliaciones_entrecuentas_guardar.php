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
$transaccion    = $_GET['transaccion']; 
$cuenta_ord     = $_GET['cuenta_ord']; 
$cuenta_ben     = $_GET['cuenta_ben'];
$rut_ord        = '77206260-5';
$id_ps_nuevo    = 0;

$id_entrecuentas = isset($_POST['iddocumento_radio'][0]) ? $_POST['iddocumento_radio'][0] : null;

$sql_detalles = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_CONSULTA] (?)}";
$params_detalles = [
    [$id_entrecuentas, SQLSRV_PARAM_IN],
];
$stmt_detalles = sqlsrv_query($conn, $sql_detalles, $params_detalles);
if ($stmt_detalles === false) {
    mostrarError("Error en la ejecución de la consulta de detalles de entrecuentas. -> stmt_detalles");
}
$detalles = sqlsrv_fetch_array($stmt_detalles, SQLSRV_FETCH_ASSOC);

$id_ps_origen = $detalles['ID_PAREO_SISTEMA']; 

/* =============== Pareo Sistema creado para Entrecuentas =============== */

$sql_tr_ord = "{call [_SP_CONCILIACIONES_TRANSFERENCIAS_PENDIENTES_CONSULTA] (?, ?)}";
$params_tr_ord = [
    [$rut_ord, SQLSRV_PARAM_IN],
    [$transaccion, SQLSRV_PARAM_IN]
];
$stmt_tr_ord = sqlsrv_query($conn, $sql_tr_ord, $params_tr_ord);
if ($stmt_tr_ord === false) {
    mostrarError("Error en la ejecución de la consulta de transferencias pendientes. -> stmt_tr_ord");
}
$tr_ord = sqlsrv_fetch_array($stmt_tr_ord, SQLSRV_FETCH_ASSOC);

$rut_deudor         = null;
$monto_diferencia   = 0;
$rut_cliente        = null;
$estado_pareo       = 0;
$es_entrecuentas    = 3;
$existe_pareo       = 0;
$id_ps_nuevo        = 0;

$sql_ps_inserta = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_INSERTA](?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
$params_ps_inserta = [
    [$rut_ord, SQLSRV_PARAM_IN],
    [$rut_deudor, SQLSRV_PARAM_IN],
    [$transaccion, SQLSRV_PARAM_IN],
    [$monto_diferencia, SQLSRV_PARAM_IN],
    [$rut_cliente, SQLSRV_PARAM_IN],
    [$estado_pareo, SQLSRV_PARAM_IN],
    [$idusuario, SQLSRV_PARAM_IN],
    [$es_entrecuentas, SQLSRV_PARAM_IN],
    [&$existe_pareo, SQLSRV_PARAM_OUT],
    [&$id_ps_nuevo, SQLSRV_PARAM_OUT]
];
$stmt_ps_inserta = sqlsrv_query($conn, $sql_ps_inserta, $params_ps_inserta);
if ($stmt_ps_inserta === false) {
    mostrarError("Error en la inserción de pareo sistema. -> stmt_ps_inserta");
}
if ($existe_pareo == 1) {
    header("Location: conciliaciones_transferencias_pendientes.php?op=3");
    exit;
}

$tipo_pareosistema = 3;
$sql_ps_detalles_inserta = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_DETALLES_INSERTA](?, ?, ?, ?)}";
$params_ps_detalles_inserta = [
    [$transaccion, SQLSRV_PARAM_IN],
    [$id_ps_nuevo, SQLSRV_PARAM_IN],
    [$tipo_pareosistema, SQLSRV_PARAM_IN],
    [$idusuario, SQLSRV_PARAM_IN]
];
$stmt_ps_detalles_inserta = sqlsrv_query($conn, $sql_ps_detalles_inserta, $params_ps_detalles_inserta);
if ($stmt_ps_detalles_inserta === false) {
    mostrarError("Error en la inserción de detalles del pareo sistema. -> stmt_ps_detalles_inserta");
}

$n_entrecta = 2;
$sql_ps_actualiza = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_PAREO_SISTEMA_ACTUALIZA](?, ?, ?, ?)}";
$params_ps_actualiza = [
    [$id_ps_origen, SQLSRV_PARAM_IN],
    [$transaccion, SQLSRV_PARAM_IN],
    [$n_entrecta, SQLSRV_PARAM_IN],
    [$idusuario, SQLSRV_PARAM_IN]
];
$stmt_ps_actualiza = sqlsrv_query($conn, $sql_ps_actualiza, $params_ps_actualiza);
if ($stmt_ps_actualiza === false) {
    mostrarError("Error en la actualización de entrecuentas. -> stmt_ps_actualiza");
}

$estado_entrecuentas = 2;
$sql_estado = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_CAMBIA_ESTADO] (?, ?, ?)}";
$params_estado = [
    [$id_entrecuentas, SQLSRV_PARAM_IN],
    [$estado_entrecuentas, SQLSRV_PARAM_IN],
    [$idusuario, SQLSRV_PARAM_IN]
];
$stmt_estado = sqlsrv_query($conn, $sql_estado, $params_estado);
if ($stmt_estado === false) {
    mostrarError("Error al cambiar el estado de las entrecuentas. -> stmt_estado");
}

header("Location: conciliaciones_transferencias_pendientes.php?op=1");
exit;
