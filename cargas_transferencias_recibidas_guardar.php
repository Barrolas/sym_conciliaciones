<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$idusuario 		= $_SESSION['ID_USUARIO'];

if ($_FILES['archivo']['name'] != '') {

	print_r($_FILES);
	//datos del arhivo
	$arr			= explode(".", $_FILES['archivo']['name']);
	$extension		= $arr[1];
	$nombre_archivo = 'TransferenciasRecibidas.xlsx';
	$tipo_archivo 	= $_FILES['archivo']['type'];
	$tamano_archivo = $_FILES['archivo']['size'];
	//echo $tipo_archivo ."<BR>";
	//compruebo si las características del archivo son las que deseo

	echo $tipo_archivo;

	if (!move_uploaded_file($_FILES['archivo']['tmp_name'],  'archivos\\' . $nombre_archivo)) {
		echo "Error al subir el archivo: " . $_FILES['archivo']['error'];
	};
}

$sql1 = "{call [_SP_CONCILIACIONES_CARGA_CARTOLA_TRANSFERENCIAS]}";
$stmt1 = sqlsrv_query($conn, $sql1);

if ($stmt1 === false) {
	echo "Error en la ejecución de la declaración 1.\n";
	die(print_r(sqlsrv_errors(), true));
}
    
header("Location: cargas_transferencias_recibidas.php?op=4");