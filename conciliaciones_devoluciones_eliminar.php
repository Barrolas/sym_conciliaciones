<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

// Obtener y sanitizar los datos de entrada
$id_ps         = $_GET["id_ps"];
$id_usuario     = $_SESSION['ID_USUARIO'];;

print_r($id_ps . '; ');

$sql_devolucion = "{call [_SP_CONCILIACIONES_SALDOS_ELIMINAR] (?)}";
$params_devolucion = array(
    array($id_ps,   SQLSRV_PARAM_IN),
);
$stmt_devolucion = sqlsrv_query($conn, $sql_devolucion, $params_devolucion);
if ($stmt_devolucion === false) {
    echo "Error in executing statement devolucion.\n";
    die(print_r(sqlsrv_errors(), true));
}

// Redireccionar a la página de lista de conciliaciones
header("Location: conciliaciones_lista_saldos.php?op=1");
exit;
