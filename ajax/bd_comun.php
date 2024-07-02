<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../conexiones.php');

$usuario = $_GET["usuario"];

try {
	$sql = "EXEC [_SP_SJUD_USUARIO_CONSULTA_USUARIO] '$usuario'";
	$stmt = sqlsrv_query( $conn, $sql );
	//echo $sql ;	
	if( $stmt === false) {	
		die( print_r( sqlsrv_errors(), true) );
	}                               
} catch (PDOException $ex) {
	
	echo "Error Pagina : usuarios.php";
	exit;
};

$data = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ;

print json_encode($data);
?>