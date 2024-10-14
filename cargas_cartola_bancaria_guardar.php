<?php
session_start();
include("funciones.php");
include("conexiones.php");
//include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$idusuario 		= 1;

if ($_FILES['archivo']['name'] != '') {

	print_r($_FILES);
	//datos del arhivo
	$arr			= explode(".", $_FILES['archivo']['name']);
	$extension		= $arr[1];
	$nombre_archivo = 'CartolaBancaria.xlsx';
	$tipo_archivo 	= $_FILES['archivo']['type'];
	$tamano_archivo = $_FILES['archivo']['size'];
	//echo $tipo_archivo ."<BR>";
	//compruebo si las características del archivo son las que deseo

	echo $tipo_archivo;

	if (!move_uploaded_file($_FILES['archivo']['tmp_name'],  'archivos\\' . $nombre_archivo)) {
		echo "Error al subir el archivo: " . $_FILES['archivo']['error'];
	};
}

$sql1 = "{call [_SP_CONCILIACIONES_CARGA_CARTOLA_BCO]}";
$stmt1 = sqlsrv_query($conn, $sql1);

if ($stmt1 === false) {
	echo "Error en la ejecución de la declaración 1.\n";
	die(print_r(sqlsrv_errors(), true));
}
/*
$sql_asign = "EXEC [_SP_CONCILIACIONES_ASIGNADOS_LISTA]";
$stmt_asign = sqlsrv_query($conn, $sql_asign);
if ($stmt_asign === false) {
	die(print_r(sqlsrv_errors(), true));
}

while ($asignados = sqlsrv_fetch_array($stmt_asign, SQLSRV_FETCH_ASSOC)) {

	$id_asignacion 	= $asignados['ID_ASIGNACION'];
	$tipo_canal		= $asignados['ID_TIPO_CANALIZACION'];

	if ($tipo_canal == 2) {
		$sql_remesa = "{call [_SP_CONCILIACIONES_ASIGNACIONES_REMESAS_ACTUALIZA](?, ?)}";
		$params_remesa = array(
			array($id_asignacion,   SQLSRV_PARAM_IN),
			array($idusuario,     	SQLSRV_PARAM_IN),
		);
		$stmt_remesa = sqlsrv_query($conn, $sql_remesa, $params_remesa);
		if ($stmt_remesa === false) {
			die(print_r(sqlsrv_errors(), true));
		}
		//	$rowremesa = sqlsrv_fetch_array($stmt_remesa, SQLSRV_FETCH_ASSOC);
	}
}
*/
//INSERTAR OPERACION
//ACTUALIZAR CARGA
header("Location: cargas_cartola_bancaria.php?op=4");
