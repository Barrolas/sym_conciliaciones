<?php
session_start();
include("funciones.php");
include("conexiones.php");
//include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$idusuario 			= $_SESSION['ID_USUARIO'];
$idcliente 			= $_POST['idcliente'];
$idcliente_deudor 	= 0;



if ($_FILES['archivo']['name'] != '') {
	// Datos del archivo
	$arr = explode(".", $_FILES['archivo']['name']);
	$extension = end($arr);
	$nombre_archivo = generateRandomString(30) . '.' . $extension;
	$tipo_archivo = $_FILES['archivo']['type'];
	$tamano_archivo = $_FILES['archivo']['size'];

	// Compruebo si las características del archivo son las que deseo
	if (!((strpos($tipo_archivo, "xls") || strpos($tipo_archivo, "xlsx") || strpos($tipo_archivo, "sheet")) && ($tamano_archivo < 60000000))) {
		$err = 1;
	} else {
		if (!move_uploaded_file($_FILES['archivo']['tmp_name'], 'archivos/' . $nombre_archivo)) {
			$err = 2;
		}
	}
}

require_once('phpexcel2/vendor/autoload.php');

$allowedFileType = [
	'application/vnd.ms-excel',
	'text/xls',
	'text/xlsx',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

if (in_array($_FILES["archivo"]["type"], $allowedFileType)) {
	$targetPath = 'archivos/' . $nombre_archivo;

	$Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

	$spreadSheet 			= $Reader->load($targetPath);
	$excelSheet 			= $spreadSheet->getActiveSheet();
	$spreadSheetAry 		= $excelSheet->toArray();
	$sheetCount 			= count($spreadSheetAry);

	$numeroMayorDeFila 		= $excelSheet->getHighestRow(); 	// Numérico
	$letraMayorDeColumna 	= $excelSheet->getHighestColumn(); 	// Letra

	if ($letraMayorDeColumna != 'AM') {
		header("Location: cargas.php?op=6");
		exit;
	}

	if ($numeroMayorDeFila <= 2) {
		header("Location: cargas.php?op=6");
		exit;
	}


	$leidos 	= 0;
	$cargados 	= 0;
	$error 		= 0;


	//====== SP para crear la carga =======
	$sql1 = "{call [_SP_SJUD_CARGA_INSERTA]( ?, ?, ?)}";

	$idcarga = 0;

	$params1 = array(
		array($idcliente, 	SQLSRV_PARAM_IN),
		array($idusuario, 	SQLSRV_PARAM_IN),
		array(&$idcarga, 	SQLSRV_PARAM_INOUT)
	);

	$stmt1 = sqlsrv_query($conn, $sql1, $params1);
	if ($stmt1 === false) {
		echo "Error in executing statement 1.\n";
		die(print_r(sqlsrv_errors(), true));
	}

	sqlsrv_next_result($stmt1);
	//	echo $idcarga;
	//	exit;

	// ======= Proceso por filas =======
	for ($i = 1; $i < $sheetCount; $i++) {
		$deud_rut 					= $spreadSheetAry[$i][0];
		$deud_apellido1 			= $spreadSheetAry[$i][1];
		$deud_apellido2 			= $spreadSheetAry[$i][2];
		$deud_nombre 				= $spreadSheetAry[$i][3];
		$deud_dir_part 				= $spreadSheetAry[$i][4];
		$deud_dir_part_comp 		= $spreadSheetAry[$i][5];
		$deud_comuna_part 			= $spreadSheetAry[$i][6];
		$deud_dir_com 				= $spreadSheetAry[$i][7];
		$deud_dir_com_comp 			= $spreadSheetAry[$i][8];
		$deud_comuna_com 			= $spreadSheetAry[$i][9];
		$deud_fono_part 			= $spreadSheetAry[$i][10];
		$deud_fono_com 				= $spreadSheetAry[$i][11];
		$deud_fono_cel 				= $spreadSheetAry[$i][12];
		$tipo_doc 					= $spreadSheetAry[$i][13];
		$num_doc 					= $spreadSheetAry[$i][14];
		$capital 					= $spreadSheetAry[$i][15];
		$abonos 					= $spreadSheetAry[$i][16];
		$totalmora 					= $spreadSheetAry[$i][17];
		$fvenc 						= $spreadSheetAry[$i][18];
		$fprotesto 					= $spreadSheetAry[$i][19];
		$motivo_cobr 				= $spreadSheetAry[$i][20];
		$gira_rut 					= $spreadSheetAry[$i][21];
		$gira_apellido1 			= $spreadSheetAry[$i][22];
		$gira_apellido2 			= $spreadSheetAry[$i][23];
		$gira_nombre 				= $spreadSheetAry[$i][24];
		$gira_fono_part 			= $spreadSheetAry[$i][25];
		$gira_fono_cel 				= $spreadSheetAry[$i][26];
		$gira_fono_com 				= $spreadSheetAry[$i][27];
		$gira_dir 					= $spreadSheetAry[$i][28];
		$gira_dir_comp 				= $spreadSheetAry[$i][29];
		$gira_comuna 				= $spreadSheetAry[$i][30];
		$banco 						= $spreadSheetAry[$i][31];
		$banco_plaza 				= $spreadSheetAry[$i][32];
		$cta_corriente 				= $spreadSheetAry[$i][33];
		$banco_suc 					= $spreadSheetAry[$i][34];
		$correo 					= $spreadSheetAry[$i][35];
		$bp 						= $spreadSheetAry[$i][36];
		$url_factura 				= $spreadSheetAry[$i][37];

		//======= Validaciones =======

		// Boolean para validaciones
		$invalid 	= false;
		$detalle_error = '';

		// Validación de RUTs (7 u 8 dígitos, sin puntos, guiones ni espacios)
		if (!validar_rut($deud_rut)) {
			$invalid = true;
			$detalle_error .= ' RUT deudor erróneo; ';
			echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;
		}
		if (!validar_rut($gira_rut)) {
			$invalid = true;
			$detalle_error .= ' RUT girador erróneo; ';
			echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;
		}


		// Validaciones de campos obligatorios
		if (empty($deud_rut)) {
			$invalid = true;
			$detalle_error .= ' RUT deudor debe tener un valor; ';
			echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;
		}
		if (empty($gira_rut)) {
			$invalid = true;
			$detalle_error .= ' RUT girador debe tener un valor; ';
			echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;
		}
		if (empty($fvenc) && empty($fprotesto)) {
			$invalid = true;
			$detalle_error .= ' Debe ingresar fecha de vencimiento o protesto; ';
			echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;
		} elseif (!empty($fvenc) && !empty($fprotesto)) {
			$invalid = true;
			$detalle_error .= ' No pueden ingresar ambas fechas (vencimiento y protesto); ';
			echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;
		}
		if (empty($totalmora)) {
			$invalid = true;
			$detalle_error .= ' Debe ingresar el total mora; ';
			echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;
		}
		if (empty($tipo_doc)) {
			$invalid = true;
			$detalle_error .= ' Tipo de documento debe tener un valor; ';
			echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;

			
		} else {
			// Validación del tipo de documento
			$es_valido = 0;
			$error_tipo = 0;

			$sql10 = "{CALL _SP_SJUD_DOCUMENTO_VALIDA(?, ?, ?, ?, ?, ?)}";
			$params10 = array(
				array($tipo_doc, 	SQLSRV_PARAM_IN),
				array($num_doc, 	SQLSRV_PARAM_IN),
				array($deud_rut, 	SQLSRV_PARAM_IN),
				array($idcliente, 	SQLSRV_PARAM_IN),
				array(&$es_valido, 	SQLSRV_PARAM_OUT),
				array(&$error_tipo, SQLSRV_PARAM_OUT)
			);

			$stmt10 = sqlsrv_query($conn, $sql10, $params10);

			if ($stmt10 === false) {
				die(print_r(sqlsrv_errors(), true));
			}

			if ($es_valido === 0) {
				$invalid = true;
				if ($error_tipo === 1) {
					$detalle_error .= ' El tipo de documento proporcionado no existe;';
				} elseif ($error_tipo === 2) {
					$detalle_error .= ' Ya existe un número de documento asociado a este tipo de documento, deudor y cliente;';
				} else {
					$detalle_error .= ' Error desconocido;';
				}
				echo 'Fila: ' . ($i + 1) . ' Error: ' . $detalle_error;
			}
			sqlsrv_free_stmt($stmt10);
		} //Cierre ELSE validaciones

		if ($invalid) { //Carga de rechazados

			$error++;

			$sql11 = "{CALL _SP_SJUD_CARGA_RECHAZO_INSERTA(?, ?, ?, ?)}";
			$params11 = array(

				array($idcarga, 		SQLSRV_PARAM_IN),
				array(($i + 1), 		SQLSRV_PARAM_IN),
				array($detalle_error, 	SQLSRV_PARAM_IN),
				array($idusuario, 		SQLSRV_PARAM_IN)

			);

			$stmt11 = sqlsrv_query($conn, $sql11, $params11);

			if ($stmt11 === false) {
				echo "Error in executing statement 11.\n";
				die(print_r(sqlsrv_errors(), true));
			}

			sqlsrv_free_stmt($stmt11);
		} else { //======= Comienzo de INSERTs y respectivos condicionales =======


			//======= DEUDORES INSERT =======

			//SP para insertar cada -DEUDOR- que cumpla la validación
			$sql2 = "{call [_SP_SJUD_DEUDOR_INSERTA]( ?, ?, ?, ?, ?, ?, ?)}";

			$params2 = array(
				array($idcliente, 			SQLSRV_PARAM_IN),
				array($deud_rut, 			SQLSRV_PARAM_IN),
				array($deud_nombre, 		SQLSRV_PARAM_IN),
				array($deud_apellido1, 		SQLSRV_PARAM_IN),
				array($deud_apellido2, 		SQLSRV_PARAM_IN),
				array($idusuario, 			SQLSRV_PARAM_IN),
				array(&$idcliente_deudor, 	SQLSRV_PARAM_INOUT)
			);

			$stmt2 = sqlsrv_query($conn, $sql2, $params2);
			if ($stmt2 === false) {
				echo "Error in executing statement 2.\n";
				die(print_r(sqlsrv_errors(), true));
			}
			sqlsrv_next_result($stmt2);
			//			echo $sql2;

			//======= DIRECCIONES INSERT =======

			//IF para validar si la direccion particular viene vacía
			if (!empty($deud_dir_part)) {

				$tipo_direccion = 1;

				//SP para insertar la -DIRECCIÓN PARTICULAR- del deudor
				$sql3 = "{call [_SP_SJUD_DIRECCION_INSERTA]( ?, ?, ?, ?, ?, ?)}";

				$params3 = array(

					array($deud_dir_part, 		SQLSRV_PARAM_IN),
					array($deud_dir_part_comp, 	SQLSRV_PARAM_IN),
					array($deud_comuna_part, 	SQLSRV_PARAM_IN),
					array($tipo_direccion, 		SQLSRV_PARAM_IN),
					array($idusuario, 			SQLSRV_PARAM_IN),
					array($idcarga, 			SQLSRV_PARAM_IN)

				);

				$stmt3 = sqlsrv_query($conn, $sql3, $params3);
				if ($stmt3 === false) {
					echo "Error in executing statement 3.\n";
					die(print_r(sqlsrv_errors(), true));
				}
				sqlsrv_next_result($stmt3);
				//				echo $sql3;
			}

			//IF para validar si la direccion particular viene vacía
			if (!empty($deud_dir_com)) {

				$tipo_direccion = 2;

				//SP para insertar la -DIRECCIÓN COMERCIAL- del deudor
				$sql4 = "{call [_SP_SJUD_DIRECCION_INSERTA]( ?, ?, ?, ?, ?, ?)}";

				$params4 = array(

					array($deud_dir_com, 		SQLSRV_PARAM_IN),
					array($deud_dir_com_comp, 	SQLSRV_PARAM_IN),
					array($deud_comuna_com, 	SQLSRV_PARAM_IN),
					array($tipo_direccion, 		SQLSRV_PARAM_IN),
					array($idusuario, 			SQLSRV_PARAM_IN),
					array($idcarga, 			SQLSRV_PARAM_IN)

				);

				$stmt4 = sqlsrv_query($conn, $sql4, $params4);
				if ($stmt4 === false) {
					echo "Error in executing statement 4.\n";
					die(print_r(sqlsrv_errors(), true));
				}
				sqlsrv_next_result($stmt4);
				//				echo $sql4;
			}

			//======= TELEFONOS INSERT =======

			//IF para validar si el -FONO PARTICULAR- viene vacío
			if (!empty($deud_fono_part)) {
				//echo "Valor de deud_fono_part: " . $deud_fono_part . "<br>";

				$tipo_telefono = 1;

				//SP para insertar -FONO PARTICULAR- del deudor
				$sql5 = "{call [_SP_SJUD_CARGA_TELEFONOS]( ?, ?, ?, ?, ?)}";

				$params5 = array(

					array($idcliente_deudor, 	SQLSRV_PARAM_IN),
					array($deud_fono_part, 	    SQLSRV_PARAM_IN),
					array($tipo_telefono, 		SQLSRV_PARAM_IN),
					array($idcarga, 			SQLSRV_PARAM_IN),
					array($idusuario, 			SQLSRV_PARAM_IN)

				);

				$stmt5 = sqlsrv_query($conn, $sql5, $params5);
				if ($stmt5 === false) {
					echo "Error in executing statement 5.\n";
					die(print_r(sqlsrv_errors(), true));
				}
				sqlsrv_next_result($stmt5);
				//				echo $sql5;
			}

			//IF para validar si el -FONO COMERCIAL- viene vacío
			if (!empty($deud_fono_com)) {

				$tipo_telefono = 2;

				//SP para insertar -FONO COMERCIAL- del deudor
				$sql6 = "{call [_SP_SJUD_CARGA_TELEFONOS]( ?, ?, ?, ?, ?)}";

				$params6 = array(

					array($idcliente_deudor, 	SQLSRV_PARAM_IN),
					array($deud_fono_com, 	    SQLSRV_PARAM_IN),
					array($tipo_telefono, 		SQLSRV_PARAM_IN),
					array($idcarga, 			SQLSRV_PARAM_IN),
					array($idusuario, 			SQLSRV_PARAM_IN)

				);

				$stmt6 = sqlsrv_query($conn, $sql6, $params6);
				if ($stmt6 === false) {
					echo "Error in executing statement 6.\n";
					die(print_r(sqlsrv_errors(), true));
				}
				sqlsrv_next_result($stmt6);
				//				echo $sql6;
			}

			//IF para validar si el -FONO CELULAR- viene vacío
			if (!empty($deud_fono_cel)) {

				$tipo_telefono = 3;

				//SP para insertar -FONO CELULAR- del deudor
				$sql7 = "{call [_SP_SJUD_CARGA_TELEFONOS]( ?, ?, ?, ?, ?)}";

				$params7 = array(

					array($idcliente_deudor, 	SQLSRV_PARAM_IN),
					array($deud_fono_cel, 	    SQLSRV_PARAM_IN),
					array($tipo_telefono, 		SQLSRV_PARAM_IN),
					array($idcarga, 			SQLSRV_PARAM_IN),
					array($idusuario, 			SQLSRV_PARAM_IN)

				);

				$stmt7 = sqlsrv_query($conn, $sql7, $params7);
				if ($stmt7 === false) {
					echo "Error in executing statement 7.\n";
					die(print_r(sqlsrv_errors(), true));
				}
				sqlsrv_next_result($stmt7);
				//				echo $sql7;
			}

			//======= DOCUMENTOS INSERT =======

			//SP para insertar -DOCUMENTO- del deudor (29 parametros)
			$sql8 = "{call [_SP_SJUD_DOCUMENTO_INSERTA](
					?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";

			$params8 = array(

				array($idcliente_deudor, 	SQLSRV_PARAM_IN),
				array($tipo_doc, 			SQLSRV_PARAM_IN),
				array($idcarga,				SQLSRV_PARAM_IN),
				array($num_doc,				SQLSRV_PARAM_IN),
				array($capital,				SQLSRV_PARAM_IN),
				array($abonos,				SQLSRV_PARAM_IN),
				array($totalmora,			SQLSRV_PARAM_IN),
				array($fvenc,				SQLSRV_PARAM_IN),
				array($fprotesto,			SQLSRV_PARAM_IN),
				array($gira_rut,			SQLSRV_PARAM_IN),
				array($gira_apellido1,		SQLSRV_PARAM_IN),
				array($gira_apellido2,		SQLSRV_PARAM_IN),
				array($gira_nombre,			SQLSRV_PARAM_IN),
				array($gira_fono_part,		SQLSRV_PARAM_IN),
				array($gira_fono_com,		SQLSRV_PARAM_IN),
				array($gira_fono_cel,		SQLSRV_PARAM_IN),
				array($gira_dir,			SQLSRV_PARAM_IN),
				array($gira_dir_comp,		SQLSRV_PARAM_IN),
				array($gira_comuna,			SQLSRV_PARAM_IN),
				array($banco,				SQLSRV_PARAM_IN),
				array($banco_plaza,			SQLSRV_PARAM_IN),
				array($cta_corriente,		SQLSRV_PARAM_IN),
				array($banco_suc,			SQLSRV_PARAM_IN),
				array($correo,				SQLSRV_PARAM_IN), //Este campo debe ir a un SP de insercion a sjud_correos
				array($bp,   				SQLSRV_PARAM_IN),
				array($url_factura,			SQLSRV_PARAM_IN),
				array($motivo_cobr,			SQLSRV_PARAM_IN),
				array($idusuario,			SQLSRV_PARAM_IN),

			);

			$stmt8 = sqlsrv_query($conn, $sql8, $params8);
			if ($stmt8 === false) {
				echo "Error in executing statement 8.\n";
				die(print_r(sqlsrv_errors(), true));
			}
			sqlsrv_free_stmt($stmt8);

			$cargados++;
		} // Fin de ELSE de INSERT de validados

		$leidos++;

		echo "Registros leídos: 	$leidos<br>";
		echo "Registros cargados: 	$cargados<br>";
		echo "Registros con error: 	$error<br>";
		//exit;

		//SP para ACTUALIZAR datos de la carga
		$sql9 = "{call [_SP_SJUD_CARGA_ACTUALIZA]( ?, ?, ?, ?, ?, ?, ?)}";

		$params9 = array(

			array($idcarga, 		SQLSRV_PARAM_IN),
			array($idcliente, 		SQLSRV_PARAM_IN),
			array($leidos, 			SQLSRV_PARAM_IN),
			array($cargados, 		SQLSRV_PARAM_IN),
			array($error, 			SQLSRV_PARAM_IN),
			array($nombre_archivo, 	SQLSRV_PARAM_IN),
			array($idusuario, 		SQLSRV_PARAM_IN)

		);

		$stmt9 = sqlsrv_query($conn, $sql9, $params9);
		if ($stmt9 === false) {
			echo "Error in executing statement 9.\n";
			die(print_r(sqlsrv_errors(), true));
		}
		sqlsrv_next_result($stmt9);
		//		echo $sql9;
	} // Fin FOR

	//exit;

} else { //Fin validador de Excel
	header("Location: cargas.php?op=6");
	exit;
}

header("Location: cargas.php?op=1");
