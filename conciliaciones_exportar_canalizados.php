<?php
session_start();
include("funciones.php");
include("conexiones.php");
//include("permisos_adm.php");
noCache();

$hoy_arch = date("YmdHis");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    ->setTitle('Archivo de Canalizados')
    ->setDescription('Archivo de Canalizados');

$hojaDeProductos = $documento->getActiveSheet();
$hojaDeProductos->setTitle("Canalizados");

function autoSizeColumns($sheet, $startColumn = 'A', $endColumn = null)
{
    $highestColumn = $sheet->getHighestColumn();
    $highestRow = $sheet->getHighestRow();
    $endColumn = $endColumn ?: $highestColumn;

    // Convertir las columnas a números
    $startColIndex = Coordinate::columnIndexFromString($startColumn);
    $endColIndex = Coordinate::columnIndexFromString($endColumn);

    for ($col = $startColIndex; $col <= $endColIndex; $col++) {
        $column = Coordinate::stringFromColumnIndex($col);
        $maxLength = 0;

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

// Escribir encabezado de los productos
$encabezado = ["CANAL", "CUENTA BENEF", "RUT CLIENTE", "RUT DEUDOR", "F. VENC", "OPERACION", "TIPO", "MONTO"];
$hojaDeProductos->fromArray($encabezado, null, 'A1');

$numeroDeFila = 2;
$i = 0;

$sql = "EXEC [_SP_CONCILIACIONES_CANALIZADOS_LISTA]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($conciliacion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

    $id_documento = $conciliacion['ID_DOC'];

    // Consulta para obtener el monto de abonos (solo si el estado no es '1')
    $sql4 = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ID](?)}";
    $params4 = array($id_documento);
    $stmt4 = sqlsrv_query($conn, $sql4, $params4);

    if ($stmt4 === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Procesar resultados de la consulta de detalles
    $detalles = sqlsrv_fetch_array($stmt4, SQLSRV_FETCH_ASSOC);

    // Consulta para obtener el estado del documento
    $sql5 = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ESTADO](?)}";
    $params5 = array($id_documento);
    $stmt5 = sqlsrv_query($conn, $sql5, $params5);

    if ($stmt5 === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $estado_pareo_text = 'N/A'; // Valor por defecto
    while ($estados = sqlsrv_fetch_array($stmt5, SQLSRV_FETCH_ASSOC)) {
        $estado_pareo = isset($estados['ID_ESTADO']) ? $estados['ID_ESTADO'] : NULL;
        switch ($estado_pareo) {
            case '1':
                $estado_pareo_text = 'CONCILIADO';
                break;
            case '2':
                $estado_pareo_text = 'ABONADO';
                break;
            case '3':
                $estado_pareo_text = 'PENDIENTE';
                break;
        }
    }

    $i++;

    $hojaDeProductos->setCellValueExplicitByColumnAndRow(1, $numeroDeFila, $detalles["CANALIZACION"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(2, $numeroDeFila, $detalles["CUENTA"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(3, $numeroDeFila, $detalles["RUT_CLIENTE"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(4, $numeroDeFila, $detalles["RUT_DEUDOR"], DataType::TYPE_STRING);
    $f_venc = $detalles["F_VENC"] instanceof DateTime ? $detalles["F_VENC"]->format('Y-m-d') : $detalles["F_VENC"];
    $hojaDeProductos->setCellValueByColumnAndRow(5, $numeroDeFila, $f_venc);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(6, $numeroDeFila, $detalles["N_DOC"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueByColumnAndRow(7, $numeroDeFila, $estado_pareo_text);
    $formattedValue = number_format($conciliacion["MONTO"], 0, ',', '.');
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(8, $numeroDeFila, $formattedValue, DataType::TYPE_STRING);
    
    $numeroDeFila++;
}

autoSizeColumns($hojaDeProductos);

$writer = new Xlsx($documento);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="ConciliacionesCanalizados' . $hoy_arch . '.xlsx"');
$writer->save('php://output');
