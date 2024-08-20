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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate; // Importar el namespace correcto

$documento = new Spreadsheet();
$documento
    ->getProperties()
    ->setCreator("Sistema Conciliaciones")
    ->setLastModifiedBy('')
    ->setTitle('Archivo de Pendientes')
    ->setDescription('Archivo de Pendientes');

$hojaDeProductos = $documento->getActiveSheet();
$hojaDeProductos->setTitle("Pendientes");

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
$encabezado = ["CTA BENEF", "F. RECEP", "TRANSACCION", "RUT DEUD", "F. VENC", "OPERACION", "MONTO DOC", "CUBIERTO"];
$hojaDeProductos->fromArray($encabezado, null, 'A1');

// Asignar el formato de celda para la columna de saldo
//$hojaDeProductos->getStyle('J')->getNumberFormat()->setFormatCode('#.##0'); // Asegúrate de que la columna de saldo esté formateada correctamente

$sql = "EXEC [_SP_CONCILIACIONES_PENDIENTES_LISTA]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    header("Location: menu_principal.php?op=1");
    exit;
}

$numeroDeFila = 2;
$i = 0;

while ($gestion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    
    $i++;

    // Manejo de F_REC
    $fecha_rec = $gestion["F_REC"];
    if ($fecha_rec instanceof DateTime) {
        // Si ya es un objeto DateTime, solo formatear
        $fecha_formateada_rec = $fecha_rec->format('Y-m-d');
    } else {
        // Si es una cadena, intentar convertirla
        $date_rec = DateTime::createFromFormat('d/m/Y', $fecha_rec);
        $fecha_formateada_rec = $date_rec !== false ? $date_rec->format('Y-m-d') : 'Fecha inválida';
    }
    // Manejo de F_VENC
    $fecha_venc = $gestion["F_VENC"];
    if ($fecha_venc instanceof DateTime) {
        // Si ya es un objeto DateTime, solo formatear
        $fecha_formateada_venc = $fecha_venc->format('Y-m-d');
    } else {
        // Si es una cadena, intentar convertirla
        $date_venc = DateTime::createFromFormat('d/m/Y', $fecha_venc);
        $fecha_formateada_venc = $date_venc !== false ? $date_venc->format('Y-m-d') : 'Fecha inválida';
    }
    $formattedMontoDoc = number_format($gestion["MONTO_DOC"], 0, ',', '.');
    $formattedMonto = number_format($gestion["MONTO"], 0, ',', '.');

    $hojaDeProductos->setCellValueExplicitByColumnAndRow(1, $numeroDeFila, $gestion["CUENTA"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueByColumnAndRow        (2, $numeroDeFila, htmlspecialchars($fecha_formateada_rec));
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(3, $numeroDeFila, $gestion["TRANSACCION"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueByColumnAndRow        (4, $numeroDeFila, $gestion["RUT_DEUDOR"]);
    $hojaDeProductos->setCellValueByColumnAndRow        (5, $numeroDeFila, htmlspecialchars($fecha_formateada_venc));
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(6, $numeroDeFila, $gestion["N_DOC"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(7, $numeroDeFila, $formattedMontoDoc, DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(8, $numeroDeFila, $formattedMonto, DataType::TYPE_STRING);


    $numeroDeFila++;
}

autoSizeColumns($hojaDeProductos);

$writer = new Xlsx($documento);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="ConciliacionesPendientes' . $hoy_arch . '.xlsx"');
$writer->save('php://output');
