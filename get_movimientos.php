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
// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar parámetros enviados por AJAX
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    // Validar que las fechas estén presentes
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        echo json_encode(['error' => 'Faltan parámetros de fecha']);
        exit;
    }

    // Definir el procedimiento almacenado y sus parámetros
    $sql = "{call [_SP_CONCILIACIONES_MOVIMIENTOS_LISTA](?, ?, ?)}";
    $params = array(1, $fecha_inicio, $fecha_fin);
    $stmt = sqlsrv_query($conn, $sql, $params);

    // Manejar errores en la ejecución del SP
    if ($stmt === false) {
        echo json_encode(['error' => sqlsrv_errors()]);
        exit;
    }

    // Preparar los datos para enviarlos a DataTables
    $result = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Formatear datos según sea necesario (por ejemplo, fechas y números)
        $result[] = [
            "ID_PS" => $row["ID_PS"],
            "TICKET" => $row["TICKET"],
            "MOVIMIENTO" => $row["MOVIMIENTO"],
            "TRANSACCION" => $row["TRANSACCION"],
            "CTA_BENEF" => $row["CTA_BENEF"],
            "F_RECEPCION" => $row["F_RECEPCION"]->format('Y-m-d'),
            "F_VENC" => $row["F_VENC"]->format('Y-m-d'),
            "N_DOC" => $row["N_DOC"],
            "MONTO_DOC" => number_format($row["MONTO_DOC"], 0, ',', '.'),
            "HABER" => number_format($row["HABER"], 0, ',', '.'),
            "DEBE" => number_format($row["DEBE"], 0, ',', '.')
        ];
    }

    // Devolver los datos en formato JSON
    echo json_encode($result);
}
