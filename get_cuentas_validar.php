<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$cuenta                 = $_POST['cuenta_benef'];
$rut_cliente            = $_POST['rut_cliente'];
$es_entrecuenta         = 0;
$cuenta_correspondiente = '';


$sql = "{call [_SP_CONCILIACIONES_CONSULTA_ENTRECUENTAS] (?, ?)}";
$params = [
    [$cuenta,                   SQLSRV_PARAM_IN],
    [$rut_cliente,              SQLSRV_PARAM_IN],
    [&$es_entrecuenta,          SQLSRV_PARAM_INOUT],
    [&$cuenta_correspondiente,  SQLSRV_PARAM_INOUT]
];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

echo $es_entrecuenta;
echo $cuenta_correspondiente;

?>
