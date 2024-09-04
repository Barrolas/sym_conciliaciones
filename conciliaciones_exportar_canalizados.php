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

$sql = "EXEC [_SP_CONCILIACIONES_CANALIZADOS_LISTA]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($conciliacion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

    $id_documento       = $conciliacion['ID_DOC'];
    $diferencia_doc     = 0;
    $cuenta             ='';

    // Consulta para obtener el monto de abonos (solo si el estado no es '1')
    $sql_diferencia = "{call [_SP_CONCILIACIONES_DIFERENCIAS_CONSULTA](?, ?)}";
    $params_diferencia = array(
        array($id_documento,        SQLSRV_PARAM_IN),
        array(&$diferencia_doc,     SQLSRV_PARAM_OUT)
    );

    $stmt_diferencia = sqlsrv_query($conn, $sql_diferencia, $params_diferencia);

    if ($stmt_diferencia === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Procesar resultados de la consulta de detalles
    $diferencia = sqlsrv_fetch_array($stmt_diferencia, SQLSRV_FETCH_ASSOC);

    if ($diferencia_doc == 0) {

        if ($cuenta == '29743125') {

            $hojaBancoVigente = $documento->getActiveSheet();
            $hojaBancoVigente->setTitle("BancoVigente");

            // Escribir encabezado de los productos
            $encabezado = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "Cartera"];
            $hojaBancoVigente->fromArray($encabezado, null, 'A1');

            $numeroDeFila = 2;

            $i++;

            $f_venc = $detalles["F_VENC"] instanceof DateTime ? $detalles["F_VENC"]->format('Y-m-d') : $detalles["F_VENC"];
            $formattedValue = number_format($conciliacion["MONTO"], 0, ',', '.');

            $hojaBancoVigente->setCellValueExplicitByColumnAndRow(1, $numeroDeFila, $detalles["CANALIZACION"], DataType::TYPE_STRING);
            $hojaBancoVigente->setCellValueExplicitByColumnAndRow(2, $numeroDeFila, $detalles["CUENTA"], DataType::TYPE_STRING);
            $hojaBancoVigente->setCellValueExplicitByColumnAndRow(3, $numeroDeFila, $detalles["RUT_CLIENTE"], DataType::TYPE_STRING);
            $hojaBancoVigente->setCellValueExplicitByColumnAndRow(4, $numeroDeFila, $detalles["RUT_DEUDOR"], DataType::TYPE_STRING);
            $hojaBancoVigente->setCellValueByColumnAndRow(5, $numeroDeFila, $f_venc);
            $hojaBancoVigente->setCellValueExplicitByColumnAndRow(6, $numeroDeFila, $detalles["N_DOC"], DataType::TYPE_STRING);
            $hojaBancoVigente->setCellValueByColumnAndRow(7, $numeroDeFila, $estado_pareo_text);
            $hojaBancoVigente->setCellValueExplicitByColumnAndRow(8, $numeroDeFila, $formattedValue, DataType::TYPE_STRING);

            $numeroDeFila++;
        }
        if ($cuenta == '61682381') {

            $hojaCMR = $documento->setActiveSheetIndex(1);
            $hojaCMR->setTitle("CMR");

            // Escribir encabezado de los productos
            $encabezado = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "Cartera"];
            $hojaCMR->fromArray($encabezado, null, 'A1');

            $numeroDeFila = 2;

            $i++;

            $f_venc = $detalles["F_VENC"] instanceof DateTime ? $detalles["F_VENC"]->format('Y-m-d') : $detalles["F_VENC"];
            $formattedValue = number_format($conciliacion["MONTO"], 0, ',', '.');

            $hojaCMR->setCellValueExplicitByColumnAndRow(1, $numeroDeFila, $detalles["CANALIZACION"], DataType::TYPE_STRING);
            $hojaCMR->setCellValueExplicitByColumnAndRow(2, $numeroDeFila, $detalles["CUENTA"], DataType::TYPE_STRING);
            $hojaCMR->setCellValueExplicitByColumnAndRow(3, $numeroDeFila, $detalles["RUT_CLIENTE"], DataType::TYPE_STRING);
            $hojaCMR->setCellValueExplicitByColumnAndRow(4, $numeroDeFila, $detalles["RUT_DEUDOR"], DataType::TYPE_STRING);
            $hojaCMR->setCellValueByColumnAndRow(5, $numeroDeFila, $f_venc);
            $hojaCMR->setCellValueExplicitByColumnAndRow(6, $numeroDeFila, $detalles["N_DOC"], DataType::TYPE_STRING);
            $hojaCMR->setCellValueByColumnAndRow(7, $numeroDeFila, $estado_pareo_text);
            $hojaCMR->setCellValueExplicitByColumnAndRow(8, $numeroDeFila, $formattedValue, DataType::TYPE_STRING);

            $numeroDeFila++;
        }
        if ($cuenta == '61682420') {

            $hojaBancoCastigo = $documento->setActiveSheetIndex(2);
            $hojaBancoCastigo->setTitle("BancoCastigo");

            // Escribir encabezado de los productos
            $encabezado = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "Cartera"];
            $hojaBancoVigente->fromArray($encabezado, null, 'A1');

            $numeroDeFila = 2;

            $i++;

            $f_venc = $detalles["F_VENC"] instanceof DateTime ? $detalles["F_VENC"]->format('Y-m-d') : $detalles["F_VENC"];
            $formattedValue = number_format($conciliacion["MONTO"], 0, ',', '.');

            $hojaBancoCastigo->setCellValueExplicitByColumnAndRow(1, $numeroDeFila, $detalles["CANALIZACION"], DataType::TYPE_STRING);
            $hojaBancoCastigo->setCellValueExplicitByColumnAndRow(2, $numeroDeFila, $detalles["CUENTA"], DataType::TYPE_STRING);
            $hojaBancoCastigo->setCellValueExplicitByColumnAndRow(3, $numeroDeFila, $detalles["RUT_CLIENTE"], DataType::TYPE_STRING);
            $hojaBancoCastigo->setCellValueExplicitByColumnAndRow(4, $numeroDeFila, $detalles["RUT_DEUDOR"], DataType::TYPE_STRING);
            $hojaBancoCastigo->setCellValueByColumnAndRow(5, $numeroDeFila, $f_venc);
            $hojaBancoCastigo->setCellValueExplicitByColumnAndRow(6, $numeroDeFila, $detalles["N_DOC"], DataType::TYPE_STRING);
            $hojaBancoCastigo->setCellValueByColumnAndRow(7, $numeroDeFila, $estado_pareo_text);
            $hojaBancoCastigo->setCellValueExplicitByColumnAndRow(8, $numeroDeFila, $formattedValue, DataType::TYPE_STRING);

            $numeroDeFila++;
        }
    }
    autoSizeColumns($hojaCMR);
    autoSizeColumns($hojaBancoVigente);
    autoSizeColumns($hojaBancoCastigo);
}



















$writer = new Xlsx($documento);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="ConciliacionesCanalizados' . $hoy_arch . '.xlsx"');
$writer->save('php://output');
