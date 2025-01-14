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


$sql_lista_saldos = "EXEC [_SP_CONCILIACIONES_SALDOS_LISTA]";
$stmt_lista_saldos = sqlsrv_query($conn, $sql_lista_saldos);
if ($stmt_lista_saldos === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_lista_saldos'.");
}
while ($conciliacion = sqlsrv_fetch_array($stmt_lista_saldos, SQLSRV_FETCH_ASSOC)) {

    $id_ps          = $conciliacion['ID_PAREO_SISTEMA'];
    $transaccion    = $conciliacion['TRANSACCION'];
    $idsaldo        = $conciliacion['ID_CONCILIACION_SALDO'];
    
    $sql_asig = "{call [_SP_CONCILIACIONES_ASIGNACION_DEVOLUCION_INSERTAR](?, ?, ?, ?)}";
    $params_asig = array(
        array($id_ps,       SQLSRV_PARAM_IN),
        array($transaccion, SQLSRV_PARAM_IN),
        array($idsaldo,     SQLSRV_PARAM_IN),
        array($idusuario,   SQLSRV_PARAM_IN)
    );
    $stmt_asig = sqlsrv_query($conn, $sql_asig, $params_asig);
    
    if ($stmt_asig === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_asig'.");
    }

    $sql_estado = "{call [_SP_CONCILIACIONES_SALDO_CAMBIA_ESTADO](?, ?)}";
    $params_estado = array(
        array($id_ps,   SQLSRV_PARAM_IN),
        array(2,        SQLSRV_PARAM_IN)
    );
    $stmt_estado = sqlsrv_query($conn, $sql_estado, $params_estado);
    
    if ($stmt_estado === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_estado'.");
    }
    
}

header("Location: conciliaciones_lista_saldos.php?op=2");
exit;
