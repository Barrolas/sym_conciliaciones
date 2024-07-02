<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

$hoy_arch	= date("YmdHis");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id 	= $_GET["id"];

require_once ('phpexcel2/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$documento = new Spreadsheet();
$documento
    ->getProperties()
    ->setCreator("Sistema Gestiones en Terreno")
    ->setLastModifiedBy('')
    ->setTitle('Archivo de Gestiones')
    ->setDescription('Archivo de Gestiones');

# Como ya hay una hoja por defecto, la obtenemos, no la creamos
$hojaDeProductos = $documento->getActiveSheet();

$hojaDeProductos->setTitle("Gestiones");

# Escribir encabezado de los productos
$encabezado = ["RUT", "DV", "TIPO RESPUESTA", "ID_RESPUESTA", "OBSERVACION", "COBRADOR", "NOMINA", "LAT", "LON", "RUT CLIENTE"];
# El último argumento es por defecto A1 pero lo pongo para que se explique mejor
$hojaDeProductos->fromArray($encabezado, null, 'A1');

$sql ="SELECT DISTINCT UG.RUT,UG.DV,CT.DESCRIPCION AS TIPO_RESPUESTA,CS.HOMOLOGACION AS ID_RESPUESTA,UG.NOTAS,U.ID_USUARIO_BAJADA AS COBRADOR,UG.NOMINA,UG.LAT,UG.LON,UG.RUTCLIENTE 
		FROM `los_exportaciones_detalle` E
		join los_usuarios_gestiones UG ON UG.ID_USUARIO_GESTION = E.ID_GESTION
		join los_categoria_subcategoria CS ON CS.ID_SUBCATEGORIA=UG.ID_GESTION
		join los_categoria_tipo CT ON CT.ID_TIPO_GESTION=CS.ID_TIPO_GESTION
		join los_usuarios U ON U.USUARIO=UG.USUARIO_ASIGNADO
		WHERE E.ID_EXPORTACION=$id;";
// echo $sql;exit;

$resultado = $conn->query($sql);
// echo $sql;
$numeroDeFila = 2;
$i=0;
while($gestion   = $resultado->fetch()) {
	$i++;
	$hojaDeProductos->setCellValueByColumnAndRow(1, $numeroDeFila, $gestion["RUT"]);
    $hojaDeProductos->setCellValueByColumnAndRow(2, $numeroDeFila, $gestion["DV"]);
    $hojaDeProductos->setCellValueByColumnAndRow(3, $numeroDeFila, $gestion["TIPO_RESPUESTA"]);
    $hojaDeProductos->setCellValueByColumnAndRow(4, $numeroDeFila, $gestion["ID_RESPUESTA"]);
    $hojaDeProductos->setCellValueByColumnAndRow(5, $numeroDeFila, $gestion["NOTAS"]);
    $hojaDeProductos->setCellValueByColumnAndRow(6, $numeroDeFila, $gestion["COBRADOR"]);
    $hojaDeProductos->setCellValueByColumnAndRow(7, $numeroDeFila, $gestion["NOMINA"]);
    $hojaDeProductos->setCellValueByColumnAndRow(8, $numeroDeFila, $gestion["LAT"]);    
    $hojaDeProductos->setCellValueByColumnAndRow(9, $numeroDeFila, $gestion["LON"]);
	$hojaDeProductos->setCellValueByColumnAndRow(10, $numeroDeFila, $gestion["RUTCLIENTE"]);
    $numeroDeFila++;
};

//print_r($documento);exit;

# Crear un "escritor"
$writer = new Xlsx($documento);
# Le pasamos la ruta de guardado
// $writer->save('Exportado.xlsx');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Gestiones'.$hoy_arch.'.xlsx"');
$writer->save('php://output');



?>