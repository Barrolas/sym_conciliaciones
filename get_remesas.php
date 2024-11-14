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
    $sql_remesas = "EXEC [_SP_CONCILIACIONES_CONCILIAR_REMESAS_FECHA_CONSULTA] ?, ?";
    $params_remesas = array(array($fecha_cartola,   SQLSRV_PARAM_IN),
                            array($cuenta_cartola,  SQLSRV_PARAM_IN));
    $stmt_remesas = sqlsrv_query($conn, $sql_remesas, $params_remesas);

    if ($stmt_remesas === false) {
        die("Error en la consulta de remesas: " . print_r(sqlsrv_errors(), true));
    }
    
    $output = '';

    while ($remesas_row = sqlsrv_fetch_array($stmt_remesas, SQLSRV_FETCH_ASSOC)) {
        $n_remesa = $remesas_row['CODIGO'];

        // Obtener detalles de cada remesa
        $sql_remesas_det = "EXEC [_SP_CONCILIACIONES_CONCILIAR_REMESAS_CONSULTA] ?";
        $params_remesas_det = array(array($n_remesa, SQLSRV_PARAM_IN));
        $stmt_remesas_det = sqlsrv_query($conn, $sql_remesas_det, $params_remesas_det);

        if ($stmt_remesas_det === false) {
            die("Error en la consulta de detalles de remesas: " . print_r(sqlsrv_errors(), true));
        }

        $remesas_det_row = sqlsrv_fetch_array($stmt_remesas_det, SQLSRV_FETCH_ASSOC);

        // Obtener cuenta beneficiario
        $sql_remesas_cta = "EXEC [_SP_CONCILIACIONES_CONCILIAR_REMESAS_CUENTA_CONSULTA] ?";
        $params_remesas_cta = array(array($n_remesa, SQLSRV_PARAM_IN));
        $stmt_remesas_cta = sqlsrv_query($conn, $sql_remesas_cta, $params_remesas_cta);

        if ($stmt_remesas_cta === false) {
            die("Error en la consulta de cuenta beneficiario: " . print_r(sqlsrv_errors(), true));
        }

        $remesas_cta_row = sqlsrv_fetch_array($stmt_remesas_cta, SQLSRV_FETCH_ASSOC);

        $fecha_remesa       = $remesas_det_row['FECHA'];
        $producto           = $remesas_det_row['PRODUCTO'];
        $monto_remesa       = number_format($remesas_det_row['MONTO'], 0, ',', '.');
        $monto_remesa_int   = (int) $remesas_det_row['MONTO'];
        $cuenta_remesa      = $remesas_cta_row['CUENTA_BENEFICIARIO'] ?? '';

        // Generar fila HTML para cada remesa
        $output .= "
            <tr>
                <td>
                    <input type='checkbox' class='select-remesa-checkbox' name='selected_remesas[]' 
                    value='{$n_remesa},{$fecha_remesa},{$producto},{$monto_remesa_int}'>
                </td>
                <td>{$fecha_remesa}     </td>
                <td>{$n_remesa}         </td>
                <td>{$cuenta_remesa}    </td>
                <td>{$producto}         </td>
                <td>{$monto_remesa}     </td>
            </tr>";
    }

    // Devolver la salida en HTML para ser insertada en la tabla
    echo $output;
} else {
    echo "<tr><td colspan='6'>Datos insuficientes.</td></tr>";
}
?>
