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
$op                     = isset($_GET["op"]) ? $_GET["op"]          : 0;
$selected_ids_docs      = isset($_POST['selected_ids_docs'])        ? $_POST['selected_ids_docs'] : [];
$selected_ids_pareodoc  = isset($_POST['selected_ids_pareodoc'])    ? $_POST['selected_ids_pareodoc'] : [];
$selected_types         = isset($_POST['selected_types'])           ? $_POST['selected_types'] : [];

// Depuración: imprimir datos recibidos
echo "<h3>Datos recibidos:</h3>";
echo "<pre>";
print_r($selected_ids_docs);
print_r($selected_ids_pareodoc);
print_r($selected_types);
echo "</pre>";

// Verificar que $selected_ids_docs es un array con un solo elemento que es una cadena de valores separados por comas
if (is_array($selected_ids_docs) && count($selected_ids_docs) > 0) {
    $selected_ids_docs = explode(',', $selected_ids_docs[0]);
} else {
    $selected_ids_docs = [];
}

// Verificar que $selected_ids_pareodoc es un array con un solo elemento que es una cadena de valores separados por comas
if (is_array($selected_ids_pareodoc) && count($selected_ids_pareodoc) > 0) {
    $selected_ids_pareodoc = explode(',', $selected_ids_pareodoc[0]);
} else {
    $selected_ids_pareodoc = [];
}

// Verificar que $selected_types es un array con un solo elemento que es una cadena de valores separados por comas
if (is_array($selected_types) && count($selected_types) > 0) {
    $selected_types = explode(',', $selected_types[0]);
} else {
    $selected_types = [];
}

// Verificar que todos los arreglos tengan la misma longitud
if (count($selected_ids_docs) !== count($selected_ids_pareodoc) || count($selected_ids_docs) !== count($selected_types)) {
    die('Error: Los arreglos no tienen la misma longitud.');
}

// Inicialización de variables
$id_usuario = $_SESSION['ID_USUARIO'];;
$id_canalizacion = 0;
$cantidad_docs = 0;
$cantidad_cheques = 0;
$cantidad_transferencias = 0;

// Consultar e insertar en la base de datos
$sql1 = "{call [_SP_CONCILIACIONES_CANALIZACION_INSERTA] (?, ?)}";
$params1 = array(
    array((int)$id_usuario,     SQLSRV_PARAM_IN),
    array(&$id_canalizacion,    SQLSRV_PARAM_OUT),
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
    $id_pareo_doc = isset($selected_ids_pareodoc[$index]) ? $selected_ids_pareodoc[$index] : null;
    $type = isset($selected_types[$index]) ? $selected_types[$index] : null;

    // Depuración: imprimir valores actuales
    echo "Processing: Index = $index, ID DocDeudores = $id_docdeudores, ID Pareo Doc = $id_pareo_doc, Type = $type<br>";

    $sql_docdeudores = "{call [_SP_CONCILIACIONES_PAREO_DOCDEUDORES_CONSULTA] (?)}";
    $params_docdeudores = array(
        array((int)$id_pareo_doc,       SQLSRV_PARAM_IN),
    );
    $stmt_docdeudores = sqlsrv_query($conn, $sql_docdeudores, $params_docdeudores);
    if ($stmt_docdeudores === false) {
        echo "Error en la ejecución de la declaración _docdeudores en el índice $index.\n";
        die(print_r(sqlsrv_errors(), true));
    }
    $docdeudores = sqlsrv_fetch_array($stmt_docdeudores, SQLSRV_FETCH_ASSOC);

    $id_doc             = $docdeudores['ID_DOCDEUDORES'];
    $monto_diferencia   = 0;
    $estado_canal       = 0;

    $sql_dif = "{call [_SP_CONCILIACIONES_DIFERENCIAS_CONSULTA] (?)}";
    $params_dif = array(
        array((int)$id_doc,             SQLSRV_PARAM_IN),
    );
    $stmt_dif = sqlsrv_query($conn, $sql_dif, $params_dif);
    if ($stmt_dif === false) {
        echo "Error en la ejecución de la declaración _dif en el índice $index.\n";
        die(print_r(sqlsrv_errors(), true));
    }
    $diferencia = sqlsrv_fetch_array($stmt_dif, SQLSRV_FETCH_ASSOC);

    $monto_diferencia = $diferencia['MONTO_DIFERENCIA'] ?? 0;

    if ($monto_diferencia > 0) {
        $estado_canal = 4;
    } elseif ($monto_diferencia == 0) {
        $estado_canal = 1;
    }

    print_r("Monto diferencia: " . $monto_diferencia);
    print_r("Estado: " . $estado_canal);
    //exit;

    $sql2 = "{call [_SP_CONCILIACIONES_CANALIZACION_DOCUMENTO_INSERTA] (?, ?, ?, ?, ?)}";
    $params2 = array(
        array((int)$id_canalizacion,    SQLSRV_PARAM_IN),
        array((int)$type,               SQLSRV_PARAM_IN),
        array((int)$id_pareo_doc,       SQLSRV_PARAM_IN),
        array((int)$estado_canal,       SQLSRV_PARAM_IN),
        array((int)$id_usuario,         SQLSRV_PARAM_IN)
    );

    $stmt2 = sqlsrv_query($conn, $sql2, $params2);
    if ($stmt2 === false) {
        echo "Error en la ejecución de la declaración 2 en el índice $index.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    // Actualizar canalización
    $sql_operacion = "{call [_SP_CONCILIACIONES_OPERACION_CANALIZACION_INSERTA] (?, ?)}";
    $params_operacion = array(
        array($id_docdeudores,     SQLSRV_PARAM_IN),
        array($id_usuario,         SQLSRV_PARAM_IN)
    );
    $stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
    if ($stmt_operacion === false) {
        echo "Error en la ejecución de la declaración _operacion.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    $cantidad_docs++;

    // Depuración: verificar éxito de la inserción
    echo "Successfully inserted document at index $index.<br>";
}

// Actualizar canalización
$sql3 = "{call [_SP_CONCILIACIONES_CANALIZACION_ACTUALIZA] (?, ?, ?)}";
$params3 = array(
    array((int)$id_canalizacion,    SQLSRV_PARAM_IN),
    array((int)$cantidad_docs,      SQLSRV_PARAM_IN),
    array((int)$id_usuario,         SQLSRV_PARAM_IN)
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

header("Location: conciliaciones_lista_pareados.php?op=1");
exit;
