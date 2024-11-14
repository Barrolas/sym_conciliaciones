<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener el ID del usuario de la sesión
$idusuario = $_SESSION['ID_USUARIO'];
$id_conciliacion = 0;

// Verificar si hay datos enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que hay datos en el arreglo de selected_details
    $datosSeleccionados = isset($_POST['selected_details']) && is_array($_POST['selected_details']) ? $_POST['selected_details'] : [];
    
    // Verificar que hay datos en el arreglo de selected_remesas
    $datosRemesasSeleccionadas = isset($_POST['selected_remesas']) && is_array($_POST['selected_remesas']) ? $_POST['selected_remesas'] : [];

    // Puedes imprimir o registrar los datos de remesas si es necesario para comprobar que llegan
     /*print_r($datosSeleccionados); 
     print_r('; ');
     print_r($datosRemesasSeleccionadas);
     print_r('; '); 
     exit;
*/

    if (!empty($datosSeleccionados)) {
        $query = "{CALL [_SP_CONCILIACIONES_CONCILIACION_OBTENER_ID](?)}";
        $params = array(array(&$id_conciliacion, SQLSRV_PARAM_OUT));
        $stmt = sqlsrv_query($conn, $query, $params);
        if ($stmt === false) {
            die("Error al obtener ID_CONCILIACION: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmt);

        // Iterar sobre los datos seleccionados y realizar inserciones
        foreach ($datosSeleccionados as $dato) {
            // Separar los valores del checkbox
            list($cuenta, $fecha, $n_documento, $descripcion, $monto_detalle_int) = explode(',', $dato);

            // Convertir la fecha de dd/mm/yyyy a yyyy-mm-dd
            $fechaConvertida = DateTime::createFromFormat('d/m/Y', $fecha);
            if ($fechaConvertida === false) {
                die("Error: Formato de fecha inválido en el dato seleccionado.");
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
                error_log("Error al insertar datos: " . print_r(sqlsrv_errors(), true));
                die("Error al insertar datos: " . print_r(sqlsrv_errors(), true));
            }
        }

        foreach ($datosRemesasSeleccionadas as $dato_remesa) {

            // Separar los valores del checkbox
            list($n_remesa, $fecha_remesa, $producto, $monto_remesa_int) = explode(',', $dato_remesa);

            $sql_canalizacion = "EXEC [_SP_CONCILIACIONES_CONCILIAR_REMESAS_CUENTA_CONSULTA] ?";
            $params_canalizacion = array(array($n_remesa, SQLSRV_PARAM_IN));
            $stmt_canalizacion = sqlsrv_query($conn, $sql_canalizacion, $params_canalizacion);
    
            if ($stmt_canalizacion === false) {
                die("Error en la consulta de cuenta beneficiario: " . print_r(sqlsrv_errors(), true));
            }
            $canalizacion_row = sqlsrv_fetch_array($stmt_canalizacion, SQLSRV_FETCH_ASSOC);
    
            if ($canalizacion_row['ID_TIPO_CANALIZACION'] == 2) {
                $tipo_canal = 2;
            } 
            if ($canalizacion_row['ID_TIPO_CANALIZACION'] == 3) {
                $tipo_canal = 3;
            } else {
                $tipo_canal = 2;
            }
            
            // Convertir la fecha de dd/mm/yyyy a yyyy-mm-dd
            $fechaRemesaConvertida = DateTime::createFromFormat('d/m/Y', $fecha_remesa);
            if ($fechaRemesaConvertida === false) {
                die("Error: Formato de fecha inválido en el dato seleccionado.");
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
                error_log("Error al insertar datos: "   . print_r(sqlsrv_errors(), true));
                die("Error al insertar datos: "         . print_r(sqlsrv_errors(), true));
            }
        }

    } else {
        // Manejar el caso en que no hay detalles seleccionados
        die("No se han seleccionado detalles.");
    }

    // Para procesar los datos de remesas más adelante, puedes guardar o imprimirlos aquí
    // if (!empty($datosRemesasSeleccionadas)) {
    //     // Lógica para manejar remesas (por ahora solo se imprime)
    //     print_r($datosRemesasSeleccionadas);
    // }

    header("Location: conciliaciones_cartola_pareo.php?op=0");
    exit;
} else {
    // Responder con error si la solicitud no es POST
    die("Método de solicitud no permitido.");
}
