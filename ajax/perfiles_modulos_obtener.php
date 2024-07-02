<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('conexiones.php');

$perfil = $_POST["modulo"];

try {
	$sql = "EXEC [_SP_SJUD_PERFIL_MODULO_NO_ASIGNADO] '$perfil'";
	$stmt = sqlsrv_query( $conn, $sql );
	//echo $sql ;	
	if( $stmt === false) {	
		die( print_r( sqlsrv_errors(), true) );
	}                               
} catch (PDOException $ex) {
	
	echo "Error Pagina : usuarios.php";
	exit;
};

$html = "<option value='0'>Seleccione un Modulo</option>";

$stmt = sqlsrv_query( $conn, $sql );
//echo $sql ;	
if( $stmt === false) {	
	die( print_r( sqlsrv_errors(), true) );
}                                        
while( $datos = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) { 
	$html .= "<option value='".$datos["ID_MODULO"]."'>".$datos["DESCRIPCION"]."</option>";	
}

echo $html;
?>