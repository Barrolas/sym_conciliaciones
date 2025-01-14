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

        $sheet->getColumnDimension($column)->setWidth($maxLength + 2);
    }
}

$encabezado = ["CANAL", "CUENTA BENEF", "RUT CLIENTE", "RUT DEUDOR", "F. VENC", "OPERACION", "TIPO", "MONTO", "DIFERENCIA"];
$hojaDeProductos->fromArray($encabezado, null, 'A1');

$numeroDeFila = 2;
$i = 0;

$sql_canalizados_lista = "EXEC [_SP_CONCILIACIONES_CANALIZADOS_PENDIENTES_LISTA]";
$stmt_canalizados_lista = sqlsrv_query($conn, $sql_canalizados_lista);
if ($stmt_canalizados_lista === false) {
    mostrarError("Error al ejecutar la consulta de canalizados pendientes. -> stmt_canalizados_lista");
}

while ($conciliacion = sqlsrv_fetch_array($stmt_canalizados_lista, SQLSRV_FETCH_ASSOC)) {
    $id_documento = $conciliacion['ID_DOC'];

    $sql_doc_deudores = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ID](?)}";
    $params_doc_deudores = [$id_documento];
    $stmt_doc_deudores = sqlsrv_query($conn, $sql_doc_deudores, $params_doc_deudores);
    if ($stmt_doc_deudores === false) {
        mostrarError("Error al consultar detalles de documentos deudores. -> stmt_doc_deudores");
    }
    $detalles = sqlsrv_fetch_array($stmt_doc_deudores, SQLSRV_FETCH_ASSOC);

    $sql_diferencia_consulta = "{call [_SP_CONCILIACIONES_DIFERENCIAS_CONSULTA](?)}";
    $params_diferencia_consulta = [$id_documento];
    $stmt_diferencia_consulta = sqlsrv_query($conn, $sql_diferencia_consulta, $params_diferencia_consulta);
    if ($stmt_diferencia_consulta === false) {
        mostrarError("Error al consultar diferencia de documentos. -> stmt_diferencia_consulta");
    }
    $diferencia = sqlsrv_fetch_array($stmt_diferencia_consulta, SQLSRV_FETCH_ASSOC);

    $sql_doc_estado = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ESTADO](?)}";
    $params_doc_estado = [$id_documento];
    $stmt_doc_estado = sqlsrv_query($conn, $sql_doc_estado, $params_doc_estado);
    if ($stmt_doc_estado === false) {
        mostrarError("Error al consultar el estado de documentos deudores. -> stmt_doc_estado");
    }

    $estado_pareo_text = 'N/A';
    while ($estados = sqlsrv_fetch_array($stmt_doc_estado, SQLSRV_FETCH_ASSOC)) {
        $estado_pareo = $estados['ID_ESTADO'] ?? null;
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
    $formattedValue2 = number_format($diferencia["DIFERENCIA"], 0, ',', '.');
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(9, $numeroDeFila, $formattedValue2, DataType::TYPE_STRING);

    $numeroDeFila++;
}

autoSizeColumns($hojaDeProductos);

$writer = new Xlsx($documento);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="ConciliacionesCanalizadosPendientes' . $hoy_arch . '.xlsx"');
$writer->save('php://output');
