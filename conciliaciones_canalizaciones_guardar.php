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

// Obtener parámetros
$op                     = isset($_GET["op"]) ? $_GET["op"]          : 0;
$selected_ids_docs      = $_POST['selected_ids_docs'] ?? [];
$selected_ids_pareodoc  = $_POST['selected_ids_pareodoc'] ?? [];
$selected_types         = $_POST['selected_types'] ?? [];

// Verificar que $selected_ids_docs es un array con un solo elemento que es una cadena de valores separados por comas
$selected_ids_docs      = is_array($selected_ids_docs) && count($selected_ids_docs) > 0 ? explode(',', $selected_ids_docs[0]) : [];
$selected_ids_pareodoc  = is_array($selected_ids_pareodoc) && count($selected_ids_pareodoc) > 0 ? explode(',', $selected_ids_pareodoc[0]) : [];
$selected_types         = is_array($selected_types) && count($selected_types) > 0 ? explode(',', $selected_types[0]) : [];

// Verificar que todos los arreglos tengan la misma longitud
if (count($selected_ids_docs) !== count($selected_ids_pareodoc) || count($selected_ids_docs) !== count($selected_types)) {
    mostrarError("Error: Los arreglos de datos no tienen la misma longitud.");
}

// Inicialización de variables
$id_canalizacion = 0;
$cantidad_docs   = 0;

// Insertar canalización
$sql_canal = "{call [_SP_CONCILIACIONES_CANALIZACION_INSERTA] (?, ?)}";
$params_canal = [
    [(int)$idusuario,   SQLSRV_PARAM_IN],
    [&$id_canalizacion, SQLSRV_PARAM_OUT],
];
$stmt_canal = sqlsrv_query($conn, $sql_canal, $params_canal);
if ($stmt_canal === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_canal'.");
}

// Procesar documentos
foreach ($selected_ids_docs as $index => $id_docdeudores) {
    $id_pareo_doc = $selected_ids_pareodoc[$index] ?? null;
    $type = $selected_types[$index] ?? null;

    // Consulta de pareo de documentos
    $sql_docdeudores = "{call [_SP_CONCILIACIONES_PAREO_DOCDEUDORES_CONSULTA] (?)}";
    $params_docdeudores = [
        [(int)$id_pareo_doc, SQLSRV_PARAM_IN],
    ];
    $stmt_docdeudores = sqlsrv_query($conn, $sql_docdeudores, $params_docdeudores);
    if ($stmt_docdeudores === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_docdeudores' en el índice $index.");
    }
    $docdeudores = sqlsrv_fetch_array($stmt_docdeudores, SQLSRV_FETCH_ASSOC);

    $id_doc             = $docdeudores['ID_DOCDEUDORES'];
    $monto_diferencia   = 0;
    $estado_canal       = 0;

    // Consulta de diferencias
    $sql_dif = "{call [_SP_CONCILIACIONES_DIFERENCIAS_CONSULTA] (?)}";
    $params_dif = [
        [(int)$id_doc, SQLSRV_PARAM_IN],
    ];
    $stmt_dif = sqlsrv_query($conn, $sql_dif, $params_dif);
    if ($stmt_dif === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_dif' en el índice $index.");
    }
    $diferencia = sqlsrv_fetch_array($stmt_dif, SQLSRV_FETCH_ASSOC);
    $monto_diferencia = $diferencia['MONTO_DIFERENCIA'] ?? 0;

    $estado_canal = $monto_diferencia > 0 ? 4 : 1;

    // Insertar documento en la canalización
    $sql_doc_inserta = "{call [_SP_CONCILIACIONES_CANALIZACION_DOCUMENTO_INSERTA] (?, ?, ?, ?, ?)}";
    $params_doc_inserta = [
        [(int)$id_canalizacion, SQLSRV_PARAM_IN],
        [(int)$type,            SQLSRV_PARAM_IN],
        [(int)$id_pareo_doc,    SQLSRV_PARAM_IN],
        [(int)$estado_canal,    SQLSRV_PARAM_IN],
        [(int)$idusuario,       SQLSRV_PARAM_IN],
    ];
    $stmt_doc_inserta = sqlsrv_query($conn, $sql_doc_inserta, $params_doc_inserta);
    if ($stmt_doc_inserta === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_doc_inserta' en el índice $index.");
    }

    // Actualizar canalización de operaciones
    $sql_operacion = "{call [_SP_CONCILIACIONES_OPERACION_CANALIZACION_INSERTA] (?, ?)}";
    $params_operacion = [
        [$id_docdeudores,   SQLSRV_PARAM_IN],
        [$idusuario,        SQLSRV_PARAM_IN],
    ];
    $stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
    if ($stmt_operacion === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_operacion' en el índice $index.");
    }

    $cantidad_docs++;
}

// Actualizar canalización
$sql_actualiza = "{call [_SP_CONCILIACIONES_CANALIZACION_ACTUALIZA] (?, ?, ?)}";
$params_actualiza = [
    [(int)$id_canalizacion, SQLSRV_PARAM_IN],
    [(int)$cantidad_docs,   SQLSRV_PARAM_IN],
    [(int)$idusuario,       SQLSRV_PARAM_IN],
];
$stmt_actualiza = sqlsrv_query($conn, $sql_actualiza, $params_actualiza);
if ($stmt_actualiza === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_actualiza'.");
}

// Redirigir al finalizar
header("Location: conciliaciones_lista_pareados.php?op=1");
exit;
