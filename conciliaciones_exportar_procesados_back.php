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
    ->setTitle('Archivo de Proceso')
    ->setDescription('Archivo de Proceso');

$hojaCheques = $documento->getActiveSheet();
$hojaCheques->setTitle("CHEQUES");
$hojaCheques->getStyle('A1:M1')->getFont()->setBold(true);
$hojaCheques->setSelectedCell('A2');

$hojaCMR_tr = $documento->createSheet();
$hojaCMR_tr->setTitle("TR CMR");
$hojaCMR_tr->getStyle('A1:L1')->getFont()->setBold(true);
$hojaCMR_tr->setSelectedCell('A2');

$hojaBancoVigente_tr = $documento->createSheet();
$hojaBancoVigente_tr->setTitle("TR B. Vigente");
$hojaBancoVigente_tr->getStyle('A1:N1')->getFont()->setBold(true);
$hojaBancoVigente_tr->setSelectedCell('A2');

$hojaBancoCastigo_tr = $documento->createSheet();
$hojaBancoCastigo_tr->setTitle("TR B. Castigo");
$hojaBancoCastigo_tr->getStyle('A1:O1')->getFont()->setBold(true);
$hojaBancoCastigo_tr->setSelectedCell('A2');

$hojaCMR_ch = $documento->createSheet();
$hojaCMR_ch->setTitle("CH CMR");
$hojaCMR_ch->getStyle('A1:L1')->getFont()->setBold(true);
$hojaCMR_ch->setSelectedCell('A2');

$hojaBancoVigente_ch = $documento->createSheet();
$hojaBancoVigente_ch->setTitle("CH B. Vigente");
$hojaBancoVigente_ch->getStyle('A1:N1')->getFont()->setBold(true);
$hojaBancoVigente_ch->setSelectedCell('A2');

$hojaBancoCastigo_ch = $documento->createSheet();
$hojaBancoCastigo_ch->setTitle("CH B. Castigo");
$hojaBancoCastigo_ch->getStyle('A1:O1')->getFont()->setBold(true);
$hojaBancoCastigo_ch->setSelectedCell('A2');

$hojaHipotecario_tr = $documento->createSheet();
$hojaHipotecario_tr->setTitle("TR HIPOT");
$hojaHipotecario_tr->getStyle('A1:L1')->getFont()->setBold(true);
$hojaHipotecario_tr->setSelectedCell('A2');

$hojaHipotecario_ch = $documento->createSheet();
$hojaHipotecario_ch->setTitle("CH HIPOT");
$hojaHipotecario_ch->getStyle('A1:O1')->getFont()->setBold(true);
$hojaHipotecario_ch->setSelectedCell('A2');

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

$numeroDeFilaCheques            = 2;
$numeroDeFilaCMR_tr             = 2;
$numeroDeFilaBancoVigente_tr    = 2;
$numeroDeFilaBancoCastigo_tr    = 2;
$numeroDeFilaCMR_ch             = 2;
$numeroDeFilaBancoVigente_ch    = 2;
$numeroDeFilaBancoCastigo_ch    = 2;
$numeroDeFilaHipotecario_tr     = 2;
$numeroDeFilaHipotecario_ch     = 2;

$sql = "{call [_SP_CONCILIACIONES_ASIGNADOS_LISTA]}";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

while ($asignados = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

    $idpareo_sis    = $asignados['ID_PAREO_SISTEMA'];
    $id_asignacion  = $asignados['ID_ASIGNACION'];

    $sql_pd = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ID_PS](?)}";
    $params_pd = array(
        array($idpareo_sis,     SQLSRV_PARAM_IN),
    );
    $stmt_pd = sqlsrv_query($conn, $sql_pd, $params_pd);
    if ($stmt_pd === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $p_docs = sqlsrv_fetch_array($stmt_pd, SQLSRV_FETCH_ASSOC);

    $cuenta = $p_docs['CUENTA_BENEFICIARIO'];

    $sql_qtydocs = "{call [_SP_CONCILIACIONES_CANALIZADOS_PROCESADOS_CANTIDAD_PAREO_SISTEMA](?)}";
    $params_qtydocs = array(
        array($idpareo_sis,    SQLSRV_PARAM_IN),
    );
    $stmt_qtydocs = sqlsrv_query($conn, $sql_qtydocs, $params_qtydocs);
    if ($stmt_qtydocs === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $qtydocs = sqlsrv_fetch_array($stmt_qtydocs, SQLSRV_FETCH_ASSOC);

    $sql_pagodocs = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_METODOS_PAGO](?)}";
    $params_pagodocs = array(
        array($idpareo_sis,    SQLSRV_PARAM_IN),
    );
    $stmt_pagodocs = sqlsrv_query($conn, $sql_pagodocs, $params_pagodocs);
    if ($stmt_pagodocs === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $pagodocs = sqlsrv_fetch_array($stmt_pagodocs, SQLSRV_FETCH_ASSOC);

    //Variables pareo sistema
    $deud_rut       = $asignados['DEUDOR_RUT'];
    $deud_dv        = $asignados['DEUDOR_DV'];
    $deud_nom       = $asignados['DEUDOR_NOMBRE'];
    $cant_docs      = $asignados['PAGO_DOCS'];
    $operacion      = $asignados['N_DOC'];
    $tipo_canal     = $asignados['ID_TIPO_CANALIZACION'];
    //Variables pareo docs
    $benef_cta      = $p_docs['CUENTA_BENEFICIARIO'];
    $cte_rut        = $p_docs['RUT_CLIENTE'];
    $f_recepcion    = $p_docs['FECHA_RECEPCION'];
    $f_venc         = isset($p_docs['F_VENC']) ? $p_docs['F_VENC']->format('Y-m-d') : '';
    $monto_tr       = $p_docs['MONTO_TRANSACCION'];
    $ord_rut        = $p_docs['ORDENANTE_RUT'];
    $ord_dv         = $p_docs['ORDENANTE_DV'];
    $ord_banco      = $p_docs['ORDENANTE_BANCO'];
    $ord_cta        = $p_docs['ORDENANTE_CUENTA'];
    $monto_doc      = $p_docs['MONTO_DOCUMENTO'];
    $producto       = $p_docs['SUBPRODUCTO'];
    $cartera        = $p_docs['CARTERA'];
    $pago_docs      = $pagodocs['DESCRIPCION_PAGOS'];

    /*
    print_r($p_docs);
    print_r($asignados);
    print_r($pagodocs);
*/
    //exit;

    /* ====================================================SUMARIO CHEQUES======================================================*/

    // Bloque CMR TRANSFERENCIA
    if ($tipo_canal == 1) {
        // Escribir encabezado de los productos para CMR
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
        $hojaCheques->setCellValueExplicitByColumnAndRow(12, $numeroDeFilaCheques, $pago_docs, DataType::TYPE_STRING);
        $hojaCheques->setCellValueByColumnAndRow(13, $numeroDeFilaCheques, '');

        $numeroDeFilaCheques++;
    }


    /* ====================================================TRANSFERENCIAS======================================================*/

    // Bloque CMR TRANSFERENCIA
    if ($tipo_canal == 2 && $benef_cta == '61682381' && $producto != 'HIPOTECARIO') {
        // Escribir encabezado de los productos para CMR
        $encabezadoCMR_tr = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "Cartera", "Cuenta Beneficiario"];
        $hojaCMR_tr->fromArray($encabezadoCMR_tr, null, 'A1');

        $hojaCMR_tr->setCellValueByColumnAndRow         (1, $numeroDeFilaCMR_tr, $id_asignacion);
        $hojaCMR_tr->setCellValueExplicitByColumnAndRow (2, $numeroDeFilaCMR_tr, $ord_rut, DataType::TYPE_STRING);
        $hojaCMR_tr->setCellValueExplicitByColumnAndRow (3, $numeroDeFilaCMR_tr, $ord_dv, DataType::TYPE_STRING);
        $hojaCMR_tr->setCellValueByColumnAndRow         (4, $numeroDeFilaCMR_tr, $ord_banco);
        $hojaCMR_tr->setCellValueExplicitByColumnAndRow (5, $numeroDeFilaCMR_tr, $ord_cta, DataType::TYPE_STRING);
        $hojaCMR_tr->setCellValueByColumnAndRow         (6, $numeroDeFilaCMR_tr, $monto_tr);
        $hojaCMR_tr->setCellValueExplicitByColumnAndRow (7, $numeroDeFilaCMR_tr, $deud_rut, DataType::TYPE_STRING);
        $hojaCMR_tr->setCellValueExplicitByColumnAndRow (8, $numeroDeFilaCMR_tr, $deud_dv, DataType::TYPE_STRING);
        $hojaCMR_tr->setCellValueExplicitByColumnAndRow (9, $numeroDeFilaCMR_tr, $operacion, DataType::TYPE_STRING);
        $hojaCMR_tr->setCellValueByColumnAndRow         (10, $numeroDeFilaCMR_tr, $monto_doc);
        $hojaCMR_tr->setCellValueExplicitByColumnAndRow (11, $numeroDeFilaCMR_tr, $cartera, DataType::TYPE_STRING);
        $hojaCMR_tr->setCellValueExplicitByColumnAndRow (12, $numeroDeFilaCMR_tr, $benef_cta, DataType::TYPE_STRING);

        $numeroDeFilaCMR_tr++;
    }

    // Bloque Banco Vigente TRANSFERENCIA
    if ($tipo_canal == 2 && $benef_cta == '29743125' && $producto != 'HIPOTECARIO') {
        // Escribir encabezado de los productos para Banco Vigente
        $encabezadoBancoVigente_tr = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "Nombre Producto", "Cartera", "N° Cuotas a pagar", "Cuenta Beneficiario"];
        $hojaBancoVigente_tr->fromArray($encabezadoBancoVigente_tr, null, 'A1');

        $hojaBancoVigente_tr->setCellValueByColumnAndRow(1, $numeroDeFilaBancoVigente_tr, $id_asignacion);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(2, $numeroDeFilaBancoVigente_tr, $ord_rut,    DataType::TYPE_STRING);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(3, $numeroDeFilaBancoVigente_tr, $ord_dv,     DataType::TYPE_STRING);
        $hojaBancoVigente_tr->setCellValueByColumnAndRow(4, $numeroDeFilaBancoVigente_tr, $ord_banco);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(5, $numeroDeFilaBancoVigente_tr, $ord_cta,    DataType::TYPE_STRING);
        $hojaBancoVigente_tr->setCellValueByColumnAndRow(6, $numeroDeFilaBancoVigente_tr, $monto_tr);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(7, $numeroDeFilaBancoVigente_tr, $deud_rut,   DataType::TYPE_STRING);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(8, $numeroDeFilaBancoVigente_tr, $deud_dv,    DataType::TYPE_STRING);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(9, $numeroDeFilaBancoVigente_tr, $operacion,  DataType::TYPE_STRING);
        $hojaBancoVigente_tr->setCellValueByColumnAndRow(10, $numeroDeFilaBancoVigente_tr, $monto_doc);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(11, $numeroDeFilaBancoVigente_tr, $producto,   DataType::TYPE_STRING);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(12, $numeroDeFilaBancoVigente_tr, $cartera,    DataType::TYPE_STRING);
        $hojaBancoVigente_tr->setCellValueByColumnAndRow(13, $numeroDeFilaBancoVigente_tr, $pago_docs);
        $hojaBancoVigente_tr->setCellValueExplicitByColumnAndRow(14, $numeroDeFilaBancoVigente_tr, $benef_cta,  DataType::TYPE_STRING);

        $numeroDeFilaBancoVigente_tr++;
    }

    // Bloque Banco Castigo TRANSFERENCIA
    if ($tipo_canal == 2 && $benef_cta == '61682420' && $producto != 'HIPOTECARIO') {
        // Escribir encabezado de los productos para Banco Vigente
        $encabezadoBancoCastigo_tr = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "N° Cuotas", "Cartera", "N° Cuotas a pagar", "Nombre Deudor", "Cuenta Beneficiario"];
        $hojaBancoCastigo_tr->fromArray($encabezadoBancoCastigo_tr, null, 'A1');

        $hojaBancoCastigo_tr->setCellValueByColumnAndRow(1, $numeroDeFilaBancoCastigo_tr, $id_asignacion);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(2, $numeroDeFilaBancoCastigo_tr, $ord_rut,    DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(3, $numeroDeFilaBancoCastigo_tr, $ord_dv,     DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueByColumnAndRow(4, $numeroDeFilaBancoCastigo_tr, $ord_banco);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(5, $numeroDeFilaBancoCastigo_tr, $ord_cta,    DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueByColumnAndRow(6, $numeroDeFilaBancoCastigo_tr, $monto_tr);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(7, $numeroDeFilaBancoCastigo_tr, $deud_rut,   DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(8, $numeroDeFilaBancoCastigo_tr, $deud_dv,    DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(9, $numeroDeFilaBancoCastigo_tr, $operacion,  DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueByColumnAndRow(10, $numeroDeFilaBancoCastigo_tr, $monto_doc);
        $hojaBancoCastigo_tr->setCellValueByColumnAndRow(11, $numeroDeFilaBancoCastigo_tr, $cant_docs);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(12, $numeroDeFilaBancoCastigo_tr, $cartera . " BANCO", DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(13, $numeroDeFilaBancoCastigo_tr, $pago_docs,  DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(14, $numeroDeFilaBancoCastigo_tr, $deud_nom,   DataType::TYPE_STRING);
        $hojaBancoCastigo_tr->setCellValueExplicitByColumnAndRow(15, $numeroDeFilaBancoCastigo_tr, $benef_cta,  DataType::TYPE_STRING);

        $numeroDeFilaBancoCastigo_tr++;
    }

    /* ==========================================================CHEQUES========================================================*/

    // Bloque CMR CHEQUE
    if ($tipo_canal == 1 && $benef_cta == '61682381' && $producto != 'HIPOTECARIO') {
        // Escribir encabezado de los productos para CMR
        $encabezadoCMR_tr = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "Cartera", "Cuenta Beneficiario"];
        $hojaCMR_ch->fromArray($encabezadoCMR_tr, null, 'A1');

        $hojaCMR_ch->setCellValueByColumnAndRow(1, $numeroDeFilaCMR_ch, $id_asignacion);
        $hojaCMR_ch->setCellValueExplicitByColumnAndRow(2, $numeroDeFilaCMR_ch, $ord_rut, DataType::TYPE_STRING);
        $hojaCMR_ch->setCellValueExplicitByColumnAndRow(3, $numeroDeFilaCMR_ch, $ord_dv, DataType::TYPE_STRING);
        $hojaCMR_ch->setCellValueByColumnAndRow(4, $numeroDeFilaCMR_ch, $ord_banco);
        $hojaCMR_ch->setCellValueExplicitByColumnAndRow(5, $numeroDeFilaCMR_ch, $ord_cta, DataType::TYPE_STRING);
        $hojaCMR_ch->setCellValueByColumnAndRow(6, $numeroDeFilaCMR_ch, $monto_tr);
        $hojaCMR_ch->setCellValueExplicitByColumnAndRow(7, $numeroDeFilaCMR_ch, $deud_rut, DataType::TYPE_STRING);
        $hojaCMR_ch->setCellValueExplicitByColumnAndRow(8, $numeroDeFilaCMR_ch, $deud_dv, DataType::TYPE_STRING);
        $hojaCMR_ch->setCellValueExplicitByColumnAndRow(9, $numeroDeFilaCMR_ch, $operacion, DataType::TYPE_STRING);
        $hojaCMR_ch->setCellValueByColumnAndRow(10, $numeroDeFilaCMR_ch, $monto_doc);
        $hojaCMR_ch->setCellValueExplicitByColumnAndRow(11, $numeroDeFilaCMR_ch, $cartera, DataType::TYPE_STRING);
        $hojaCMR_ch->setCellValueExplicitByColumnAndRow(12, $numeroDeFilaCMR_ch, $benef_cta, DataType::TYPE_STRING);

        $numeroDeFilaCMR_ch++;
    }

    // Bloque Banco Vigente CHEQUE
    if ($tipo_canal == 1 && $benef_cta == '29743125' && $producto != 'HIPOTECARIO') {
        // Escribir encabezado de los productos para Banco Vigente
        $encabezadoBancoVigente_ch = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "Nombre Producto", "Cartera", "N° Cuotas a pagar", "Cuenta Beneficiario"];
        $hojaBancoVigente_ch->fromArray($encabezadoBancoVigente_ch, null, 'A1');

        $hojaBancoVigente_ch->setCellValueByColumnAndRow(1, $numeroDeFilaBancoVigente_ch, $id_asignacion);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(2, $numeroDeFilaBancoVigente_ch, $ord_rut,    DataType::TYPE_STRING);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(3, $numeroDeFilaBancoVigente_ch, $ord_dv,     DataType::TYPE_STRING);
        $hojaBancoVigente_ch->setCellValueByColumnAndRow(4, $numeroDeFilaBancoVigente_ch, $ord_banco);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(5, $numeroDeFilaBancoVigente_ch, $ord_cta,    DataType::TYPE_STRING);
        $hojaBancoVigente_ch->setCellValueByColumnAndRow(6, $numeroDeFilaBancoVigente_ch, $monto_tr);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(7, $numeroDeFilaBancoVigente_ch, $deud_rut,   DataType::TYPE_STRING);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(8, $numeroDeFilaBancoVigente_ch, $deud_dv,    DataType::TYPE_STRING);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(9, $numeroDeFilaBancoVigente_ch, $operacion,  DataType::TYPE_STRING);
        $hojaBancoVigente_ch->setCellValueByColumnAndRow(10, $numeroDeFilaBancoVigente_ch, $monto_doc);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(11, $numeroDeFilaBancoVigente_ch, $producto,   DataType::TYPE_STRING);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(12, $numeroDeFilaBancoVigente_ch, $cartera,    DataType::TYPE_STRING);
        $hojaBancoVigente_ch->setCellValueByColumnAndRow(13, $numeroDeFilaBancoVigente_ch, $pago_docs);
        $hojaBancoVigente_ch->setCellValueExplicitByColumnAndRow(14, $numeroDeFilaBancoVigente_ch, $benef_cta,  DataType::TYPE_STRING);

        $numeroDeFilaBancoVigente_ch++;
    }

    // Bloque Banco Castigo CHEQUE
    if ($tipo_canal == 1 && $benef_cta == '61682420' && $producto != 'HIPOTECARIO') {
        // Escribir encabezado de los productos para Banco Vigente
        $encabezadoBancoCastigo_ch = ["ID", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "N° Cuotas", "Cartera", "N° Cuotas a pagar", "Nombre Deudor", "Cuenta Beneficiario"];
        $hojaBancoCastigo_ch->fromArray($encabezadoBancoCastigo_ch, null, 'A1');

        $hojaBancoCastigo_ch->setCellValueByColumnAndRow(1, $numeroDeFilaBancoCastigo_ch, $id_asignacion);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(2, $numeroDeFilaBancoCastigo_ch, $ord_rut,    DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(3, $numeroDeFilaBancoCastigo_ch, $ord_dv,     DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueByColumnAndRow(4, $numeroDeFilaBancoCastigo_ch, $ord_banco);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(5, $numeroDeFilaBancoCastigo_ch, $ord_cta,    DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueByColumnAndRow(6, $numeroDeFilaBancoCastigo_ch, $monto_tr);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(7, $numeroDeFilaBancoCastigo_ch, $deud_rut,   DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(8, $numeroDeFilaBancoCastigo_ch, $deud_dv,    DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(9, $numeroDeFilaBancoCastigo_ch, $operacion,  DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueByColumnAndRow(10, $numeroDeFilaBancoCastigo_ch, $monto_doc);
        $hojaBancoCastigo_ch->setCellValueByColumnAndRow(11, $numeroDeFilaBancoCastigo_ch, $cant_docs);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(12, $numeroDeFilaBancoCastigo_ch, $cartera . " BANCO", DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(13, $numeroDeFilaBancoCastigo_ch, $pago_docs,  DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(14, $numeroDeFilaBancoCastigo_ch, $deud_nom,   DataType::TYPE_STRING);
        $hojaBancoCastigo_ch->setCellValueExplicitByColumnAndRow(15, $numeroDeFilaBancoCastigo_ch, $benef_cta,  DataType::TYPE_STRING);

        $numeroDeFilaBancoCastigo_ch++;
    }
}
/* ======================================================HIPOTECARIOS=====================================================*/

$sql_hipotecario = "EXEC [_SP_CONCILIACIONES_CANALIZADOS_PROCESADOS_HIPOTECARIOS_LISTA]";
$stmt_hipotecario = sqlsrv_query($conn, $sql_hipotecario);
if ($stmt_hipotecario === false) {
    die(print_r(sqlsrv_errors(), true));
}

$numeroDeFilaHipotecario_tr     = 2;
$numeroDeFilaHipotecario_ch     = 2;

while ($hipotecario = sqlsrv_fetch_array($stmt_hipotecario, SQLSRV_FETCH_ASSOC)) {

    //Variables pareo sistema
    $id_ps_hipotecario          = $hipotecario['ID_PS'];
    $id_asign_hipotecario       = $hipotecario['ID_ASIGNACION'];
    $ord_rut_hipotecario        = $hipotecario['ORDENANTE_RUT'];
    $ord_dv_hipotecario         = $hipotecario['ORDENANTE_DV'];
    $ord_banco_hipotecario      = $hipotecario['ORDENANTE_BANCO'];
    $ord_cta_hipotecario        = $hipotecario['ORDENANTE_CUENTA'];
    $monto_tr_hipotecario       = $hipotecario['MONTO_TRANSACCION'];
    $deud_rut_hipotecario       = $hipotecario['DEUDOR_RUT'];
    $deud_dv_hipotecario        = $hipotecario['DEUDOR_DV'];
    $operacion_hipotecario      = $hipotecario['N_DOC'];
    $monto_doc_hipotecario      = $hipotecario['MONTO_DOCUMENTO'];
    $producto_hipotecario       = $hipotecario['SUBPRODUCTO'];
    $cartera_hipotecario        = $hipotecario['CARTERA'];
    $benef_cta_hipotecario      = $hipotecario['CUENTA_BENEFICIARIO'];
    $tipo_canal_hipotecario     = $hipotecario['CANAL'];
    $canalizacion_hipotecario   = $hipotecario['CANALIZACION'];

    /* ======================================================HIPOTECARIOS=====================================================*/

        $encabezadoHipotecario_ch = ["ID", "Canal", "Rut Ordenante", "DV", "Banco Ordenante", "Cuenta Ordenante", "Monto", "Rut Titular", "DVT", "Operacion", "Valor Cuota", "Subproducto", "Cartera", "Cuenta Beneficiario"];
        $hojaHipotecario_ch->fromArray($encabezadoHipotecario_ch, null, 'A1');

        $hojaHipotecario_ch->setCellValueByColumnAndRow         (1, $numeroDeFilaHipotecario_ch, $id_asign_hipotecario);
        $hojaHipotecario_ch->setCellValueByColumnAndRow         (2, $numeroDeFilaHipotecario_ch, $canalizacion_hipotecario);
        $hojaHipotecario_ch->setCellValueExplicitByColumnAndRow (3, $numeroDeFilaHipotecario_ch, $ord_rut_hipotecario,    DataType::TYPE_STRING);
        $hojaHipotecario_ch->setCellValueExplicitByColumnAndRow (4, $numeroDeFilaHipotecario_ch, $ord_dv_hipotecario,     DataType::TYPE_STRING);
        $hojaHipotecario_ch->setCellValueByColumnAndRow         (5, $numeroDeFilaHipotecario_ch, $ord_banco_hipotecario);
        $hojaHipotecario_ch->setCellValueExplicitByColumnAndRow (6, $numeroDeFilaHipotecario_ch, $ord_cta_hipotecario,    DataType::TYPE_STRING);
        $hojaHipotecario_ch->setCellValueByColumnAndRow         (7, $numeroDeFilaHipotecario_ch, $monto_tr_hipotecario);
        $hojaHipotecario_ch->setCellValueExplicitByColumnAndRow (8, $numeroDeFilaHipotecario_ch, $deud_rut_hipotecario,   DataType::TYPE_STRING);
        $hojaHipotecario_ch->setCellValueExplicitByColumnAndRow (9, $numeroDeFilaHipotecario_ch, $deud_dv_hipotecario,    DataType::TYPE_STRING);
        $hojaHipotecario_ch->setCellValueExplicitByColumnAndRow (10, $numeroDeFilaHipotecario_ch, $operacion_hipotecario,  DataType::TYPE_STRING);
        $hojaHipotecario_ch->setCellValueByColumnAndRow         (11, $numeroDeFilaHipotecario_ch, $monto_doc_hipotecario);
        $hojaHipotecario_ch->setCellValueByColumnAndRow         (12, $numeroDeFilaHipotecario_ch, $producto_hipotecario);
        $hojaHipotecario_ch->setCellValueByColumnAndRow         (13, $numeroDeFilaHipotecario_ch, $cartera_hipotecario);
        $hojaHipotecario_ch->setCellValueExplicitByColumnAndRow (14, $numeroDeFilaHipotecario_ch, $benef_cta_hipotecario,  DataType::TYPE_STRING);

        $numeroDeFilaHipotecario_ch++;

}

// Ajustar el ancho de las columnas en ambas hojas
autoSizeColumns($hojaCheques);
autoSizeColumns($hojaCMR_tr);
autoSizeColumns($hojaBancoVigente_tr);
autoSizeColumns($hojaBancoCastigo_tr);
autoSizeColumns($hojaCMR_ch);
autoSizeColumns($hojaBancoVigente_ch);
autoSizeColumns($hojaBancoCastigo_ch);
autoSizeColumns($hojaHipotecario_ch);
autoSizeColumns($hojaHipotecario_tr);

// Guardar el archivo
$writer = new Xlsx($documento);
$nombreArchivo = "Proceso_$hoy_arch.xlsx";
$writer->save($nombreArchivo);

// Enviar el archivo como respuesta
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');
readfile($nombreArchivo);

// Limpiar el archivo temporal
unlink($nombreArchivo);
