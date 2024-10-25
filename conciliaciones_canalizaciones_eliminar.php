<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

// Obtener y sanitizar los datos de entrada
$id_doc         = $_GET["id_doc"];      
$transaccion    = $_GET["transaccion"];   
$id_usuario     = $_SESSION['ID_USUARIO'];;

print_r($id_doc . '; ');
print_r($transaccion . '; ');

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


$sql_seleccion = "EXEC [_SP_CONCILIACIONES_OPERACIONES_ASOCIADAS_IDENTIFICAR] ?, ?, ?, ?";
$params_seleccion = array(
    array($id_doc,      SQLSRV_PARAM_IN),
    array($transaccion, SQLSRV_PARAM_IN),
    array(1,            SQLSRV_PARAM_IN),
    array('2-3',        SQLSRV_PARAM_IN)
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
