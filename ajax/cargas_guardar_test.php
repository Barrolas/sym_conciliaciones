<?php
session_start();
include("funciones.php");
include("conexiones.php");
//include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$tsql_callSP = "{call [_SP_SJUD_CARGA_INSERTAR]( ?, ?, ?)}";	

$idcliente      = 4;  
$idusuario      = 5;  
$idcarga        = 0;  

$params = array(   
                 array($idcliente, SQLSRV_PARAM_IN),  
                 array($idusuario, SQLSRV_PARAM_IN),
                 array(&$idcarga, SQLSRV_PARAM_INOUT)  
               );  
  
/* Execute the query. */  
$stmt3 = sqlsrv_query( $conn, $tsql_callSP, $params);  
if( $stmt3 === false )  
{  
     echo "Error in executing statement 3.\n";  
     die( print_r( sqlsrv_errors(), true));  
}  
  
/* Display the value of the output parameter $vacationHrs. */  
sqlsrv_next_result($stmt3);  
echo "Remaining vacation hours: ".$idcarga;  

?>
