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
$id_asignacion  = intval($_GET["idasig"] ?? 0);
$id_doc         = intval($_GET["iddoc"] ?? 0);
$transaccion    = htmlspecialchars($_GET["transaccion"] ?? '', ENT_QUOTES, 'UTF-8');
$motivo         = htmlspecialchars($_GET["motivo"]      ?? '', ENT_QUOTES, 'UTF-8');

$estado1 = 1;
$estado2 = 1;

// Consulta de asignados
$sql_asign = "EXEC [_SP_CONCILIACIONES_ASIGNADOS_CONSULTA] ?, ?, ?";
$params_asign = [
    [$id_asignacion, SQLSRV_PARAM_IN],
    [$estado1,       SQLSRV_PARAM_IN],
    [$estado2,       SQLSRV_PARAM_IN],
];
$stmt_asign = sqlsrv_query($conn, $sql_asign, $params_asign);
if ($stmt_asign === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_asign'.");
}
$asignados = sqlsrv_fetch_array($stmt_asign, SQLSRV_FETCH_ASSOC);

$id_pareo_sis = $asignados['ID_PAREO_SISTEMA'] ?? null;

// Validar transacción entre cuentas
$sql_entrecta = "{call [_SP_CONCILIACIONES_ENTRECUENTAS_TRANSACCION_VALIDA](?)}";
$params_entrecta = [
    [$transaccion, SQLSRV_PARAM_IN],
];
$stmt_entrecta = sqlsrv_query($conn, $sql_entrecta, $params_entrecta);
if ($stmt_entrecta === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_entrecta'.");
}
$entrecta = sqlsrv_fetch_array($stmt_entrecta, SQLSRV_FETCH_ASSOC);
$transaccion_entrecta = $entrecta['TRANSACCION'] ?? 0;

if ($transaccion_entrecta !== 0) {
    $id_pareo_sis = $entrecta['ID_PAREO_SISTEMA'];
}

// Consulta de operaciones
$sql_operacion = "{call [_SP_CONCILIACIONES_OPERACION_CONSULTA](?)}";
$params_operacion = [
    [$id_pareo_sis, SQLSRV_PARAM_IN],
];
$stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
if ($stmt_operacion === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_operacion'.");
}
$operaciones = sqlsrv_fetch_array($stmt_operacion, SQLSRV_FETCH_ASSOC);
$transaccion = $operaciones['TRANSACCION'] ?? null;

// Identificar operaciones asociadas
$sql_seleccion = "EXEC [_SP_CONCILIACIONES_OPERACIONES_ASOCIADAS_IDENTIFICAR] ?, ?, ?, ?";
$params_seleccion = [
    [$id_doc,          SQLSRV_PARAM_IN],
    [$transaccion,     SQLSRV_PARAM_IN],
    [1,                SQLSRV_PARAM_IN], // ID_ESTADO
    ['3-4',            SQLSRV_PARAM_IN]  // ID_ETAPA
];
$stmt_seleccion = sqlsrv_query($conn, $sql_seleccion, $params_seleccion);
if ($stmt_seleccion === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_seleccion'.");
}
while ($seleccion = sqlsrv_fetch_array($stmt_seleccion, SQLSRV_FETCH_ASSOC)) {
    $id_documento = $seleccion["ID_DOCDEUDORES"];
    $id_pareodoc = $seleccion["ID_PAREO_DOCDEUDORES"];

    // Cambiar estado de la canalización
    $sql_operacion = "{call [_SP_CONCILIACIONES_CANALIZACION_CAMBIA_ESTADO_PD](?, ?, ?)}";
    $params_operacion = [
        [$id_pareodoc, SQLSRV_PARAM_IN],
        [1,            SQLSRV_PARAM_IN],
        [$idusuario,   SQLSRV_PARAM_IN],
    ];
    $stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
    if ($stmt_operacion === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_operacion'.");
    }

    // Eliminar asignación de operación
    $sql_opeliminar = "EXEC [_SP_CONCILIACIONES_OPERACION_ASIGNACION_ELIMINA] ?, ?, ?, ?";
    $params_opeliminar = [
        [$id_documento,     SQLSRV_PARAM_IN],
        [$id_pareodoc,      SQLSRV_PARAM_IN],
        [$id_asignacion,    SQLSRV_PARAM_IN],
        [$idusuario,        SQLSRV_PARAM_IN],
    ];
    $stmt_opeliminar = sqlsrv_prepare($conn, $sql_opeliminar, $params_opeliminar);
    if ($stmt_opeliminar === false) {
        mostrarError("Error al preparar la consulta 'stmt_opeliminar'.");
    }
    if (!sqlsrv_execute($stmt_opeliminar)) {
        mostrarError("Error al ejecutar la consulta 'stmt_opeliminar'.");
    }
}

// Insertar desasignación
$sql_desasig = "{call [_SP_CONCILIACIONES_DESASIGNACION_INSERTA](?, ?, ?)}";
$params_desasig = [
    [$id_asignacion, SQLSRV_PARAM_IN],
    [$motivo,        SQLSRV_PARAM_IN],
    [$idusuario,     SQLSRV_PARAM_IN],
];
$stmt_desasig = sqlsrv_query($conn, $sql_desasig, $params_desasig);
if ($stmt_desasig === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_desasig'.");
}

// Cambiar estado de asignación
$estado_asig = 0;
$sql_cambia_estado = "{call [_SP_CONCILIACIONES_ASIGNACION_CAMBIA_ESTADO](?, ?, ?)}";
$params_cambia_estado = [
    [$id_asignacion, SQLSRV_PARAM_IN],
    [$estado_asig,   SQLSRV_PARAM_IN],
    [$idusuario,     SQLSRV_PARAM_IN],
];
$stmt_cambia_estado = sqlsrv_query($conn, $sql_cambia_estado, $params_cambia_estado);
if ($stmt_cambia_estado === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_cambia_estado'.");
}

// Redirigir al finalizar
header("Location: conciliaciones_lista_pendientes_comprobante.php?op=2");
exit;
