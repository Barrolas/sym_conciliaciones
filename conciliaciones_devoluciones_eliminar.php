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


// Obtener y sanitizar los datos de entrada
$id_ps         = $_GET["id_ps"];
$idusuario     = $_SESSION['ID_USUARIO'];

print_r($id_ps . '; ');

$sql_devolucion = "{call [_SP_CONCILIACIONES_SALDOS_ELIMINAR] (?)}";
$params_devolucion = array(
    array($id_ps,   SQLSRV_PARAM_IN),
);
$stmt_devolucion = sqlsrv_query($conn, $sql_devolucion, $params_devolucion);
if ($stmt_devolucion === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_devolucion'.");
}

// Redireccionar a la página de lista de conciliaciones
header("Location: conciliaciones_lista_saldos.php?op=1");
exit;
