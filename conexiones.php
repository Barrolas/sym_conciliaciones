<?php
// SET UP CONEXION SQL SERVER / HADES
$serverName = "192.168.101.15\RECOVER"; 
$connectionInfo = array( "Database"=>"sjud", "UID"=>"sa", "PWD"=>"Hendrix1966.", "CharacterSet" => "UTF-8");
$conn           = sqlsrv_connect( $serverName, $connectionInfo);

?>