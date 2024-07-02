<?php
session_start();
include("funciones.php");
include("conexiones.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$usuario= trim($_POST['usuario']);
$pass   = sha1(trim($_POST['pass']));
// echo 'ECHO : '.$pass.'<BR>'; 
$sql = "EXEC [_SP_SJUD_VALIDA_USUARIO] '$usuario','$pass'";

//echo $sql;

$stmt = sqlsrv_query( $conn, $sql );
//echo $sql ;	
if( $stmt === false) {	
	die( print_r( sqlsrv_errors(), true) );
}                                        
$elusuario = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ;

//print_r($elusuario);exit;
//  echo $usuario["RUT"];exit;

if (!$elusuario) {
	header("Location: login.php?op=1");
	exit;
};

// VALIDA SI ESTA HABILITADO PARA INGRESAR
if ($elusuario["ESTADO"] == '0') {
	header("Location: login.php?op=4");
	exit;
};

//echo $usuario["PASSWORD"]."<BR>";
//echo $pass."<BR>";
//echo sha1($pass)."<BR>";exit;

if ($elusuario["PASSWORD"] != (($pass))) {
	header("Location: login.php?op=2");
	exit;
};

$_SESSION["ID_USUARIO"]	= $elusuario["ID_USUARIO"];
$_SESSION["PERFIL"]		= $elusuario["ID_PERFIL"];
$_SESSION["NOMBRES"]	= $elusuario["NOMBRES"].' '.$elusuario["APELLIDOS"];
$_SESSION["TIPO"] = 1;
header("Location: menu_principal.php");
exit;

?>