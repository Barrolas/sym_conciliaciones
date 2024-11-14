<?php
session_start();
include("funciones.php");
include("conexiones.php");
//include("permisos_adm.php");
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
/* print_r(sqlsrv_errors());
exit; */
$estado1 = 2;
$estado2 = 2;
$sql_asign    = "EXEC [_SP_CONCILIACIONES_ASIGNADOS_LISTA] ?, ?";
$params_asign = array(
	array($estado1,     SQLSRV_PARAM_IN),
	array($estado2,     SQLSRV_PARAM_IN),
);
$stmt_asign = sqlsrv_query($conn, $sql_asign, $params_asign);
if ($stmt_asign === false) {
	die(print_r(sqlsrv_errors(), true));
}
while ($asignados = sqlsrv_fetch_array($stmt_asign, SQLSRV_FETCH_ASSOC)) {

	$id_asignacion 	= $asignados['ID_ASIGNACION'];
	$tipo_canal		= $asignados['ID_TIPO_CANALIZACION'];
	$n_cheque		= $asignados['N_CHEQUE'];

	$sql_ch    = "EXEC [_SP_CONCILIACIONES_CARTOLA_CHEQUES_CONSULTA] ?";
	$params_ch = array(
		array($n_cheque, 	SQLSRV_PARAM_IN),
	);
	$stmt_ch = sqlsrv_query($conn, $sql_ch, $params_ch);
	if ($stmt_ch === false) {
		die(print_r(sqlsrv_errors(), true));
	}
	$cheque = sqlsrv_fetch_array($stmt_ch, SQLSRV_FETCH_ASSOC);

	$n_documento = isset($cheque['N_DOCUMENTO']) 	? $cheque['N_DOCUMENTO'] 	: null;
	$cuenta      = isset($cheque['CUENTA']) 		? $cheque['CUENTA'] 		: null;
	if (!empty($fecha)) {
		$dateTime = DateTime::createFromFormat('Y-m-d', $fecha);
		if ($dateTime) {
			$fecha = $dateTime->format('Y-m-d');
		} else {
			$fecha = null;
		}
	} else {
		$fecha = null;
	}
	$monto       = isset($cheque['MONTO']) 			? $cheque['MONTO'] 			: null;

	if ($monto !== null) {
		$monto = preg_replace('/[^\d]/', '', $monto);
	}

	/* Conciliamos todos los asignados que hagan match en la cartola y esté canalizado por cheque */
	if ($tipo_canal == 1 && $n_cheque == $n_documento) {

		$sql_remesa = "{call [_SP_CONCILIACIONES_ASIGNACIONES_CHEQUES_ACTUALIZA](?, ?)}";
		$params_remesa = array(
			array($id_asignacion,   	SQLSRV_PARAM_IN),
			array($idusuario,     		SQLSRV_PARAM_IN),
		);
		$stmt_remesa = sqlsrv_query($conn, $sql_remesa, $params_remesa);
		if ($stmt_remesa === false) {
			die(print_r(sqlsrv_errors(), true));
		}

		// Insertar en conciliación
		$sql_conciliacion = "{call [_SP_CONCILIACIONES_CONCILIACION_INSERTAR](?, ?, ?, ?, ?, ?, ?)}";
		$params_conciliacion = array(
			array($n_documento,          SQLSRV_PARAM_IN),
			array($cuenta,               SQLSRV_PARAM_IN),
			array($fecha,                SQLSRV_PARAM_IN),
			array($n_cheque,             SQLSRV_PARAM_IN),
			array($tipo_canal,           SQLSRV_PARAM_IN),
			array($monto,                SQLSRV_PARAM_IN),
			array($idusuario,            SQLSRV_PARAM_IN)
		);
		$stmt_conciliacion = sqlsrv_query($conn, $sql_conciliacion, $params_conciliacion);
		if ($stmt_conciliacion === false) {
			echo "Error in executing statement conciliacion.\n";
			die(print_r(sqlsrv_errors(), true));
		}
	}
}


//INSERTAR OPERACION
//ACTUALIZAR CARGA
header("Location: cargas_cartola_bancaria.php?op=4");
