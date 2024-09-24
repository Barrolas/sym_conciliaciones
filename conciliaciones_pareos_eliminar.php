<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_doc             = $_GET['id_doc']; 
$transaccion        = $_GET['transaccion']; 

$sql_eliminar = "{call [_SP_CONCILIACIONES_PAREOS_ELIMINAR] (?, ?)}";
$params_eliminar = array(
    array($id_doc,          SQLSRV_PARAM_IN),
    array($transaccion,     SQLSRV_PARAM_IN)
);
$stmt_eliminar = sqlsrv_query($conn, $sql_eliminar, $params_eliminar);

if ($stmt_eliminar === false) {
    echo "Error in executing statement eliminar.\n";
    die(print_r(sqlsrv_errors(), true));
}

header("Location: conciliaciones_lista_pareados.php?op=5");
exit;