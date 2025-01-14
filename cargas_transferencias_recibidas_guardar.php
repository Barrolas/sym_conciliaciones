<?php
session_start();
include("permisos_adm.php");
include("funciones.php");
include("error_view.php");
include("conexiones.php");
validarConexion($conn);  
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

$idusuario = $_SESSION['ID_USUARIO'] ?? null;

if (!$idusuario) {
    mostrarError("No se pudo identificar al usuario. Por favor, inicie sesión nuevamente.");
}

// Verificar archivo cargado
if ($_FILES['archivo']['name'] != '') {
    $arr = explode(".", $_FILES['archivo']['name']);
    $extension = $arr[1] ?? '';
    $nombre_archivo = 'TransferenciasRecibidas.xlsx';

    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], 'archivos\\' . $nombre_archivo)) {
        mostrarError("No se pudo cargar el archivo. Verifique los permisos de escritura en el servidor.");
    }
} else {
    mostrarError("No se seleccionó ningún archivo para cargar.");
}

// Ejecutar SP para cargar datos del archivo
$stmt_carga_transferencias = "{call [_SP_CONCILIACIONES_CARGA_CARTOLA_TRANSFERENCIAS]}";
$carga_transferencias_result = sqlsrv_query($conn, $stmt_carga_transferencias);
if ($carga_transferencias_result === false) {
    mostrarError("Error en stmt_carga_transferencias | No se pudo cargar los datos de las transferencias en la base de datos.");
}

// Redirigir al finalizar
header("Location: cargas_transferencias_recibidas.php?op=4");
?>
