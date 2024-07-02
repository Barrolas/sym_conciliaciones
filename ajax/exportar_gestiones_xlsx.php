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

$sql ="SELECT COUNT(DISTINCT UG.RUT) AS TOTAL
		FROM `los_usuarios_gestiones` UG
		join los_usuarios U ON U.USUARIO=UG.USUARIO_ASIGNADO
		join los_categoria_subcategoria CS ON CS.ID_SUBCATEGORIA=UG.ID_GESTION
		join los_categoria_tipo CT ON CT.ID_TIPO_GESTION=CS.ID_TIPO_GESTION
		WHERE UG.ESTADO=2;";
//echo $sql;exit;
$resultado = $conn->query($sql);
$contar    = $resultado->fetch();

if ($contar["TOTAL"] == 0) {
	header("Location: exportar.php?op=1");
	exit;
};

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
$encabezado = ["RUT", "DV", "TIPO RESPUESTA", "ID_RESPUESTA", "OBSERVACION", "COBRADOR", "NOMINA", "LAT", "LON", "RUT CLIENTE","F_GESTION"];
# El último argumento es por defecto A1 pero lo pongo para que se explique mejor
$hojaDeProductos->fromArray($encabezado, null, 'A1');

$sql ="SELECT DISTINCT UG.RUT,UG.DV,CT.DESCRIPCION AS TIPO_RESPUESTA,CS.HOMOLOGACION AS ID_RESPUESTA,UG.NOTAS,U.ID_USUARIO_BAJADA AS COBRADOR,UG.NOMINA,UG.LAT,UG.LON,UG.RUTCLIENTE,date_format(UG.F_GESTION, '%d-%m-%Y %H:%m:%s') as F_GESTION
		FROM `los_usuarios_gestiones` UG
		join los_usuarios U ON U.USUARIO=UG.USUARIO_ASIGNADO
		join los_categoria_subcategoria CS ON CS.ID_SUBCATEGORIA=UG.ID_GESTION
		join los_categoria_tipo CT ON CT.ID_TIPO_GESTION=CS.ID_TIPO_GESTION
		WHERE UG.ESTADO=2;";
//echo $sql;exit;

$sql3 = "INSERT INTO `los_exportaciones`
			( `FECHA`, `ID_USUARIO`, `CANTIDAD`) VALUES 
			('$hoy','".$_SESSION["ID_USUARIO"]."','".$contar["TOTAL"]."')";
$conn->query($sql3);
$last_id 	= $conn->lastInsertId();
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
	$hojaDeProductos->setCellValueByColumnAndRow(11, $numeroDeFila, $gestion["F_GESTION"]);	
	/*$documento->getActiveSheet()->getStyle('H'.$numeroDeFila)->getNumberFormat()
    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	*/
	$sql3="SELECT UG.ID_USUARIO_GESTION
		FROM `los_usuarios_gestiones` UG
		join los_usuarios U ON U.USUARIO=UG.USUARIO_ASIGNADO
		join los_categoria_subcategoria CS ON CS.ID_SUBCATEGORIA=UG.ID_GESTION
		join los_categoria_tipo CT ON CT.ID_TIPO_GESTION=CS.ID_TIPO_GESTION
		WHERE UG.ESTADO=2 AND UG.RUT='".$gestion["RUT"]."' AND UG.NOMINA=".$gestion["NOMINA"];
	$resultado3 = $conn->query($sql3);
	while($detalle   = $resultado3->fetch()) {
		$sql2 = "INSERT INTO `los_exportaciones_detalle`
					( `ID_EXPORTACION`, `ID_GESTION`) VALUES 
					('$last_id',".$detalle["ID_USUARIO_GESTION"].")";
		$conn->query($sql2);
	};
    $numeroDeFila++;
}

$sql ="UPDATE los_exportaciones 
		SET CANTIDAD=$i
		WHERE ID_EXPORTACION=$last_id;";
//echo $sql;exit;
$conn->query($sql);

$sql ="UPDATE los_usuarios_gestiones 
		SET ESTADO=3
		WHERE ESTADO=2;";
//echo $sql;exit;
$conn->query($sql);

# Crear un "escritor"
$writer = new Xlsx($documento);
# Le pasamos la ruta de guardado
// $writer->save('Exportado.xlsx');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Gestiones'.$hoy_arch.'.xlsx"');
$writer->save('php://output');



?>