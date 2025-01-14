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
    $nombre_archivo = 'InformeRecaudaciones.xls';

    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], 'archivos\\' . $nombre_archivo)) {
        mostrarError("No se pudo cargar el archivo. Verifique los permisos de escritura en el servidor.");
    }
} else {
    mostrarError("No se seleccionó ningún archivo para cargar.");
}

// Ejecutar SP para cargar datos del archivo
$stmt_carga = "{call [_SP_CONCILIACIONES_CARGA_CARTOLA_SISREC]}";
$carga_result = sqlsrv_query($conn, $stmt_carga);
if ($carga_result === false) {
    mostrarError("Error en stmt_carga | No se pudo cargar los datos del archivo en la base de datos.");
}

// Consultar asignaciones
$estado1 = '1';
$estado2 = '1';
$stmt_asign = "EXEC [_SP_CONCILIACIONES_ASIGNADOS_LISTA] ?, ?";
$params_asign = [
    [$estado1, SQLSRV_PARAM_IN],
    [$estado2, SQLSRV_PARAM_IN],
];
$asign_result = sqlsrv_query($conn, $stmt_asign, $params_asign);
if ($asign_result === false) {
    mostrarError("Error en stmt_asign | No se pudieron consultar las asignaciones.");
}

// Procesar asignaciones
while ($asignados = sqlsrv_fetch_array($asign_result, SQLSRV_FETCH_ASSOC)) {
    $id_asignacion 	= $asignados['ID_ASIGNACION'];
    $tipo_canal 	= $asignados['ID_TIPO_CANALIZACION'];

    if ($tipo_canal == 2) {
        $stmt_remesa = "{call [_SP_CONCILIACIONES_ASIGNACIONES_REMESAS_ACTUALIZA](?, ?)}";
        $params_remesa = [
            [$id_asignacion, 	SQLSRV_PARAM_IN],
            [$idusuario, 		SQLSRV_PARAM_IN],
        ];
        $remesa_result = sqlsrv_query($conn, $stmt_remesa, $params_remesa);
        if ($remesa_result === false) {
            mostrarError("Error en stmt_remesa | No se pudo actualizar la asignación de remesas.");
        }
    }
}

// Redirigir al finalizar
header("Location: cargas_sisrec.php?op=4");
?>
