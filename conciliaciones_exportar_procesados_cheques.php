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

$hoy_arch = date("YmdHis");

// Verificar que la solicitud se envió con el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_start = isset($_POST['date_start']) ? $_POST['date_start'] : null;
    $date_end = isset($_POST['date_end']) ? $_POST['date_end'] : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : null;

    // Validar y convertir las fechas al formato 'Y-m-d'
    if ($date_start) {
        $date_start = DateTime::createFromFormat('Y-m-d', $date_start);
        if ($date_start) {
            $date_start = $date_start->format('Y-m-d');
        } else {
            die("Fecha de inicio inválida.");
        }
    }

    if ($date_end) {
        $date_end = DateTime::createFromFormat('Y-m-d', $date_end);
        if ($date_end) {
            $date_end = $date_end->format('Y-m-d');
        } else {
            die("Fecha de fin inválida.");
        }
    }

    // Mapear el estado
    $p_estado1 = null;
    $p_estado2 = null;

    switch ($estado) {
        case '1': // Todos
            $p_estado1 = '1-';
            $p_estado2 = '4';
            break;
        case '2': // Pendientes de comprobante
            $p_estado1 = '1';
            $p_estado2 = '';
            break;
        case '3': // Sin conciliar
            $p_estado1 = '2';
            $p_estado2 = '';
            break;
        case '4': // Conciliados
            $p_estado1 = '3-';
            $p_estado2 = '4';
            break;
        default:
            die("Estado inválido.");
    }
}
/*
echo "Fecha de Inicio: $date_start<br>";
echo "Fecha de Fin: $date_end<br>";
echo "p_estado1: $p_estado1<br>";
echo "p_estado2: $p_estado2<br>";

exit;
*/
require_once('phpexcel2/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$documento = new Spreadsheet();
$documento
    ->getProperties()
    ->setCreator("Sistema Conciliaciones")
    ->setLastModifiedBy('')
    ->setTitle('Archivo de Proceso')
    ->setDescription('Archivo de Proceso');

$hojaCheques = $documento->setActiveSheetIndex(0);
$hojaCheques->setTitle("Cheques");
$hojaCheques->getStyle('A1:M1')->getFont()->setBold(true);
$hojaCheques->setSelectedCell('A2');


// Establece la primera hoja (hojaCheques) como la hoja activa
$documento->setActiveSheetIndex(0);

function autoSizeColumns($sheet, $startColumn = 'A', $endColumn = null)
{
    $highestColumn  = $sheet->getHighestColumn();
    $highestRow     = $sheet->getHighestRow();
    $endColumn      = $endColumn ?: $highestColumn;

    // Convertir las columnas a números
    $startColIndex  = Coordinate::columnIndexFromString($startColumn);
    $endColIndex    = Coordinate::columnIndexFromString($endColumn);

    for ($col = $startColIndex; $col <= $endColIndex; $col++) {
        $column     = Coordinate::stringFromColumnIndex($col);
        $maxLength  = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell($column . $row)->getValue();
            $cellValue = is_string($cellValue) ? $cellValue : (string)$cellValue;
            if (strlen($cellValue) > $maxLength) {
                $maxLength = strlen($cellValue);
            }
        }

        // Ajustar el ancho de la columna
        $sheet->getColumnDimension($column)->setWidth($maxLength + 2); // +2 para margen
    }
}

$numeroDeFilaCheques = 2;

$sql_asign    = "EXEC [_SP_CONCILIACIONES_CHEQUES_FILTRADOS_LISTA] ?, ?, ?, ?";
$params_asign = array(
    array($p_estado1,   SQLSRV_PARAM_IN),
    array($p_estado2,   SQLSRV_PARAM_IN),
    array($date_start,  SQLSRV_PARAM_IN),
    array($date_end,    SQLSRV_PARAM_IN),
);
$stmt_asign = sqlsrv_query($conn, $sql_asign, $params_asign);
if ($stmt_asign === false) {
    mostrarError("Error al ejecutar la consulta 'stmt_asign'.");
}

while ($asignados = sqlsrv_fetch_array($stmt_asign, SQLSRV_FETCH_ASSOC)) {

    $idpareo_sis        = $asignados['ID_PAREO_SISTEMA'];
    $iddoc              = $asignados['ID_DOCDEUDORES'];
    $id_asignacion      = $asignados['ID_ASIGNACION'];
    $id_estado_asign    = $asignados['ID_ESTADO'];
    /*
    print_r('$idpareo_sis: ' . $idpareo_sis . '; ');
    print_r('$iddoc: ' . $iddoc . '; ');
    print_r('$id_asignacion: ' . $id_asignacion . '; ');
    print_r('$id_estado_asign: ' . $id_estado_asign . '; ');
*/
    $sql_pd = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ID_PS](?)}";
    $params_pd = array(
        array($idpareo_sis,     SQLSRV_PARAM_IN),
    );
    $stmt_pd = sqlsrv_query($conn, $sql_pd, $params_pd);
    if ($stmt_pd === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_pd'.");
    }
    $p_docs = sqlsrv_fetch_array($stmt_pd, SQLSRV_FETCH_ASSOC);

    //print_r($p_docs);
    //exit;

    $sql_docdetalles = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ID](?)}";
    $params_docdetalles = array(
        array($iddoc,     SQLSRV_PARAM_IN),
    );
    $stmt_docdetalles = sqlsrv_query($conn, $sql_docdetalles, $params_docdetalles);
    if ($stmt_docdetalles === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_docdetalles'.");
    }
    $docdetalles = sqlsrv_fetch_array($stmt_docdetalles, SQLSRV_FETCH_ASSOC);

    $sql_qtydocs = "{call [_SP_CONCILIACIONES_CANALIZADOS_PROCESADOS_CANTIDAD_PAREO_SISTEMA](?)}";
    $params_qtydocs = array(
        array($idpareo_sis,    SQLSRV_PARAM_IN),
    );
    $stmt_qtydocs = sqlsrv_query($conn, $sql_qtydocs, $params_qtydocs);
    if ($stmt_qtydocs === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_qtydocs'.");
    }
    $qtydocs = sqlsrv_fetch_array($stmt_qtydocs, SQLSRV_FETCH_ASSOC);

    $sql_pagodocs = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_METODOS_PAGO](?)}";
    $params_pagodocs = array(
        array($idpareo_sis,    SQLSRV_PARAM_IN),
    );
    $stmt_pagodocs = sqlsrv_query($conn, $sql_pagodocs, $params_pagodocs);
    if ($stmt_pagodocs === false) {
        mostrarError("Error al ejecutar la consulta 'stmt_pagodocs'.");
    }
    $pagodocs = sqlsrv_fetch_array($stmt_pagodocs, SQLSRV_FETCH_ASSOC);

    // Variables asignados
    $deud_rut       = isset($asignados['DEUDOR_RUT']) ? $asignados['DEUDOR_RUT'] : '';
    $deud_dv        = isset($asignados['DEUDOR_DV']) ? $asignados['DEUDOR_DV'] : '';
    $deud_nom       = isset($asignados['DEUDOR_NOMBRE']) ? $asignados['DEUDOR_NOMBRE'] : '';
    $cant_docs      = isset($asignados['PAGO_DOCS']) ? $asignados['PAGO_DOCS'] : '';
    $operacion      = isset($asignados['N_DOC']) ? $asignados['N_DOC'] : '';
    $monto_doc      = isset($asignados['MONTO_DOC']) ? $asignados['MONTO_DOC'] : '';
    $tipo_canal     = isset($asignados['ID_TIPO_CANALIZACION']) ? $asignados['ID_TIPO_CANALIZACION'] : '';
    $canal          = isset($asignados['DESCRIPCION']) ? substr($asignados['DESCRIPCION'], 0, 2) : '';
    $n_cheque       = isset($asignados['N_CHEQUE']) ? $asignados['N_CHEQUE'] : '';
    $benef_cta      = isset($asignados['CUENTA_BENEFICIARIO']) ? $asignados['CUENTA_BENEFICIARIO'] : '';

    // Variables pareo docs
    $cte_rut        = isset($p_docs['RUT_CLIENTE']) ? $p_docs['RUT_CLIENTE'] : '';
    $f_recepcion    = isset($p_docs['FECHA_RECEPCION']) ? $p_docs['FECHA_RECEPCION'] : '';
    $f_venc         = isset($docdetalles['F_VENC']) ? $docdetalles['F_VENC']->format('Y-m-d') : '';
    $monto_tr       = isset($p_docs['MONTO_TRANSACCION']) ? $p_docs['MONTO_TRANSACCION'] : '';
    $ord_rut        = isset($p_docs['ORDENANTE_RUT']) ? $p_docs['ORDENANTE_RUT'] : '';
    $ord_dv         = isset($p_docs['ORDENANTE_DV']) ? $p_docs['ORDENANTE_DV'] : '';
    $ord_banco      = isset($p_docs['ORDENANTE_BANCO']) ? $p_docs['ORDENANTE_BANCO'] : '';
    $ord_cta        = isset($p_docs['ORDENANTE_CUENTA']) ? $p_docs['ORDENANTE_CUENTA'] : '';
    $producto       = isset($p_docs['SUBPRODUCTO']) ? $p_docs['SUBPRODUCTO'] : '';
    $cartera        = isset($p_docs['CARTERA']) ? $p_docs['CARTERA'] : '';
    $pago_docs      = isset($pagodocs['DESCRIPCION_PAGOS']) ? $pagodocs['DESCRIPCION_PAGOS'] : '';
    /*
    print_r($p_docs);
    print_r($asignados);
    print_r($pagodocs);

*/

    /* ====================================================SUMARIO CHEQUES======================================================*/

    if ($tipo_canal == 1) {

        $encabezadoCheques = ["ID", "Rut Titular", "DVT", "Nombre Titular", "Cta. Benef", "Rut Cliente", "Operación", "V. Cuota", "F. Venc", "Subproducto", "Cartera", "N° Cuotas", "N° Documento"];
        $hojaCheques->fromArray($encabezadoCheques, null, 'A1');

        $hojaCheques->setCellValueByColumnAndRow(1, $numeroDeFilaCheques, $id_asignacion);
        $hojaCheques->setCellValueExplicitByColumnAndRow(2, $numeroDeFilaCheques, $deud_rut, DataType::TYPE_STRING);
        $hojaCheques->setCellValueExplicitByColumnAndRow(3, $numeroDeFilaCheques, $deud_dv, DataType::TYPE_STRING);
        $hojaCheques->setCellValueExplicitByColumnAndRow(4, $numeroDeFilaCheques, $deud_nom, DataType::TYPE_STRING);
        $hojaCheques->setCellValueExplicitByColumnAndRow(5, $numeroDeFilaCheques, $benef_cta, DataType::TYPE_STRING);
        $hojaCheques->setCellValueExplicitByColumnAndRow(6, $numeroDeFilaCheques, $cte_rut, DataType::TYPE_STRING);
        $hojaCheques->setCellValueExplicitByColumnAndRow(7, $numeroDeFilaCheques, $operacion, DataType::TYPE_STRING);
        $hojaCheques->setCellValueExplicitByColumnAndRow(8, $numeroDeFilaCheques, $monto_doc, DataType::TYPE_STRING);
        $hojaCheques->setCellValueByColumnAndRow(9, $numeroDeFilaCheques, $f_venc);
        $hojaCheques->setCellValueExplicitByColumnAndRow(10, $numeroDeFilaCheques, $producto, DataType::TYPE_STRING);
        $hojaCheques->setCellValueByColumnAndRow(11, $numeroDeFilaCheques, $cartera);
        $hojaCheques->setCellValueExplicitByColumnAndRow(12, $numeroDeFilaCheques, $cant_docs, DataType::TYPE_STRING);
        $hojaCheques->setCellValueByColumnAndRow(13, $numeroDeFilaCheques, $n_cheque);

        $numeroDeFilaCheques++;
    }
}

//print_r($hojaCheques);
//exit;
// Ajustar el ancho de las columnas en ambas hojas
autoSizeColumns($hojaCheques);

// Guardar el archivo
$writer = new Xlsx($documento);
$nombreArchivo = "Cheques_$hoy_arch.xlsx";
$writer->save($nombreArchivo);

// Enviar el archivo como respuesta
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');
readfile($nombreArchivo);

// Limpiar el archivo temporal
unlink($nombreArchivo);
