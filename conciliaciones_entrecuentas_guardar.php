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
/*
print_r($_POST['iddocumento_radio']);
exit;
*/
$id_entrecuentas = isset($_POST['iddocumento_radio'][0]) ? $_POST['iddocumento_radio'][0] : null;
/*
print_r($id_entrecuentas);
exit;
*/

$sql_detalles = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_CONSULTA] (?)}";
$params_detalles = array(
    array($id_entrecuentas,         SQLSRV_PARAM_IN),
);
$stmt_detalles = sqlsrv_query($conn, $sql_detalles, $params_detalles);
if ($stmt_detalles === false) {
    echo "Error en la ejecución de la declaración detalles.\n";
    die(print_r(sqlsrv_errors(), true));
}
$detalles = sqlsrv_fetch_array($stmt_detalles, SQLSRV_FETCH_ASSOC);

$id_ps_origen = $detalles['ID_PAREO_SISTEMA']; 

/* =============== Pareo Sistema creado para Entrecuentas =============== */

$sql_tr_ord = "{call [_SP_CONCILIACIONES_TRANSFERENCIAS_PENDIENTES_CONSULTA] (?, ?)}";
$params_tr_ord = array(
    array($rut_ord,         SQLSRV_PARAM_IN),
    array($transaccion,     SQLSRV_PARAM_IN)
);
$stmt_tr_ord = sqlsrv_query($conn, $sql_tr_ord, $params_tr_ord);
if ($stmt_tr_ord === false) {
    echo "Error en la ejecución de la declaración tr_ord.\n";
    die(print_r(sqlsrv_errors(), true));
}
$tr_ord = sqlsrv_fetch_array($stmt_tr_ord, SQLSRV_FETCH_ASSOC);

$rut_deudor         = null;
$monto_diferencia   = 0;
$rut_cliente        = null;
$estado_pareo       = 0;
$es_entrecuentas    = 3;
$existe_pareo       = 0;
$id_ps_nuevo        = 0;
/*
print_r($tr_ord);
exit;
*/
$sql1 = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_INSERTA](?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
$params1 = array(
    array($rut_ord,             SQLSRV_PARAM_IN),
    array($rut_deudor,          SQLSRV_PARAM_IN),
    array($transaccion,         SQLSRV_PARAM_IN),
    array($monto_diferencia,    SQLSRV_PARAM_IN),
    array($rut_cliente,         SQLSRV_PARAM_IN),
    array($estado_pareo,        SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN),
    array($es_entrecuentas,     SQLSRV_PARAM_IN),
    array(&$existe_pareo,       SQLSRV_PARAM_OUT),
    array(&$id_ps_nuevo,        SQLSRV_PARAM_OUT)
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

/*
print_r($id_ps_nuevo);
exit;
*/

$tipo_pareosistema = 3;
$sql_tipo_ps = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_DETALLES_INSERTA](?, ?, ?, ?)}";
$params_tipo_ps = array(
    array($transaccion,         SQLSRV_PARAM_IN),
    array($id_ps_nuevo,         SQLSRV_PARAM_IN),
    array($tipo_pareosistema,   SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN)
);
$stmt_tipo_ps = sqlsrv_query($conn, $sql_tipo_ps, $params_tipo_ps);
if ($stmt_tipo_ps === false) {
    echo "Error in executing statement tipo_ps.\n";
    die(print_r(sqlsrv_errors(), true));
}

$n_entrecta = 3;
$sql_psactualiza = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_PAREO_SISTEMA_ACTUALIZA] (?, ?, ?)}";
$params_psactualiza = array(
    array($id_ps_nuevo,     SQLSRV_PARAM_IN),
    array($transaccion,     SQLSRV_PARAM_IN),
    array($n_entrecta,      SQLSRV_PARAM_IN),
    array($idusuario,       SQLSRV_PARAM_IN)
);

/* =============== Actualizar Entrecuentas =============== */
$n_entrecta = 2;
$sql_psactualiza = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_PAREO_SISTEMA_ACTUALIZA] (?, ?, ?, ?)}";
$params_psactualiza = array(
    array($id_ps_origen,    SQLSRV_PARAM_IN),
    array($transaccion,     SQLSRV_PARAM_IN),
    array($n_entrecta,      SQLSRV_PARAM_IN),
    array($idusuario,       SQLSRV_PARAM_IN)
);
$stmt_psactualiza = sqlsrv_query($conn, $sql_psactualiza, $params_psactualiza);
if ($stmt_psactualiza === false) {
    echo "Error en la ejecución de la declaración _psactualiza.\n";
    die(print_r(sqlsrv_errors(), true));
}

$estado_entrecuentas = 2;
$sql_estado = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_CAMBIA_ESTADO] (?, ?, ?)}";
$params_estado = array(
    array($id_entrecuentas,         SQLSRV_PARAM_IN),
    array($estado_entrecuentas,     SQLSRV_PARAM_IN),
    array($idusuario,               SQLSRV_PARAM_IN)
);
$stmt_estado = sqlsrv_query($conn, $sql_estado, $params_estado);
if ($stmt_estado === false) {
    echo "Error en la ejecución de la declaración _estado.\n";
    die(print_r(sqlsrv_errors(), true));
}

header("Location: conciliaciones_transferencias_pendientes.php?op=1");
exit;