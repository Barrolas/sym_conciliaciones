<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

// Obtener y sanitizar los datos de entrada
$rr_cl = $_GET["r_cl"];
$rr_dd = $_GET["r_dd"];
$f_venc = $_GET["f_venc"];
$ndoc = $_GET["ndoc"];

// Asegúrate de que el formato de f_venc sea correcto
$f_venc = trim($f_venc);  // Elimina cualquier espacio extra

// Preparar la llamada al procedimiento almacenado
$sql = "{CALL [_SP_CONCILIACIONES_CANALIZACION_ELIMINAR](?, ?, ?, ?)}";

// Preparar los parámetros
$params = array(
    array($rr_cl,   SQLSRV_PARAM_IN),
    array($rr_dd,   SQLSRV_PARAM_IN),
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
