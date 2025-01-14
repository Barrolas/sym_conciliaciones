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

// Obtener y sanitizar los datos de entrada
$id_doc         = $_GET["id_doc"] ?? null;
$transaccion    = $_GET["transaccion"] ?? null;

if (!$id_doc || !$transaccion) {
    mostrarError("Faltan parámetros requeridos: ID del documento o transacción.");
}

// Validar si la transacción es entre cuentas
$sql_entrecta = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_TRANSACCION_VALIDA](?)}";
$params_entrecta = [
    [$transaccion, SQLSRV_PARAM_IN],
];
$stmt_entrecta = sqlsrv_query($conn, $sql_entrecta, $params_entrecta);
if ($stmt_entrecta === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_entrecta'.");
}
$entrecta = sqlsrv_fetch_array($stmt_entrecta, SQLSRV_FETCH_ASSOC);
$transaccion_entrecta = $entrecta['TRANSACCION'] ?? null;

if ($transaccion_entrecta && $transaccion_entrecta !== 0) {
    // Si es entre cuentas, actualiza la transacción con el valor original
    $transaccion = $entrecta['TRANSACCION'];
}

// Identificar operaciones asociadas
$sql_seleccion = "EXEC [_SP_CONCILIACIONES_OPERACIONES_ASOCIADAS_IDENTIFICAR] ?, ?, ?, ?";
$params_seleccion = [
    [$id_doc, SQLSRV_PARAM_IN],
    [$transaccion, SQLSRV_PARAM_IN],
    [1, SQLSRV_PARAM_IN],
    ['2-3', SQLSRV_PARAM_IN],
];
$stmt_seleccion = sqlsrv_query($conn, $sql_seleccion, $params_seleccion);
if ($stmt_seleccion === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_seleccion'.");
}

// Procesar las operaciones asociadas
while ($seleccion = sqlsrv_fetch_array($stmt_seleccion, SQLSRV_FETCH_ASSOC)) {
    $id_documento = $seleccion["ID_DOCDEUDORES"];
    $id_pareodoc  = $seleccion["ID_PAREO_DOCDEUDORES"];

    // Eliminar operación de canalización
    $sql_eliminar_op = "EXEC [_SP_CONCILIACIONES_OPERACION_CANALIZACION_ELIMINA] ?, ?, ?";
    $params_eliminar_op = [
        [$id_documento, SQLSRV_PARAM_IN],
        [$id_pareodoc, SQLSRV_PARAM_IN],
        [$idusuario, SQLSRV_PARAM_IN],
    ];
    $stmt_eliminar_op = sqlsrv_prepare($conn, $sql_eliminar_op, $params_eliminar_op);
    if ($stmt_eliminar_op === false || !sqlsrv_execute($stmt_eliminar_op)) {
        mostrarError("Error al ejecutar la consulta 'stmt_eliminar_op'.");
    }

    // Eliminar datos de la canalización
    $sql_eliminar_cn = "{call [_SP_CONCILIACIONES_CANALIZACION_ELIMINAR](?, ?, ?, ?, ?)}";
    $params_eliminar_cn = [
        [$id_documento, SQLSRV_PARAM_IN],
        [$rut_cl, SQLSRV_PARAM_IN],
        [$rut_dd, SQLSRV_PARAM_IN],
        [$f_venc, SQLSRV_PARAM_IN],
        [$ndoc, SQLSRV_PARAM_IN],
    ];
    $stmt_eliminar_cn = sqlsrv_prepare($conn, $sql_eliminar_cn, $params_eliminar_cn);
    if ($stmt_eliminar_cn === false || !sqlsrv_execute($stmt_eliminar_cn)) {
        mostrarError("Error al ejecutar la consulta 'stmt_eliminar_cn'.");
    }
}

// Redirigir a la página de lista de conciliaciones
header("Location: conciliaciones_lista_canalizados.php?op=2");
exit;
