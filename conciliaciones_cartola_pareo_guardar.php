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

$id_conciliacion = 0;

// Verificar si hay datos enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que hay datos en el arreglo de selected_details
    $datosSeleccionados = isset($_POST['selected_details']) && is_array($_POST['selected_details']) ? $_POST['selected_details'] : [];

    // Verificar que hay datos en el arreglo de selected_remesas
    $datosRemesasSeleccionadas = isset($_POST['selected_remesas']) && is_array($_POST['selected_remesas']) ? $_POST['selected_remesas'] : [];

    if (!empty($datosSeleccionados)) {

        $query = "{CALL [_SP_CONCILIACIONES_CONCILIACION_OBTENER_ID](?)}";
        $params = array(array(&$id_conciliacion, SQLSRV_PARAM_OUT));
        $stmt = sqlsrv_query($conn, $query, $params);
        if ($stmt === false) {
            mostrarError("Error al obtener el ID de conciliación. -> stmt_obtener_id");
        }
        sqlsrv_free_stmt($stmt);

        // Iterar sobre los datos seleccionados y realizar inserciones
        foreach ($datosSeleccionados as $dato) {
            // Separar los valores del checkbox
            list($cuenta, $fecha, $n_documento, $descripcion, $monto_detalle_int) = explode(',', $dato);

            // Convertir la fecha de dd/mm/yyyy a yyyy-mm-dd
            $fechaConvertida = DateTime::createFromFormat('d/m/Y', $fecha);
            if ($fechaConvertida === false) {
                mostrarError("Error: Formato de fecha inválido en el detalle seleccionado.");
            }
            $fechaSQL = $fechaConvertida->format('Y-m-d');

            // Preparar y ejecutar el procedimiento de inserción
            $queryInsert = "{CALL [_SP_CONCILIACIONES_CONCILIACION_CARTOLA_INSERTAR](?, ?, ?, ?, ?, ?, ?)}";
            $paramsInsert = array(
                array($id_conciliacion,         SQLSRV_PARAM_IN),
                array($cuenta,                  SQLSRV_PARAM_IN),
                array($fechaSQL,                SQLSRV_PARAM_IN),
                array($n_documento,             SQLSRV_PARAM_IN),
                array($descripcion,             SQLSRV_PARAM_IN),
                array((int)$monto_detalle_int,  SQLSRV_PARAM_IN),
                array($idusuario,               SQLSRV_PARAM_IN)
            );
            $stmtInsert = sqlsrv_query($conn, $queryInsert, $paramsInsert);
            if ($stmtInsert === false) {
                mostrarError("Error al insertar datos en la cartola. -> stmt_cartola_insertar");
            }
        }

        foreach ($datosRemesasSeleccionadas as $dato_remesa) {

            // Separar los valores del checkbox
            list($n_remesa, $fecha_remesa, $producto, $monto_remesa_int) = explode(',', $dato_remesa);

            $sql_canalizacion = "EXEC [_SP_CONCILIACIONES_CONCILIAR_REMESAS_CUENTA_CONSULTA] ?";
            $params_canalizacion = array(array($n_remesa, SQLSRV_PARAM_IN));
            $stmt_canalizacion = sqlsrv_query($conn, $sql_canalizacion, $params_canalizacion);

            if ($stmt_canalizacion === false) {
                mostrarError("Error al consultar la cuenta beneficiario para la remesa. -> stmt_canalizacion");
            }
            $canalizacion_row = sqlsrv_fetch_array($stmt_canalizacion, SQLSRV_FETCH_ASSOC);

            if ($canalizacion_row['ID_TIPO_CANALIZACION'] == 2) {
                $tipo_canal = 2;
            }
            if ($canalizacion_row['ID_TIPO_CANALIZACION'] == 3) {
                $tipo_canal = 3;

                $sql_saldo_consulta = "{call [_SP_CONCILIACIONES_SALDO_CAMBIA_ESTADO](?)}";
                $params_saldo_consulta = array(
                    array($n_remesa,   SQLSRV_PARAM_IN),
                );
                $stmt_saldo_consulta = sqlsrv_query($conn, $sql_saldo_consulta, $params_saldo_consulta);
                if ($stmt_saldo_consulta === false) {
                    mostrarError("Error al consultar el saldo asociado. -> stmt_saldo_consulta");
                }
                $saldo_consulta_row = sqlsrv_fetch_array($stmt_saldo_consulta, SQLSRV_FETCH_ASSOC);
                $id_ps = $saldo_consulta_row['ID_PAREO_SISTEMA'];

                $sql_estado = "{call [_SP_CONCILIACIONES_SALDO_CAMBIA_ESTADO](?, ?)}";
                $params_estado = array(
                    array($id_ps,   SQLSRV_PARAM_IN),
                    array(3,        SQLSRV_PARAM_IN)
                );
                $stmt_estado = sqlsrv_query($conn, $sql_estado, $params_estado);
                if ($stmt_estado === false) {
                    mostrarError("Error al cambiar el estado del saldo. -> stmt_estado");
                }
            } else {
                $tipo_canal = 2;
            }

            // Convertir la fecha de dd/mm/yyyy a yyyy-mm-dd
            $fechaRemesaConvertida = DateTime::createFromFormat('d/m/Y', $fecha_remesa);
            if ($fechaRemesaConvertida === false) {
                mostrarError("Error: Formato de fecha inválido en la remesa seleccionada.");
            }
            $fechaRemesaSQL = $fechaRemesaConvertida->format('Y-m-d');

            // Preparar y ejecutar el procedimiento de inserción
            $queryInsert = "{CALL [_SP_CONCILIACIONES_CONCILIACION_RESPALDO_INSERTAR](?, ?, ?, ?, ?, ?, ?, ?)}";
            $paramsInsert = array(
                array($id_conciliacion,         SQLSRV_PARAM_IN),
                array($cuenta,                  SQLSRV_PARAM_IN),
                array($fechaRemesaSQL,          SQLSRV_PARAM_IN),
                array($n_remesa,                SQLSRV_PARAM_IN),
                array($producto,                SQLSRV_PARAM_IN),
                array((int)$monto_remesa_int,   SQLSRV_PARAM_IN),
                array($tipo_canal,              SQLSRV_PARAM_IN),
                array($idusuario,               SQLSRV_PARAM_IN)
            );
            $stmtInsert = sqlsrv_query($conn, $queryInsert, $paramsInsert);
            if ($stmtInsert === false) {
                mostrarError("Error al insertar datos en el respaldo. -> stmt_respaldo_insertar");
            }
        }
    } else {
        // Manejar el caso en que no hay detalles seleccionados
        mostrarError("No se han seleccionado detalles para procesar.");
    }

    header("Location: conciliaciones_cartola_pareo.php?op=0");
    exit;
    
} else {
    // Responder con error si la solicitud no es POST
    mostrarError("Método de solicitud no permitido.");
}
