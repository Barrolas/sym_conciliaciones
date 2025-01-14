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

$html = '';

$rut_deudor = $_POST['rut_deudor'];

$sql = "EXEC [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES] ?";
$params = array($rut_deudor);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

while ($detalle = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $estado_doc = $detalle["ESTADO_DOC"];

    // Asignar descripciones según el valor de ESTADO_DOC
    switch ($estado_doc) {
        case '001':
            $estado_doc_text = 'VIGENTE';
            break;
        case '014':
            $estado_doc_text = 'PAGADO';
            break;
        case '333':
            $estado_doc_text = 'NO VIGENTE';
            break;
        default:
            $estado_doc_text = $estado_doc;  // Mantener el valor numérico por defecto
            break;
    }

    $f_venc = $detalle["F_VENC"];
    if ($f_venc instanceof DateTime) {
        $f_venc = $f_venc->format('Y-m-d');  // Ajusta el formato de fecha según tus necesidades
    }

    $html .= '<tr>';
    $html .= '<td class="f_venc" id="f_venc">' . $f_venc . '</td>';
    $html .= '<td class="valor_cuota" id="valor_cuota" style="display: none;">' . $detalle["MONTO"] . '</td>';
    $html .= '<td class="valor_cuota2" id="valor_cuota2">$' . number_format($detalle["MONTO"], 0, ',', '.') . '</td>';
    $html .= '<td class="n_doc" id="n_doc">' . htmlspecialchars($detalle["N_DOC"]) . '</td>';
    $html .= '<td class="rut_cliente" id="rut_cliente">' . $detalle["RUT_CLIENTE"] . '</td>';
    $html .= '<td class="nom_cliente" id="nom_cliente">' . $detalle["NOM_CLIENTE"] . '</td>';
    $html .= '<td class="estado_doc" id="estado_doc">' . $estado_doc_text . '</td>';
    $html .= '<td style="text-align: center;"><input type="checkbox" class="valor-checkbox" onchange="checks(this)"></td>';
    $html .= '</tr>';
}

echo $html;
?>
