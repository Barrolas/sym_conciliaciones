<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

// Obtener y sanitizar los datos de entrada
$id_doc         = isset($_GET["id_doc"])        ? $_GET["id_doc"] : null;
$transaccion    = isset($_GET["transaccion"])   ? (string)$_GET["transaccion"] : null;
$id_usuario     = 1;

print_r($id_doc . '; ');
print_r($transaccion . '; ');

$sql_seleccion = "EXEC [_SP_CONCILIACIONES_CANALIZACIONES_ASOCIADAS_IDENTIFICAR] ?, ?";

$params_seleccion = array(
    array($id_doc,       SQLSRV_PARAM_IN),
    array($transaccion,  SQLSRV_PARAM_IN),
);

$stmt_seleccion = sqlsrv_query($conn, $sql_seleccion, $params_seleccion);
if ($stmt_seleccion === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Imprimir resultados y continuar con la lógica
while ($seleccion = sqlsrv_fetch_array($stmt_seleccion, SQLSRV_FETCH_ASSOC)) {
    $id_documento   = $seleccion["ID_DOCDEUDORES"];
    $id_pareodoc    = $seleccion["ID_PAREO_DOCDEUDORES"];

    // Procedimiento de eliminación si necesitas continuar
    $sql_eliminar = "EXEC [_SP_CONCILIACIONES_OPERACION_CANALIZACION_ELIMINA] ?, ?, ?";
    $params_eliminar = array(
        array($id_documento,    SQLSRV_PARAM_IN),
        array($id_pareodoc,     SQLSRV_PARAM_IN),
        array($id_usuario,      SQLSRV_PARAM_IN),
    );

    $stmt_eliminar = sqlsrv_prepare($conn, $sql_eliminar, $params_eliminar);
    if ($stmt_eliminar === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    if (!sqlsrv_execute($stmt_eliminar)) {
        die(print_r(sqlsrv_errors(), true));
    }
}

$sql = "{call [_SP_CONCILIACIONES_CANALIZACION_ELIMINAR](?, ?, ?, ?, ?)}";
$params = array(
    array($id_documento,    SQLSRV_PARAM_IN),
    array($rut_cl,          SQLSRV_PARAM_IN),
    array($rut_dd,          SQLSRV_PARAM_IN),
    array($f_venc,          SQLSRV_PARAM_IN),
    array($ndoc,            SQLSRV_PARAM_IN),
);
$stmt = sqlsrv_prepare($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
if (!sqlsrv_execute($stmt)) {
    die(print_r(sqlsrv_errors(), true));
}

// Redireccionar a la página de lista de conciliaciones
header("Location: conciliaciones_lista_canalizados.php?op=2");
exit;
