<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

// Obtener y sanitizar los datos de entrada
$id_asignacion  = $_GET["id_asig"];      
$id_doc         = $_GET["iddoc"];      
$transaccion    = $_GET["transaccion"];      
$id_usuario     = 1;

print_r($id_asignacion . '; ');
print_r($id_doc . '; ');
print_r($transaccion . '; ');


$sql_seleccion = "EXEC [_SP_CONCILIACIONES_OPERACIONES_ASOCIADAS_IDENTIFICAR] ?, ?, ?, ?";
$params_seleccion = array(
    array($id_doc,      SQLSRV_PARAM_IN),
    array($transaccion, SQLSRV_PARAM_IN),   
    array(1,            SQLSRV_PARAM_IN), // ID_ESTADO
    array(4,            SQLSRV_PARAM_IN)  // ID_ETAPA
);
$stmt_seleccion = sqlsrv_query($conn, $sql_seleccion, $params_seleccion);
if ($stmt_seleccion === false) {
    echo "Error in executing statement seleccion.\n";
    die(print_r(sqlsrv_errors(), true));
}

// Imprimir resultados y continuar con la lógica
while ($seleccion = sqlsrv_fetch_array($stmt_seleccion, SQLSRV_FETCH_ASSOC)) {
    
    $id_documento   = $seleccion["ID_DOCDEUDORES"];
    $id_pareodoc    = $seleccion["ID_PAREO_DOCDEUDORES"];

    // Procedimiento de eliminación si necesitas continuar
    $sql_opeliminar = "EXEC [_SP_CONCILIACIONES_OPERACION_ASIGNACION_ELIMINA] ?, ?, ?, ?";
    $params_opeliminar = array(
        array($id_documento,    SQLSRV_PARAM_IN),
        array($id_pareodoc,     SQLSRV_PARAM_IN),
        array($id_asignacion,   SQLSRV_PARAM_IN),
        array($id_usuario,      SQLSRV_PARAM_IN)
    );
    $stmt_opeliminar = sqlsrv_prepare($conn, $sql_opeliminar, $params_opeliminar);
    if ($stmt_opeliminar === false) {
        echo "Error in preparing statement opeliminar.\n";
        die(print_r(sqlsrv_errors(), true));
    }
    if (!sqlsrv_execute($stmt_opeliminar)) {
        echo "Error in executing statement opeliminar.\n";
        die(print_r(sqlsrv_errors(), true));
    }

}

// Redireccionar a la página de lista de conciliaciones
header("Location: conciliaciones_lista_conciliados.php?op=2");
exit;