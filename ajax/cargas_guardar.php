<?php
session_start();
include("funciones.php");
include("conexiones.php");
//include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_FILES['archivo']['name'] != '' ) {
	//datos del arhivo
	$arr			= explode(".", $_FILES['archivo']['name']);
	$extension		= $arr[1]; 
	$nombre_archivo = generateRandomString(20).'.'.$extension;
	$tipo_archivo 	= $_FILES['archivo']['type'];
	$tamano_archivo = $_FILES['archivo']['size'];
	//echo $tipo_archivo ."<BR>";
	//compruebo si las características del archivo son las que deseo
	
	if (!((strpos($tipo_archivo, "xls") || strpos($tipo_archivo, "xlsx") || strpos($tipo_archivo, "sheet"  )) && ($tamano_archivo < 60000000))) {
		
		$err=1;
	} else {
		if (!move_uploaded_file($_FILES['archivo']['tmp_name'],  'archivos/'.$nombre_archivo)){
			
			$err=2;
		} 
	}
};
// echo "NOMBRE : ".$err.' '.$nombre_archivo;exit;
$sql ="INSERT INTO `los_cargas`
		(  `FECHA_CARGA`, `ID_USUARIO`, `ARCHIVO`) VALUES 
		('$hoy','".$_SESSION["ID_USUARIO"]."','$nombre_archivo')";

// echo $sql;

$conn->query($sql);
$last_id 	= $conn->lastInsertId();

require_once ('phpexcel2/vendor/autoload.php');

$allowedFileType = [
'application/vnd.ms-excel',
'text/xls',
'text/xlsx',
'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

if (in_array($_FILES["archivo"]["type"], $allowedFileType)) {
	$targetPath = 'archivos/' . $nombre_archivo;

	$Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

	$spreadSheet 	= $Reader->load($targetPath);
	$excelSheet 	= $spreadSheet->getActiveSheet();
	$spreadSheetAry = $excelSheet->toArray();
	$sheetCount 	= count($spreadSheetAry);
	
	$numeroMayorDeFila = $excelSheet->getHighestRow(); // Numérico
    $letraMayorDeColumna = $excelSheet->getHighestColumn(); // Letra
	
	if ($letraMayorDeColumna <> 'O') {
		header("Location: cargas.php?op=6");
		exit;
	};
	
	if ($numeroMayorDeFila <= 2) {
		header("Location: cargas.php?op=6");
		exit;
	};
	
	
	// echo $sheetCount."<BR>";
	for ($i = 1; $i <= $sheetCount-1; $i ++) {		
			$rut 		= $spreadSheetAry[$i][0];
			$dv 		= $spreadSheetAry[$i][1];
			$nombre 	= $spreadSheetAry[$i][2];
			$contencion = $spreadSheetAry[$i][3];
			$saldomora	= $spreadSheetAry[$i][4]; 
			$cuota 		= $spreadSheetAry[$i][5];			
			$fvenc 		= $spreadSheetAry[$i][6];
			$fcastigo 	= $spreadSheetAry[$i][7];
			$direcc		= $spreadSheetAry[$i][8];
			$comuna		= $spreadSheetAry[$i][9];
			$region		= $spreadSheetAry[$i][10];
			$usuario 	= $spreadSheetAry[$i][11];
			$nomina		= $spreadSheetAry[$i][12];
			$campana	= $spreadSheetAry[$i][13];
			$rutcliente	= $spreadSheetAry[$i][14];
		   
			$sw = 0;
			$f1 = $fvenc;
			$f2 = $fcastigo;
			// echo "FVENC : ".$fvenc.'<BR>';
			if ($f2 == '') {
				// echo "FCAST : ".$fcastigo.'<BR>';
				$f2 = '01/01/1901';
				$fcastigo = $f2;
			};
			// echo $f1.' - '.$f2;
			// exit;
			if (strlen($f1) <= 10 and strlen($f2) <= 10) {	
				
				$fecha = explode("/", $fvenc);

				if ($fecha[0] < 10) {
					$dia = '0'.$fecha[0];
				} else {
					$dia = $fecha[0];
				};
				
				// echo "Dia :".$dia.'<BR>';
				if ($fecha[1] < 10) {
					$mes = '0'.$fecha[1];
				} else {
					$mes = $fecha[1];
				};
				
				// echo "mes :".$mes.'<BR>';
				$ano = substr($fvenc,-4);
				
				$ff1 = $ano.'-'.$dia.'-'.$mes;
				// echo $ff1;exit;
				$fecha = explode("/", $fcastigo);

				if ($fecha[0] < 10) {
					$dia = '0'.$fecha[0];
				} else {
					$dia = $fecha[0];
				};
				
				// echo "Dia :".$dia.'<BR>';
				if ($fecha[1] < 10) {
					$mes = '0'.$fecha[1];
				} else {
					$mes = $fecha[1];
				};
				
				// echo "mes :".$mes.'<BR>';
				$ano = substr($fcastigo,-4);
				
				$ff2 = $ano.'-'.$dia.'-'.$mes;
				// echo $fcastigo. ' - '.$ff2.'<BR>';exit;
							
			} else {
				$ff1 = '1901-01-01';
				$ff2 = '1901-01-01';	
			};
			
			
		
		// echo $rut. ' '.$dv. ' '.$nombre. ' '.$contencion. ' '.$saldomora. ' '.$monto. ' '.$cuota. ' '.$fvenc. ' '.$direcc. ' '.$comuna. ' '.$region. ' '.$nomina. ' '.$campana. ' '. '<BR> ';
			$sql5 = "INSERT INTO `los_cargas_detalle`
			(`ID_CARGA`, `USUARIO`, `NOMINA`, `RUT`, `DV`, `NOMBRE`, `CONTENCION`, `SALDO`, `CUOTA`, `F_VCTO_MENOR`, `F_CASTIGO`, `DIRECCION`, `COMUNA`, `REGION`, `USUARIO_ASIGNADO`, `CAMPANA`, `RUTCLIENTE`) VALUES 
			('$last_id','".$_SESSION["ID_USUARIO"]."','$nomina','$rut','$dv','$nombre','$contencion','$saldomora','$cuota','$ff1','$ff2','$direcc','$comuna','$region','$usuario','$campana','$rutcliente')";
			// echo $sql5.'<BR>';
			$conn->query($sql5);
			$last_id2 	= $conn->lastInsertId();
			
			if (strlen($f1) > 10 and strlen($f2) > 10) {	
			
				$sql4 = "INSERT INTO `los_cargas_rechazados`(
				`ID_CARGA_DETALLE`, `ID_TIPO_RECHAZO`) VALUES ('$last_id2','4')";
				// echo $sql4;
				$conn->query($sql4);
				$sw = 1;
			};
			// VALIDA REGION
			
			if ($region == 'NULL') {
				$sql4 = "INSERT INTO `los_cargas_rechazados`(
				`ID_CARGA_DETALLE`, `ID_TIPO_RECHAZO`) VALUES ('$last_id2','5')";
				$conn->query($sql4);
				$sw = 1;
			};
			// VALIDA RUT CLIENTE
			$sql3 = "SELECT ID_CLIENTE_SERVICIO 
						FROM `los_clientes_servicio` 
						WHERE RUT='$rutcliente'";
			//echo $sql3;
			$resultado3 = $conn->query($sql3);
			$campana1    = $resultado3->fetch();
			if (!$campana1) {
				$sql4 = "INSERT INTO `los_cargas_rechazados`(
				`ID_CARGA_DETALLE`, `ID_TIPO_RECHAZO`) VALUES ('$last_id2','1')";
				$conn->query($sql4);
				$sw = 1;
			};
		
			// VALIDA RUT DEUDOR-FECHA
			$sql7="SELECT * 
					FROM `los_usuarios_gestiones`
					where RUT='$rut' AND F_VCTO_MENOR = '$ff1' AND ID_GESTION=0 AND ESTADO=1 AND RUTCLIENTE='$rutcliente';";
			// echo $sql7."<BR>";
			
			$resultado7 = $conn->query($sql7);
			
			$valida     = $resultado7->fetch();
			
			if ($valida) {
				$sql4 = "INSERT INTO `los_cargas_rechazados`(
				`ID_CARGA_DETALLE`, `ID_TIPO_RECHAZO`) VALUES ('$last_id2','3')";
				$conn->query($sql4);
				$sw=1;
			};
		
			$sql3 = "SELECT ID_USUARIO 
						FROM `los_usuarios` 
						WHERE USUARIO='$usuario'";
			//echo $sql3;
			$resultado3 = $conn->query($sql3);
			$usuario1    = $resultado3->fetch();
			if (!$usuario1) {
				$sql4 = "INSERT INTO `los_cargas_rechazados`(
				`ID_CARGA_DETALLE`, `ID_TIPO_RECHAZO`) VALUES ('$last_id2','2')";
				$conn->query($sql4);
				$sw = 1;
			};
			
			if ($sw == 0) {
				$sql7 = "INSERT INTO `los_usuarios_gestiones`
				( `ID_CARGA_DETALLE`,`RUT`, `DV`, `NOMINA`, `NOMBRE`, `F_ASIGNACION`, `ID_GESTION`, `NOTAS`, `TELEFONO`, `ESTADO`, `CONTENCION`, `SALDO`, `CUOTA`, `F_VCTO_MENOR`, `F_CASTIGO`, `DIRECCION`, `COMUNA`, `REGION`, `USUARIO_ASIGNADO`, `CAMPANA`, `RUTCLIENTE`) VALUES 
				('$last_id2','$rut','$dv','$nomina','$nombre','$hoy','0','','','1','$contencion','$saldomora','$cuota','$ff1','$ff2','$direcc','$comuna','$region','$usuario','$campana','$rutcliente')";
				// echo $sql7."<BR>";
				// exit;
				$conn->query($sql7);
			}
		
	};
	// exit;
} else {
	header("Location: cargas.php?op=5");
	exit;
}
header("Location: cargas.php?op=4");
?>