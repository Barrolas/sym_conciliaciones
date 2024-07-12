<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//$iddocumento_checked    = $_POST['iddocumento_checkbox'];
//print_r($monto_checked);
//exit;

$op             = isset($_GET["op"]) ? $_GET["op"] : 0;

if (isset($_POST['iddocumento_checkbox'])) {
    // Creamos arreglos vacíos para almacenar los valores separados
    $id_documentos = array();
    $montos = array();

    // Recorremos cada valor del arreglo de checkboxes
    foreach ($_POST['iddocumento_checkbox'] as $checkbox_value) {
        // Dividimos el valor usando la coma como delimitador
        $valores = explode(',', $checkbox_value);

        // $valores[0] contiene ID_DOCUMENTO y $valores[1] contiene MONTO
        $id_documentos[] = $valores[0];
        $montos[] = $valores[1];
    }
}

$rut_ordenante          = $_GET['rut_ordenante'];
$rut_deudor             = $_GET['rut_deudor'];
$transaccion            = $_GET['transaccion'];

// Inicializar variables de salida
$existe_pareo       = 0;
$idpareo_sistema    = 0;

// SP para insertar en PAREO_SISTEMA y obtener ID_PAREO_SISTEMA
$sql1 = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_INSERTA](?, ?, ?, ?, ?)}";

// Parámetros para la llamada al stored procedure
$params1 = array(
    array($rut_ordenante,       SQLSRV_PARAM_IN),
    array($rut_deudor,          SQLSRV_PARAM_IN),
    array($transaccion,         SQLSRV_PARAM_IN),
    array(&$existe_pareo,       SQLSRV_PARAM_OUT),
    array(&$idpareo_sistema,    SQLSRV_PARAM_OUT)
);

// Ejecutar la consulta
$stmt1 = sqlsrv_query($conn, $sql1, $params1);

if ($stmt1 === false) {
    echo "Error in executing statement 1.\n";
    die(print_r(sqlsrv_errors(), true));
}

// Verificar la variable de salida $existe
if ($existe_pareo && $op == 1) {
    header("Location: conciliaciones_documentos.php?op=2&rut_ordenante=$rut_ordenante&transaccion=$transaccion&rut_deudor=$rut_deudor");
    exit;
}
if ($existe_pareo && $op == 2) {
    header("Location: conciliaciones_documentos_b.php?op=2&rut_ordenante=$rut_ordenante&transaccion=$transaccion&rut_deudor=$rut_deudor");
    exit;
}

// SP para insertar en DOCDEUDORES
$sql2 = "{call [_SP_CONCILIACIONES_PAREO_DOCDEUDORES_INSERTA](?, ?, ?)}";

foreach ($id_documentos as $index => $id_docdeudores) {
    // Inicializar variable de salida para cada iteración

    $existe_doc = 0;

    // Parámetros para la llamada al stored procedure
    $sql2 = "{call [_SP_CONCILIACIONES_PAREO_DOCDEUDORES_INSERTA] (?, ?, ?, ?)}"; // Reemplaza NOMBRE_SP_INSERTAR_DOCUMENTO por el nombre correcto de tu SP
    $params2 = array(
        array($idpareo_sistema, SQLSRV_PARAM_IN),
        array($id_docdeudores,  SQLSRV_PARAM_IN),
        array($montos[$index],  SQLSRV_PARAM_IN), // Accedemos al MONTO usando el índice
        array(&$existe_doc,     SQLSRV_PARAM_OUT)
    );

    // Ejecutar la consulta
    $stmt2 = sqlsrv_query($conn, $sql2, $params2);

    if ($stmt2 === false) {
        echo "Error in executing statement 2.\n";
        die(print_r(sqlsrv_errors(), true));
    }
}
if ($existe_doc && $op == 1) {
    header("Location: conciliaciones_documentos.php?op=2&rut_ordenante=$rut_ordenante&transaccion=$transaccion&rut_deudor=$rut_deudor");
}
if ($existe_doc && $op == 2) {
    header("Location: conciliaciones_documentos_b.php?op=2&rut_ordenante=$rut_ordenante&transaccion=$transaccion&rut_deudor=$rut_deudor");
} else {

    $sql3 = "{call [_SP_CONCILIACIONES_SALDO_INSERTA] (?)}";
    $params3 = array(
        array($idpareo_sistema, SQLSRV_PARAM_IN)
    );

    // Ejecutar la consulta
    $stmt3 = sqlsrv_query($conn, $sql3, $params3);

    if ($stmt3 === false) {
        echo "Error in executing statement 3.\n";
        die(print_r(sqlsrv_errors(), true));
    }
    if ($op == 1) {
        header("Location: conciliaciones_documentos.php?op=1&rut_ordenante=$rut_ordenante&transaccion=$transaccion&rut_deudor=$rut_deudor");
    }
    if ($op == 2) {
        header("Location: conciliaciones_documentos_b.php?op=1&rut_ordenante=$rut_ordenante&transaccion=$transaccion&rut_deudor=$rut_deudor");
    }

}
