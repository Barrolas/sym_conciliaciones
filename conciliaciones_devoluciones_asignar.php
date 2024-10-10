<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$idusuario = $_SESSION['ID_USUARIO'];

$sql = "EXEC [_SP_CONCILIACIONES_SALDOS_LISTA]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($conciliacion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

    $id_ps          = $conciliacion['ID_PAREO_SISTEMA'];
    $transaccion    = $conciliacion['TRANSACCION'];
    
    $sql_asig = "{call [_SP_CONCILIACIONES_ASIGNACION_DEVOLUCION_INSERTAR](?, ?, ?)}";
    $params_asig = array(
        array($id_ps,       SQLSRV_PARAM_IN),
        array($transaccion, SQLSRV_PARAM_IN),
        array($idusuario,   SQLSRV_PARAM_IN)
    );
    $stmt_asig = sqlsrv_query($conn, $sql_asig, $params_asig);
    
    if ($stmt_asig === false) {
        echo "Error in executing statement asig.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    $sql_asig = "{call [_SP_CONCILIACIONES_SALDO_CAMBIA_ESTADO](?, ?)}";
    $params_asig = array(
        array($id_ps,   SQLSRV_PARAM_IN),
        array(2,        SQLSRV_PARAM_IN)
    );
    $stmt_asig = sqlsrv_query($conn, $sql_asig, $params_asig);
    
    if ($stmt_asig === false) {
        echo "Error in executing statement asig.\n";
        die(print_r(sqlsrv_errors(), true));
    }
    
}

header("Location: conciliaciones_lista_saldos.php?op=1");
exit;
