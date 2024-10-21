<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$response = [];

// Verificar que el usuario esté autenticado
if (isset($_SESSION['ID_USUARIO'])) {
    $idUsuario = $_SESSION['ID_USUARIO'];

    // Preparar la consulta SQL para obtener las preferencias del usuario
    $sql = "SELECT ETIQUETAS_SELECCIONADAS, ETIQUETAS_FILTRO_SELECCIONADAS, EXCLUIR_ESTADO FROM conciliaciones_usuarios_preferencias WHERE ID_USUARIO = ?";
    $stmt = sqlsrv_prepare($conn, $sql, array(&$idUsuario));

    if (!$stmt) {
        $response['error'] = 'Error en la consulta: ' . print_r(sqlsrv_errors(), true);
        echo json_encode($response);
        exit;
    }

    if (sqlsrv_execute($stmt)) {
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Procesar los resultados
            $etiquetasSeleccionadas = $row['ETIQUETAS_SELECCIONADAS'];
            $etiquetasFiltroSeleccionadas = $row['ETIQUETAS_FILTRO_SELECCIONADAS'];
            $excluirEstado = (int)$row['EXCLUIR_ESTADO']; // Convertir a entero para mantener el formato esperado

            // Verificar y convertir las etiquetas seleccionadas
            if (!empty($etiquetasSeleccionadas)) {
                $decodedEtiquetasSeleccionadas = json_decode($etiquetasSeleccionadas, true);
                $response['etiquetas_seleccionadas'] = is_array($decodedEtiquetasSeleccionadas) ? $decodedEtiquetasSeleccionadas : [];
            } else {
                $response['etiquetas_seleccionadas'] = [];
            }

            // Verificar y convertir las etiquetas de filtro seleccionadas
            if (!empty($etiquetasFiltroSeleccionadas)) {
                $decodedEtiquetasFiltroSeleccionadas = json_decode($etiquetasFiltroSeleccionadas, true);
                $response['etiquetas_filtro_seleccionadas'] = is_array($decodedEtiquetasFiltroSeleccionadas) ? $decodedEtiquetasFiltroSeleccionadas : [];
            } else {
                $response['etiquetas_filtro_seleccionadas'] = [];
            }

            $response['excluir_estado'] = $excluirEstado; // Ya está convertido a entero
        } else {
            $response['error'] = 'No se encontraron preferencias para el usuario.';
        }
    } else {
        $response['error'] = 'Error al ejecutar la consulta: ' . print_r(sqlsrv_errors(), true);
    }
} else {
    $response['error'] = 'Usuario no autenticado.';
}

echo json_encode($response);
