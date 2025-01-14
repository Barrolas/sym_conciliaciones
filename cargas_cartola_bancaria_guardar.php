<?php
session_start();
ob_start(); // Inicia el buffer de salida

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

if ($_FILES['archivo']['name'] != '') {
	$arr = explode(".", $_FILES['archivo']['name']);
	$extension = $arr[1] ?? '';
	$nombre_archivo = 'CartolaBancaria.xlsx';

	if (!move_uploaded_file($_FILES['archivo']['tmp_name'], 'archivos\\' . $nombre_archivo)) {
		mostrarError("Error al subir el archivo: " . $_FILES['archivo']['error']);
	}
}

// Cargar cartola bancaria
$sql1 = "{call [_SP_CONCILIACIONES_CARGA_CARTOLA_BCO]}";
$stmt1 = sqlsrv_query($conn, $sql1);
if ($stmt1 === false) {
	$sqlErrors = sqlsrv_errors();
	mostrarError("Error en la conexión al servidor. Detalle: " . print_r($sqlErrors, true));
}

// Consultar asignados
$estado1 = 2;
$estado2 = 2;
$sql_asign = "EXEC [_SP_CONCILIACIONES_ASIGNADOS_LISTA] ?, ?";
$params_asign = [
	[$estado1, SQLSRV_PARAM_IN],
	[$estado2, SQLSRV_PARAM_IN],
];
$stmt_asign = sqlsrv_query($conn, $sql_asign, $params_asign);
if ($stmt_asign === false) {
	mostrarError("Error en la conexión al servidor. No se pudo obtener la lista de asignaciones.");
}

while ($asignados = sqlsrv_fetch_array($stmt_asign, SQLSRV_FETCH_ASSOC)) {
	$id_asignacion = $asignados['ID_ASIGNACION'];
	$tipo_canal = $asignados['ID_TIPO_CANALIZACION'];
	$n_cheque = $asignados['N_CHEQUE'];

	$sql_ch = "EXEC [_SP_CONCILIACIONES_CARTOLA_CHEQUES_CONSULTA] ?";
	$params_ch = [
		[$n_cheque, SQLSRV_PARAM_IN],
	];
	$stmt_ch = sqlsrv_query($conn, $sql_ch, $params_ch);
	if ($stmt_ch === false) {
		mostrarError("Error al consultar los datos del cheque.");
	}

	$cheque = sqlsrv_fetch_array($stmt_ch, SQLSRV_FETCH_ASSOC);
	$n_documento = $cheque['N_DOCUMENTO'] ?? null;
	$cuenta = $cheque['CUENTA'] ?? null;

	if ($tipo_canal == 1 && $n_cheque == $n_documento) {
		$cheque_resultado = 0;

		$sql_remesa = "{call [_SP_CONCILIACIONES_ASIGNACIONES_CHEQUES_ACTUALIZA](?, ?, ?, ?)}";
		$params_remesa = [
			[$id_asignacion, SQLSRV_PARAM_IN],
			[$n_cheque, SQLSRV_PARAM_IN],
			[$idusuario, SQLSRV_PARAM_IN],
			[&$cheque_resultado, SQLSRV_PARAM_INOUT],
		];
		$stmt_remesa = sqlsrv_query($conn, $sql_remesa, $params_remesa);
		if ($stmt_remesa === false) {
			mostrarError("Error al actualizar la asignación del cheque.");
		}

		$sql_conciliacion = "{call [_SP_CONCILIACIONES_CONCILIACION_INSERTAR](?, ?, ?, ?, ?, ?, ?)}";
		$params_conciliacion = [
			[$n_documento, SQLSRV_PARAM_IN],
			[$cuenta, SQLSRV_PARAM_IN],
			[$fecha, SQLSRV_PARAM_IN],
			[$n_cheque, SQLSRV_PARAM_IN],
			[$tipo_canal, SQLSRV_PARAM_IN],
			[$monto, SQLSRV_PARAM_IN],
			[$idusuario, SQLSRV_PARAM_IN],
		];
		$stmt_conciliacion = sqlsrv_query($conn, $sql_conciliacion, $params_conciliacion);
		if ($stmt_conciliacion === false) {
			mostrarError("Error al insertar los datos de la conciliación.");
		}
	}
}

header("Location: cargas_cartola_bancaria.php?op=4");
ob_end_flush(); // Envía el contenido del buffer y lo limpia
