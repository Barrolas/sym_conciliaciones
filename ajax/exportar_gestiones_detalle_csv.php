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

$sql ="SELECT COUNT(DISTINCT UG.RUT) AS TOTAL
		FROM `los_usuarios_gestiones` UG
		join los_usuarios U ON U.USUARIO=UG.USUARIO_ASIGNADO
		join los_categoria_subcategoria CS ON CS.ID_SUBCATEGORIA=UG.ID_GESTION
		join los_categoria_tipo CT ON CT.ID_TIPO_GESTION=CS.ID_TIPO_GESTION
		WHERE UG.ESTADO=2;";
//echo $sql;exit;
$resultado = $conn->query($sql);
$contar    = $resultado->fetch();


# Escribir encabezado de los productos
$arreglo = array();
$arreglo[0] = array("NOMINA","RUT", "DV", "TIPO RESPUESTA",  "ID_RESPUESTA", "GESTION",  "COBRADOR", "F_GESTION", "DIRECCION", "COMUNA", "NUEVA_COMUNA","NUEVA_DIRECCION","PARTICULAR_COMERCIAL","NUEVO_TELEFONO","FECHA_COMPROMISO","MONTO_COMPROMISO");

$sql ="SELECT DISTINCT UG.RUT,UG.DV,CT.DESCRIPCION AS TIPO_RESPUESTA,CS.HOMOLOGACION AS ID_RESPUESTA,UG.NOTAS,U.ID_USUARIO_BAJADA AS COBRADOR,UG.NOMINA,UG.LAT,UG.LON,UG.RUTCLIENTE,date_format(UG.F_GESTION, '%d-%m-%Y %H:%i:%s') as F_GESTION,UG.DIRECCION,UG.COMUNA,UG.N_COMUNA,UG.N_DIRECCION,UG.TIPO_DIRECCION,UG.N_TELEFONO,UG.F_COMPROMISO,UG.MONTO_COMPROMISO
		FROM `los_exportaciones_detalle` E
		join los_usuarios_gestiones UG ON UG.ID_USUARIO_GESTION = E.ID_GESTION
		join los_categoria_subcategoria CS ON CS.ID_SUBCATEGORIA=UG.ID_GESTION
		join los_categoria_tipo CT ON CT.ID_TIPO_GESTION=CS.ID_TIPO_GESTION
		join los_usuarios U ON U.USUARIO=UG.USUARIO_ASIGNADO
		WHERE E.ID_EXPORTACION=$id";
// echo $sql;exit;

$resultado = $conn->query($sql);
// echo $sql;
$numeroDeFila = 2;
$i=0;
while($gestion   = $resultado->fetch()) {
	$i++;
	$nota = trim(preg_replace('/\s+/', ' ', utf8_decode($gestion["NOTAS"])));
	$arreglo[$i] = array($gestion["NOMINA"],$gestion["RUT"],$gestion["DV"],$gestion["TIPO_RESPUESTA"],$gestion["ID_RESPUESTA"],$nota,$gestion["COBRADOR"],$gestion["F_GESTION"],
					    trim($gestion["DIRECCION"]),$gestion["COMUNA"],$gestion["N_COMUNA"],$gestion["N_DIRECCION"],$gestion["TIPO_DIRECCION"],$gestion["N_TELEFONO"],
						 $gestion["F_COMPROMISO"],$gestion["MONTO_COMPROMISO"]);
}

# Crear un "escritor"
$ruta ="archivos/Gestiones".$hoy_arch.'.csv';
generarCSV($arreglo, $ruta, $delimitador = ';', $encapsulador = '"');


header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$ruta.'"');
readfile("$ruta");

function generarCSV($arreglo, $ruta, $delimitador, $encapsulador){
  $file_handle = fopen($ruta, 'w');
  foreach ($arreglo as $linea) {
    fputcsv($file_handle, $linea, $delimitador, $encapsulador);
  }
  rewind($file_handle);
  fclose($file_handle);
}

?>