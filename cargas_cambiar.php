<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

$id	= $_GET["id"];

$sql = "EXEC [_SP_SJUD_CARGA_CAMBIA_ESTADO] $id";
$stmt = sqlsrv_query( $conn, $sql );
//echo $sql ;	
if( $stmt === false) {	
	die( print_r( sqlsrv_errors(), true) );
}      

header("Location: cargas.php?op=3");

?>