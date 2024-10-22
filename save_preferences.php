<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = [];

// Verificar si el usuario está autenticado
if (isset($_SESSION['ID_USUARIO'])) {
    $idUsuario = $_SESSION['ID_USUARIO'];

    // Obtener los datos enviados a través de POST
    $data = json_decode(file_get_contents("php://input"), true);

    // Imprimir los datos para depuración
    error_log("Datos recibidos: " . print_r($data, true));

    // Convertir etiquetas seleccionadas a texto plano (antes era tag_states)
    $etiquetasSeleccionadas = isset($data['etiquetas_seleccionadas']) ? $data['etiquetas_seleccionadas'] : '';
    $etiquetasFiltroSeleccionadas = isset($data['etiquetas_filtro_seleccionadas']) ? $data['etiquetas_filtro_seleccionadas'] : '';
    $excluirEstado = isset($data['excluir_estado']) ? (int)$data['excluir_estado'] : 0;

    // Imprimir las etiquetas para depuración
    error_log("Etiquetas Seleccionadas (antes tag_states): " . $etiquetasSeleccionadas);
    error_log("Etiquetas Filtro Seleccionadas: " . $etiquetasFiltroSeleccionadas);

    // Preparar la consulta SQL para el procedimiento almacenado
    $sql = "{CALL _SP_CONCILIACIONES_USUARIOS_PREFERENCIAS_GUARDAR(?, ?, ?, ?)}";

    // Preparar los parámetros para la consulta
    $params = [
        $idUsuario,
        $etiquetasSeleccionadas, // Ahora etiquetas_seleccionadas contiene lo que antes era tag_states
        $etiquetasFiltroSeleccionadas,
        $excluirEstado
    ];

    // Preparar la consulta
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if (!$stmt) {
        $response['error'] = 'Error en la consulta: ' . json_encode(sqlsrv_errors());
        echo json_encode($response);
        exit;
    }

    // Ejecutar la consulta
    if (sqlsrv_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Preferencias guardadas correctamente.';
        $response['data'] = [
            'idUsuario' => $idUsuario,
            'etiquetasSeleccionadas' => $etiquetasSeleccionadas,
            'etiquetasFiltroSeleccionadas' => $etiquetasFiltroSeleccionadas,
            'excluirEstado' => $excluirEstado
        ];
    } else {
        $response['error'] = 'Error al guardar preferencias: ' . json_encode(sqlsrv_errors());
    }
} else {
    $response['error'] = 'Usuario no autenticado.';
}

// Devolver la respuesta JSON
echo json_encode($response);
exit;
