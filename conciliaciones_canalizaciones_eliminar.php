<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

// Obtener y sanitizar los datos de entrada
$id_doc = $_GET["id_doc"];
$rut_cl = $_GET["r_cl"];
$rut_dd = $_GET["r_dd"];
$f_venc = $_GET["f_venc"];
$ndoc   = $_GET["ndoc"];

/*
print_r($id_doc);
exit;
*/

$f_venc = trim($f_venc);

$sql = "{CALL [_SP_CONCILIACIONES_CANALIZACION_ELIMINAR](?, ?, ?, ?, ?)}";

$params = array(
    array($id_doc,  SQLSRV_PARAM_IN),
    array($rut_cl,  SQLSRV_PARAM_IN),
    array($rut_dd,  SQLSRV_PARAM_IN),
    array($f_venc,  SQLSRV_PARAM_IN),
    array($ndoc,    SQLSRV_PARAM_IN)
);

// Preparar la consulta
$stmt = sqlsrv_prepare($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Ejecutar la consulta
if (!sqlsrv_execute($stmt)) {
    die(print_r(sqlsrv_errors(), true));
}

// Redireccionar a la página de lista de conciliaciones
header("Location: conciliaciones_lista_canalizados.php?op=2");
exit;
?>
