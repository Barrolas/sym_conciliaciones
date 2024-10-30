<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si se recibieron las variables necesarias
if (isset($_POST['fecha']) && isset($_POST['cuenta'])) {

    $fecha_cartola  = $_POST['fecha'];
    $cuenta_cartola = $_POST['cuenta'];

    // Consulta a la base de datos
    $sql_remesas = "EXEC [_SP_CONCILIACIONES_CONCILIAR_REMESAS_FECHA_CONSULTA] ?";
    $params_remesas = array(array($fecha_cartola, SQLSRV_PARAM_IN));
    $stmt_remesas = sqlsrv_query($conn, $sql_remesas, $params_remesas);
    
    if ($stmt_remesas === false) {
        $error = sqlsrv_errors();
        error_log("Error en la consulta de remesas: " . print_r($error, true));
        die(json_encode(["error" => "Error en la consulta de remesas."]));
    }

    $remesas = [];

    while ($remesas_row = sqlsrv_fetch_array($stmt_remesas, SQLSRV_FETCH_ASSOC)) {
        $n_remesa = $remesas_row['N_REMESA'] ?? null; // Manejar si N_REMESA es null

        // Obtener detalles de cada remesa
        $sql_remesas_det = "EXEC [_SP_CONCILIACIONES_CONCILIAR_REMESAS_CONSULTA] ?";
        $params_remesas_det = array(array($n_remesa, SQLSRV_PARAM_IN));
        $stmt_remesas_det = sqlsrv_query($conn, $sql_remesas_det, $params_remesas_det);

        if ($stmt_remesas_det === false) {
            $error = sqlsrv_errors();
            error_log("Error en la consulta de detalles de remesas: " . print_r($error, true));
            die(json_encode(["error" => "Error en la consulta de detalles de remesas."]));
        }

        $remesas_det_row = sqlsrv_fetch_array($stmt_remesas_det, SQLSRV_FETCH_ASSOC);
        if ($remesas_det_row === null) {
            // Manejar si no hay detalles de remesa
            continue;
        }

        // Obtener cuenta beneficiario
        $sql_remesas_cta = "EXEC [_SP_CONCILIACIONES_CONCILIAR_REMESAS_CUENTA_CONSULTA] ?";
        $params_remesas_cta = array(array($n_remesa, SQLSRV_PARAM_IN));
        $stmt_remesas_cta = sqlsrv_query($conn, $sql_remesas_cta, $params_remesas_cta);

        if ($stmt_remesas_cta === false) {
            $error = sqlsrv_errors();
            error_log("Error en la consulta de cuenta beneficiario: " . print_r($error, true));
            die(json_encode(["error" => "Error en la consulta de cuenta beneficiario."]));
        }

        $remesas_cta_row = sqlsrv_fetch_array($stmt_remesas_cta, SQLSRV_FETCH_ASSOC);

        // Extraer valores, asegurando que no sean null
        $fecha_remesa   = $remesas_det_row['FECHA_REMESA'] ?? '';
        $cant_tr        = $remesas_det_row['CANT_TRANSACCIONES'] ?? 0; // Manejar si CANT_TRANSACCIONES es null
        $producto       = $remesas_det_row['PRODUCTO'] ?? '';
        $monto_remesa   = $remesas_det_row['MONTO_REMESA'] ?? 0; // Manejar si MONTO_REMESA es null
        $cuenta_remesa  = $remesas_cta_row['CUENTA_BENEFICIARIO'] ?? '';

        $remesas[] = [
            'fecha'     => $fecha_remesa,
            'n_remesa'  => $n_remesa,
            'cant_tr'   => $cant_tr,
            'producto'  => $producto,
            'monto'     => number_format($monto_remesa, 0, ',', '.')
        ];
    }

    // Devolver los datos como JSON
    header('Content-Type: application/json');
    echo json_encode($remesas);
} else {
    echo json_encode(["error" => "Datos insuficientes."]);
}
?>
