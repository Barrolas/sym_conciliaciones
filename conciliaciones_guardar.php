<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$rut_ordenante      = $_GET['rut_ordenante'];
$rut_deudor 	    = $_GET['rut_deudor'];
$transaccion	    = $_GET['transaccion'];

// Inicializar variables de salida
$existe = 0;

// SP para insertar cada -DEUDOR- que cumpla la validación
$sql1 = "{call [_SP_CONCILIACIONES__CONCILIACION_INSERTA](?, ?, ?, ?)}";

// Parámetros para la llamada al stored procedure
$params1 = array(
    array($rut_ordenante, 	SQLSRV_PARAM_IN),
    array($rut_deudor, 		SQLSRV_PARAM_IN),
    array($transaccion, 	SQLSRV_PARAM_IN),
    array(&$existe, 		SQLSRV_PARAM_OUT)
);

// Ejecutar la consulta
$stmt1 = sqlsrv_query($conn, $sql1, $params1);

if ($stmt1 === false) {
    echo "Error in executing statement 1.\n";
    die(print_r(sqlsrv_errors(), true));
}

// Verificar la variable de salida $existe
if ($existe) {
    header("Location: conciliaciones_documentos.php.php?op=1");
} else {
    header("Location: conciliaciones_ordenantes_guardar.php?rut_ordenante=$rut_ordenante&transaccion=$transaccion&rut_deudor=$rut_deudor");
}

?>
