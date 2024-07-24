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

require_once ('phpexcel2/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType; // Asegúrate de usar el namespace correcto

$documento = new Spreadsheet();
$documento
    ->getProperties()
    ->setCreator("Sistema Conciliaciones")
    ->setLastModifiedBy('')
    ->setTitle('Archivo de Transferencias Indeterminadas')
    ->setDescription('Archivo de Transferencias Indeterminadas');

# Como ya hay una hoja por defecto, la obtenemos, no la creamos
$hojaDeProductos = $documento->getActiveSheet();

$hojaDeProductos->setTitle("Transferencias Indeterminadas");

# Escribir encabezado de los productos
$encabezado = ["RUT", "DV", "NOMBRE", "MONTO", "BANCO", "CUENTA"];
# El último argumento es por defecto A1 pero lo pongo para que se explique mejor
$hojaDeProductos->fromArray($encabezado, null, 'A1');

$sql = "EXEC [_SP_CONCILIACIONES_TRANSFERENCIAS_INDETERMINADAS_LISTA]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    header("Location: menu_principal.php?op=1");
    exit;
}

// echo $sql;
$numeroDeFila = 2;
$i = 0;

while ($gestion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $i++;

    $rutSinDv   = substr($gestion["RUT_ORDENANTE"], 0, -2);
    $dv         = str_replace('-', '', substr($gestion["RUT_ORDENANTE"], -2));
    $monto      = str_replace('.', '', $gestion["MONTO"]);
    
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(1, $numeroDeFila, $rutSinDv,   DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(2, $numeroDeFila, $dv,         DataType::TYPE_STRING);
    $hojaDeProductos->setCellValueByColumnAndRow        (3, $numeroDeFila, $gestion["NOMBRE_ORDENANTE"]);
    $hojaDeProductos->setCellValueByColumnAndRow        (4, $numeroDeFila, $monto);
    $hojaDeProductos->setCellValueByColumnAndRow        (5, $numeroDeFila, $gestion["BANCO_ORDENANTE"]);
    $hojaDeProductos->setCellValueExplicitByColumnAndRow(6, $numeroDeFila, $gestion["CUENTA_ORDENANTE"], DataType::TYPE_STRING);

    $numeroDeFila++;
//    print_r($gestion);
}

# Crear un "escritor"
$writer = new Xlsx($documento);
# Le pasamos la ruta de guardado
// $writer->save('Exportado.xlsx');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="TransferenciasIndeterminadas'.$hoy_arch.'.xlsx"');
$writer->save('php://output');

?>
