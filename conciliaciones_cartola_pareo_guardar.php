<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$op = isset($_GET["op"]) ? $_GET["op"] : 0;
$idusuario = $_SESSION['ID_USUARIO'];

/*
print_r($_POST['iddocumento_radio']);
exit;
*/

if (isset($_POST['iddocumento_radio'])) {

    $radio_value = $_POST['iddocumento_radio'][0];
    $valores = explode(',', $radio_value);

    if (count($valores) === 6) {
        $n_documento            = $valores[0];
        $fecha                  = $valores[1];
        $cuenta                 = $valores[2];
        $monto                  = $valores[3];
        $codigo                 = $valores[4];
        $tipo_canal             = $valores[5];
    }
}

// Cambiar el estado de asignación
$estado_asign = 3;
$sql_asign = "{call [_SP_CONCILIACIONES_ASIGNACION_CAMBIA_ESTADO_CODIGO](?, ?, ?, ?)}";
$params_asign = array(
    array($codigo,          SQLSRV_PARAM_IN),
    array($tipo_canal,      SQLSRV_PARAM_IN),
    array($estado_asign,    SQLSRV_PARAM_IN),
    array($idusuario,       SQLSRV_PARAM_IN)
);
$stmt_asign = sqlsrv_query($conn, $sql_asign, $params_asign);
if ($stmt_asign === false) {
    echo "Error in executing statement _asign.\n";
    die(print_r(sqlsrv_errors(), true));
}

// Insertar en conciliación
$sql_conciliacion = "{call [_SP_CONCILIACIONES_CONCILIACION_INSERTAR](?, ?, ?, ?, ?, ?, ?)}";
$params_conciliacion = array(
    array($n_documento,          SQLSRV_PARAM_IN),
    array($cuenta,               SQLSRV_PARAM_IN),
    array($fecha,                SQLSRV_PARAM_IN),
    array($codigo,               SQLSRV_PARAM_IN),
    array($tipo_canal,           SQLSRV_PARAM_IN),
    array($monto,                SQLSRV_PARAM_IN),
    array($idusuario,            SQLSRV_PARAM_IN)
);
$stmt_conciliacion = sqlsrv_query($conn, $sql_conciliacion, $params_conciliacion);
if ($stmt_conciliacion === false) {
    echo "Error in executing statement conciliacion.\n";
    die(print_r(sqlsrv_errors(), true));
}

header("Location: conciliaciones_cartola_pendientes.php?op=1");
exit;
?>