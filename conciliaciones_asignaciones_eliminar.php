<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

// Obtener y sanitizar los datos de entrada
$id_asignacion  = $_GET["idasig"];
$id_doc         = $_GET["iddoc"];
$transaccion    = $_GET["transaccion"];
$motivo         = isset($_GET["motivo"]) ? $_GET["motivo"] : '';
$id_usuario     = $_SESSION['ID_USUARIO'];

// Sanitizar los datos para prevenir inyecciones SQL
$id_asignacion  = intval($id_asignacion);
$id_doc         = intval($id_doc);
$transaccion    = htmlspecialchars($transaccion,    ENT_QUOTES, 'UTF-8');
$motivo         = htmlspecialchars($motivo,         ENT_QUOTES, 'UTF-8');

/*
print_r($_GET);
print_r($id_asignacion . '; ');
print_r($id_doc . '; ');
print_r($transaccion . '; ');
*/

$estado1 = 1;
$estado2 = 1;
$sql_asign    = "EXEC [_SP_CONCILIACIONES_ASIGNADOS_CONSULTA] ?, ?, ?";
$params_asign = array(
    array($id_asignacion,   SQLSRV_PARAM_IN),
    array($estado1,         SQLSRV_PARAM_IN),
    array($estado2,         SQLSRV_PARAM_IN),
);
$stmt_asign = sqlsrv_query($conn, $sql_asign, $params_asign);
if ($stmt_asign === false) {
    die(print_r(sqlsrv_errors(), true));
}
$asignados = sqlsrv_fetch_array($stmt_asign, SQLSRV_FETCH_ASSOC);

$id_pareo_sis = $asignados['ID_PAREO_SISTEMA'];

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
//Si es entrecuenta, busca el ID_PAREO_SISTEMA de la TRANSACCION original asociada a las operaciones. 
    $id_pareo_sis = $entrecta['ID_PAREO_SISTEMA'];
}

$sql_operacion = "{call [_SP_CONCILIACIONES_OPERACION_CONSULTA](?)}";
$params_operacion = array(
    array($id_pareo_sis,    SQLSRV_PARAM_IN),
);
$stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
if ($stmt_operacion === false) {
    die(print_r(sqlsrv_errors(), true));
}
$operaciones = sqlsrv_fetch_array($stmt_operacion, SQLSRV_FETCH_ASSOC);

$transaccion = $operaciones['TRANSACCION'];

$sql_seleccion = "EXEC [_SP_CONCILIACIONES_OPERACIONES_ASOCIADAS_IDENTIFICAR] ?, ?, ?, ?";
$params_seleccion = array(
    array($id_doc,          SQLSRV_PARAM_IN),
    array($transaccion,     SQLSRV_PARAM_IN),
    array(1,                SQLSRV_PARAM_IN), // ID_ESTADO
    array('3-4',            SQLSRV_PARAM_IN)  // ID_ETAPA
);
$stmt_seleccion = sqlsrv_query($conn, $sql_seleccion, $params_seleccion);
if ($stmt_seleccion === false) {
    echo "Error in executing statement seleccion.\n";
    die(print_r(sqlsrv_errors(), true));
}
while ($seleccion = sqlsrv_fetch_array($stmt_seleccion, SQLSRV_FETCH_ASSOC)) {

    $id_documento   = $seleccion["ID_DOCDEUDORES"];
    $id_pareodoc    = $seleccion["ID_PAREO_DOCDEUDORES"];

    $sql_operacion = "{call [_SP_CONCILIACIONES_CANALIZACION_CAMBIA_ESTADO_PD](?, ?, ?)}";
    $params_operacion = array(
        array($id_pareodoc,     SQLSRV_PARAM_IN),
        array('1',              SQLSRV_PARAM_IN),
        array($id_usuario,      SQLSRV_PARAM_IN),
    );
    $stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
    if ($stmt_operacion === false) {
        die(print_r(sqlsrv_errors(), true));
    }

/*
print_r($id_documento);
print_r($id_pareodoc);
print_r($id_asignacion);
print_r($id_usuario);
exit;
*/

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

$sql_desasig = "{call [_SP_CONCILIACIONES_DESASIGNACION_INSERTA](?, ?, ?)}";
$params_desasig = array(
    array($id_asignacion,   SQLSRV_PARAM_IN),
    array($motivo,          SQLSRV_PARAM_IN),
    array($id_usuario,      SQLSRV_PARAM_IN),
);
$stmt_desasig = sqlsrv_query($conn, $sql_desasig, $params_desasig);
if ($stmt_desasig === false) {
    die(print_r(sqlsrv_errors(), true));
}

$estado_asig = 0;
$sql_desasig = "{call [_SP_CONCILIACIONES_ASIGNACION_CAMBIA_ESTADO](?, ?, ?)}";
$params_desasig = array(
    array($id_asignacion,   SQLSRV_PARAM_IN),
    array($estado_asig,     SQLSRV_PARAM_IN),
    array($id_usuario,      SQLSRV_PARAM_IN),
);
$stmt_desasig = sqlsrv_query($conn, $sql_desasig, $params_desasig);
if ($stmt_desasig === false) {
    die(print_r(sqlsrv_errors(), true));
}


header("Location: conciliaciones_lista_pendientes_comprobante.php?op=2");
exit;