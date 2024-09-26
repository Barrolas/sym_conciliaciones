<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$idusuario              = 1;
$idproceso              = 0;
$estado_canalizacion    = 2;
$total_procesados       = 0;

$sql_proceso = "{call [_SP_CONCILIACIONES_CANALIZACION_PROCESO_INSERTA](?, ?)}";
$params_proceso = array(
    array($idusuario,   SQLSRV_PARAM_IN),
    array(&$idproceso,  SQLSRV_PARAM_INOUT)
);

$stmt_proceso = sqlsrv_query($conn, $sql_proceso, $params_proceso);

if ($stmt_proceso === false) {
    die(print_r(sqlsrv_errors(), true));
}

$sql = "EXEC [_SP_CONCILIACIONES_CANALIZADOS_LISTA]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($conciliacion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

    $id_documento       = $conciliacion['ID_DOCDEUDORES'];
    $diferencia_doc     = 0;

    $sql_diferencia = "{call [_SP_CONCILIACIONES_DIFERENCIAS_CONSULTA](?, ?)}";
    $params_diferencia = array(
        array($id_documento,        SQLSRV_PARAM_IN),
        array(&$diferencia_doc,     SQLSRV_PARAM_OUT)
    );

    $stmt_diferencia = sqlsrv_query($conn, $sql_diferencia, $params_diferencia);

    if ($stmt_diferencia === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Procesar resultados de la consulta de detalles
    $diferencia = sqlsrv_fetch_array($stmt_diferencia, SQLSRV_FETCH_ASSOC);

    if ($diferencia_doc == 0) {

        $sql_detalles = "{call [_SP_CONCILIACIONES_CANALIZACION_PROCESO_DETALLES_INSERTA](?, ?)}";
        $params_detalles = array(
            array($idproceso,       SQLSRV_PARAM_IN),
            array($id_documento,    SQLSRV_PARAM_IN)
        );

        $stmt_detalles = sqlsrv_query($conn, $sql_detalles, $params_detalles);

        if ($stmt_detalles === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $sql_estado = "{call [_SP_CONCILIACIONES_CANALIZACION_CAMBIA_ESTADO](?, ?)}";
        $params_estado = array(
            array($id_documento,        SQLSRV_PARAM_IN),
            array($estado_canalizacion, SQLSRV_PARAM_IN)
        );

        $stmt_estado = sqlsrv_query($conn, $sql_estado, $params_estado);

        if ($stmt_estado === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $sql_operacion = "{call [_SP_CONCILIACIONES_OPERACION_PROCESO_INSERTA](?, ?)}";
        $params_operacion = array(
            array($id_documento,    SQLSRV_PARAM_IN),
            array($idusuario,       SQLSRV_PARAM_IN)
        );

        $stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);

        if ($stmt_operacion === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $total_procesados++;
        
    };
};

$sql_actualiza = "{call [_SP_CONCILIACIONES_CANALIZACION_PROCESO_ACTUALIZA](?, ?, ?)}";
$params_actualiza = array(
    array($idproceso,           SQLSRV_PARAM_IN),
    array($total_procesados,    SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN)
);

$stmt_actualiza = sqlsrv_query($conn, $sql_actualiza, $params_actualiza);

if ($stmt_actualiza === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Redirigir a otra página
header("Location: conciliaciones_lista_canalizados.php?op=1");
exit;
