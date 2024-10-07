<?php
error_reporting(0);
session_start();
if ($_SESSION["ID_USUARIO"] == ''){
	header("Location: login.php");
	exit;
};

?>