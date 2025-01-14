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

header('Content-Type: application/json');

$response = [];

// Verificar que el usuario esté autenticado
if (isset($_SESSION['ID_USUARIO'])) {
    $idUsuario = $_SESSION['ID_USUARIO'];

    // Preparar la consulta SQL para limpiar las preferencias del usuario
    $sql = "UPDATE conciliaciones_usuarios_preferencias SET ETIQUETAS_SELECCIONADAS = NULL, ETIQUETAS_FILTRO_SELECCIONADAS = NULL, EXCLUIR_ESTADO = 0 WHERE ID_USUARIO = ?";
    
    $stmt = sqlsrv_prepare($conn, $sql, array(&$idUsuario));

    if (!$stmt) {
        $response['error'] = 'Error en la preparación de la consulta: ' . print_r(sqlsrv_errors(), true);
    } else {
        if (sqlsrv_execute($stmt)) {
            $response['message'] = 'Preferencias limpiadas correctamente.';
        } else {
            $response['error'] = 'Error al ejecutar la consulta: ' . print_r(sqlsrv_errors(), true);
        }
    }
} else {
    $response['error'] = 'Usuario no autenticado.';
}

// Enviar la respuesta en formato JSON
echo json_encode($response);
?>
