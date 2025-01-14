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


$id_doc             = $_GET['id_doc']; 
$transaccion        = $_GET['transaccion'];

$sql_entrecta = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_TRANSACCION_VALIDA](?)}";
$params_entrecta = array(
    array($transaccion,    SQLSRV_PARAM_IN),
);
$stmt_entrecta = sqlsrv_query($conn, $sql_entrecta, $params_entrecta);
if ($stmt_entrecta === false) {
    die(print_r(sqlsrv_errors(), true));
}
$entrecta = sqlsrv_fetch_array($stmt_entrecta, SQLSRV_FETCH_ASSOC);
$transaccion_entrecta = $entrecta['TRANSACCION'];
if($transaccion_entrecta <> 0){
//Si es entrecuenta, busca la TRANSACCION original asociada a las operaciones. 
    $transaccion = $entrecta['TRANSACCION'];
}

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