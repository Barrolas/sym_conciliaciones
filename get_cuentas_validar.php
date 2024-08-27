<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // Establece el tipo de contenido como JSON

$cuenta                 = $_POST['cuenta'] ?? ''; // Asegúrate de que las variables estén definidas
$rut_cliente            = $_POST['rut_cliente'] ?? '';
$es_entrecuentas         = 0;
$cuenta_correspondiente = '';

// Preparar la consulta y los parámetros
$sql = "{call [_SP_CONCILIACIONES_CONSULTA_ENTRECUENTAS] (?, ?, ?, ?)}";
$params = [
    [$cuenta, SQLSRV_PARAM_IN],
    [$rut_cliente, SQLSRV_PARAM_IN],
    [&$es_entrecuentas, SQLSRV_PARAM_INOUT],
    [&$cuenta_correspondiente, SQLSRV_PARAM_INOUT]
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    // Si hay un error en la consulta, devuelve un JSON con el mensaje de error
    echo json_encode([
        'error' => 'Error en la consulta SQL',
        'details' => sqlsrv_errors()
    ]);
    exit();
}

// Devuelve un JSON con las variables
echo json_encode([
    'es_entrecuentas' => $es_entrecuentas,
    'cuenta_correspondiente' => $cuenta_correspondiente
]);
?>
