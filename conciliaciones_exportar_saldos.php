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
    ->setTitle('Archivo de Saldos')
    ->setDescription('Archivo de Saldos');

$hojaDeProductos = $documento->getActiveSheet();
$hojaDeProductos->setTitle("Saldos");

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
$encabezado = ["TRANSACCION", "FECHA RECEPCION", "CUENTA BENEFICIARIO", "PRODUCTO", "RUT", "DV", "NOMBRE", "BANCO", "CUENTA ORDENANTE", "SALDO"];
$hojaDeProductos->fromArray($encabezado, null, 'A1');

// Asignar el formato de celda para la columna de saldo
//$hojaDeProductos->getStyle('J')->getNumberFormat()->setFormatCode('#.##0'); // Asegúrate de que la columna de saldo esté formateada correctamente

$sql = "EXEC [_SP_CONCILIACIONES_SALDOS_LISTA]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    header("Location: menu_principal.php?op=1");
    exit;
}

$numeroDeFila = 2;
$i = 0;

while ($gestion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $i++;

    $hojaDeProductos->setCellValueExplicitByColumnAndRow(1, $numeroDeFila, $gestion["TRANSACCION"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueByColumnAndRow(2, $numeroDeFila, $gestion["F_REC"]);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(3, $numeroDeFila, $gestion["CUENTA"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueByColumnAndRow(4, $numeroDeFila, $gestion["PRODUCTO"]);
    $hojaDeProductos->setCellValueByColumnAndRow(5, $numeroDeFila, $gestion["RUT_ORD"]);
    $hojaDeProductos->setCellValueByColumnAndRow(6, $numeroDeFila, $gestion["DV"]);
    $hojaDeProductos->setCellValueByColumnAndRow(7, $numeroDeFila, $gestion["NOMBRE"]);
    $hojaDeProductos->setCellValueByColumnAndRow(8, $numeroDeFila, $gestion["BANCO"]);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(9, $numeroDeFila, $gestion["CTA_ORD"], DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueByColumnAndRow(10, $numeroDeFila, $gestion["SALDO"]);

    $numeroDeFila++;
}

autoSizeColumns($hojaDeProductos);

$writer = new Xlsx($documento);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="ConciliacionesSaldos' . $hoy_arch . '.xlsx"');
$writer->save('php://output');
