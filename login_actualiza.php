<?php
session_start();
include("funciones.php");
include("conexiones2.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pass = sha1(trim($_POST['pass']));
$pass1 = trim($_POST['pass1']);

$sql     = "select *
			from los_usuarios U			  
			where U.USUARIO = '" . $_SESSION["USER"] . "'";
// echo $sql;exit;
$resultado = $conn->query($sql);
$usuario   = $resultado->fetch();

if ($usuario) {
	$sql     = "update los_usuarios 			  
			  SET PASSWORD='$pass'
			  WHERE USUARIO = '" . $_SESSION["USER"] . "'";
	$conn->query($sql);
};

header("Location: login.php?op=6");
