<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener parámetros
$op = isset($_GET["op"]) ? $_GET["op"] : 0;
$selected_ids_docs = isset($_POST['selected_ids_docs']) ? $_POST['selected_ids_docs'] : [];
$selected_ids_pareosistema = isset($_POST['selected_ids_pareosistema']) ? $_POST['selected_ids_pareosistema'] : [];
$selected_types = isset($_POST['selected_types']) ? $_POST['selected_types'] : [];

// Depuración: imprimir datos recibidos
echo "<h3>Datos recibidos:</h3>";
echo "<pre>";
print_r($selected_ids_docs);
print_r($selected_ids_pareosistema);
print_r($selected_types);
echo "</pre>";

// Verificar que $selected_ids_docs es un array con un solo elemento que es una cadena de valores separados por comas
if (is_array($selected_ids_docs) && count($selected_ids_docs) > 0) {
    $selected_ids_docs = explode(',', $selected_ids_docs[0]);
} else {
    $selected_ids_docs = [];
}

// Verificar que $selected_ids_pareosistema es un array con un solo elemento que es una cadena de valores separados por comas
if (is_array($selected_ids_pareosistema) && count($selected_ids_pareosistema) > 0) {
    $selected_ids_pareosistema = explode(',', $selected_ids_pareosistema[0]);
} else {
    $selected_ids_pareosistema = [];
}

// Verificar que $selected_types es un array con un solo elemento que es una cadena de valores separados por comas
if (is_array($selected_types) && count($selected_types) > 0) {
    $selected_types = explode(',', $selected_types[0]);
} else {
    $selected_types = [];
}

// Verificar que todos los arreglos tengan la misma longitud
if (count($selected_ids_docs) !== count($selected_ids_pareosistema) || count($selected_ids_docs) !== count($selected_types)) {
    die('Error: Los arreglos no tienen la misma longitud.');
}

// Inicialización de variables
$id_usuario = 1;
$id_canalizacion = 0;
$cantidad_docs = 0;
$cantidad_cheques = 0;
$cantidad_transferencias = 0;

// Consultar e insertar en la base de datos
$sql1 = "{call [_SP_CONCILIACIONES_CANALIZACION_INSERTA] (?, ?)}";
$params1 = array(
    array((int)$id_usuario, SQLSRV_PARAM_IN),
    array(&$id_canalizacion, SQLSRV_PARAM_OUT),
);

$stmt1 = sqlsrv_query($conn, $sql1, $params1);
if ($stmt1 === false) {
    echo "Error en la ejecución de la declaración 1.\n";
    die(print_r(sqlsrv_errors(), true));
}

// Depuración: imprimir id_canalizacion
echo "<h3>ID Canalización:</h3>";
echo "<pre>";
print_r($id_canalizacion);
echo "</pre>";

// Insertar documentos
echo "<h3>Procesando documentos:</h3>";
foreach ($selected_ids_docs as $index => $id_docdeudores) {
    // Asegurar que los índices existen en los otros arreglos
    $id_pareo_sistema = isset($selected_ids_pareosistema[$index]) ? $selected_ids_pareosistema[$index] : null;
    $type = isset($selected_types[$index]) ? $selected_types[$index] : null;

    // Depuración: imprimir valores actuales
    echo "Processing: Index = $index, ID DocDeudores = $id_docdeudores, ID Pareo Sistema = $id_pareo_sistema, Type = $type<br>";

    $sql2 = "{call [_SP_CONCILIACIONES_CANALIZACION_DOCUMENTO_INSERTA] (?, ?, ?, ?, ?)}";
    $params2 = array(
        array((int)$id_canalizacion,    SQLSRV_PARAM_IN),
        array((int)$type,               SQLSRV_PARAM_IN),
        array((int)$id_docdeudores,     SQLSRV_PARAM_IN),
        array((int)$id_pareo_sistema,   SQLSRV_PARAM_IN),
        array((int)$id_usuario,         SQLSRV_PARAM_IN)
    );

    $stmt2 = sqlsrv_query($conn, $sql2, $params2);
    if ($stmt2 === false) {
        echo "Error en la ejecución de la declaración 2 en el índice $index.\n";
        die(print_r(sqlsrv_errors(), true));
    }
    $cantidad_docs++;

    // Depuración: verificar éxito de la inserción
    echo "Successfully inserted document at index $index.<br>";
}

// Actualizar canalización
$sql3 = "{call [_SP_CONCILIACIONES_CANALIZACION_ACTUALIZA] (?, ?, ?)}";
$params3 = array(
    array((int)$id_canalizacion, SQLSRV_PARAM_IN),
    array((int)$cantidad_docs, SQLSRV_PARAM_IN),
    array((int)$id_usuario, SQLSRV_PARAM_IN)
);

$stmt3 = sqlsrv_query($conn, $sql3, $params3);
if ($stmt3 === false) {
    echo "Error en la ejecución de la declaración 3.\n";
    die(print_r(sqlsrv_errors(), true));
}

// Depuración: mostrar cantidad_docs
echo "<h3>Cantidad de documentos:</h3>";
echo "<pre>";
print_r($cantidad_docs);
echo "</pre>";

//exit;

// Redirigir a otra página
header("Location: conciliaciones_lista_pareados.php?op=1");
exit;

?>
