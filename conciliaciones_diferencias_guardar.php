<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$op = isset($_GET["op"]) ? $_GET["op"] : 0;

//print_r($_POST['iddocumento_radio']);
//exit;

if (isset($_POST['iddocumento_radio'])) {

    // Creamos arreglos vacíos para almacenar los valores separados
    $id_documentos          = array();
    $prestamos              = array();
    $suma_prestamos         = 0;
    $cant_docs              = 0;

    // Recorremos cada valor del arreglo de checkboxes
    foreach ($_POST['iddocumento_radio'] as $checkbox_value) {

        // Dividimos el valor usando la coma como delimitador
        $valores = explode(',', $checkbox_value);

        // Verificamos que $valores tenga el número correcto de elementos
        if (count($valores) >= 1) {
            $id_documentos[]        = $valores[0];
            $prestamos[]            = $valores[1];
            $suma_prestamos        += $valores[1];
            $cant_docs++;
        }
    }
}

/*
print_r($id_documentos);
print_r($prestamos);
print_r($fechas_venc);
print_r($subproductos);
print_r($monto_pareodocs);
*/
//exit;

//print_r($cant_docs);
//exit;

$es_entrecuentas                = 0;
$rut_ordenante                  = isset($_GET['rut_ordenante']) ? $_GET['rut_ordenante'] : null;
$transaccion                    = isset($_GET['transaccion'])   ? $_GET['transaccion'] : null;
$cuenta                         = isset($_GET['cuenta'])        ? $_GET['cuenta'] : null;
$fecha_rec                      = isset($_GET['fecha_rec'])     ? $_GET['fecha_rec'] : null;
$monto_transferido_con_puntos   = isset($_GET['monto'])         ? $_GET['monto'] : 0;
$monto_transferido              = str_replace(['.', ' '], '', $monto_transferido_con_puntos);
$monto_diferencia               = 0;
$idusuario                      = 1;
$trae_cobertura                 = 0;
$diferencia_prestamo            = 0;
$nombre_ordenante               = null;
$n_documentos                   = null;
$fechas_venc                    = null;
$montos_docs_orig               = null;       
$montos_ingresados              = null;
$subproductos                   = null;
$etapa                          = null;
$saldo_disponible               = $monto_transferido;

//print_r($_POST);
//print_r($_GET);
//print_r($monto_diferencia - $monto_transferido);
//print_r("saldo inicial:" . $saldo_disponible . ";");
//exit;

$existe_pareo    = 0;
$idpareo_sistema = 0;
$estado_pareo    = 0;

if ($monto_transferido == $suma_prestamos) {
    $estado_pareo = 1;
}
if ($monto_transferido > $suma_prestamos) {
    $estado_pareo = 2;
}
if ($monto_transferido < $suma_prestamos) {
    $estado_pareo = 3;
}

$sql_consulta = "{call [_SP_CONCILIACIONES_MOVIMIENTO_CONSULTA](?)}";
$params_consulta = array(
    array($id_documentos[0],       SQLSRV_PARAM_IN),
);
$stmt_consulta = sqlsrv_query($conn, $sql_consulta, $params_consulta);

if ($stmt_consulta === false) {
    echo "Error in executing statement _consulta.\n";
    die(print_r(sqlsrv_errors(), true));
}

while ($consulta = sqlsrv_fetch_array($stmt_consulta, SQLSRV_FETCH_ASSOC)) {

    $rut_deudor     = $consulta['RUT_DEUDOR'];
    $rut_cliente    = $consulta['RUT_CLIENTE'];

    // SP para insertar en PAREO_SISTEMA y obtener ID_PAREO_SISTEMA
    $sql1 = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_INSERTA](?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";

    // Parámetros para la llamada al stored procedure
    $params1 = array(
        array($rut_ordenante,       SQLSRV_PARAM_IN),
        array($rut_deudor,          SQLSRV_PARAM_IN),
        array($transaccion,         SQLSRV_PARAM_IN),
        array($monto_diferencia,    SQLSRV_PARAM_IN),
        array($rut_cliente,         SQLSRV_PARAM_IN),
        array($estado_pareo,        SQLSRV_PARAM_IN),
        array($idusuario,           SQLSRV_PARAM_IN),
        array($es_entrecuentas,     SQLSRV_PARAM_IN),
        array(&$existe_pareo,       SQLSRV_PARAM_OUT),
        array(&$idpareo_sistema,    SQLSRV_PARAM_OUT)
    );

    // Ejecutar la consulta
    $stmt1 = sqlsrv_query($conn, $sql1, $params1);

    if ($stmt1 === false) {
        echo "Error in executing statement 1.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    // Verificar la variable de salida $existe
    if ($existe_pareo == 1) {
        header("Location: conciliaciones_transferencias_pendientes.php?op=3");
    }
}

$leidos                 = 0;
$conciliados            = 0;
$abonados               = 0;
$pendientes             = 0;
$ya_conciliados         = 0;
$saldo_insuf            = 0;
$concilia_doc           = 0;
$idpareo_docdeudores    = 0;
$tipo_pago              = 0;
$trae_cobertura         = 0;
$estado_diferencia      = 0;
$estado_canal           = 1;

//print_r($saldo_disponible);
//exit;

foreach ($id_documentos as $index => $id_docdeudores) {

    // Inicializar variables
    $concilia_doc           = 0;
    $idpareo_docdeudores    = 0;
    $tipo_pago              = 0;
    $haber                  = 0;
    $debe                   = 0;
    $aplica_cobertura       = 0;
    print_r('transferido: ' . $saldo_disponible . "; ");

    // Determinar si estamos en la última iteración

    $sql4 = "{call [_SP_CONCILIACIONES_MOVIMIENTO_DIFERENCIA_INSERTA] (?, ?, ?, ?, ?, ?, ?, ?, ?)}";
    $params4 = [
        [$id_docdeudores,           SQLSRV_PARAM_IN],
        [$cuenta,                   SQLSRV_PARAM_IN],
        [$transaccion,              SQLSRV_PARAM_IN],
        [$idpareo_sistema,          SQLSRV_PARAM_IN],
        [$fecha_rec,                SQLSRV_PARAM_IN],
        [$debe,                     SQLSRV_PARAM_IN],
        [$prestamos[$index],        SQLSRV_PARAM_IN],
        [$idusuario,                SQLSRV_PARAM_IN],
        [&$saldo_disponible,        SQLSRV_PARAM_INOUT]
    ];
    $stmt4 = sqlsrv_query($conn, $sql4, $params4);
    if ($stmt4 === false) {
        echo "Error in executing statement 4.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    $sql_diferencia = "{call [_SP_CONCILIACIONES_DIFERENCIA_CAMBIA_ESTADO] (?, ?)}";
    $params_diferencia = array(
        array($id_docdeudores,      SQLSRV_PARAM_IN),
        array($estado_diferencia,   SQLSRV_PARAM_IN)
    );
    $stmt_diferencia = sqlsrv_query($conn, $sql_diferencia, $params_diferencia);
    if ($stmt_diferencia === false) {
        echo "Error in executing statement diferencia.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    $sql_estado = "{call [_SP_CONCILIACIONES_CANALIZACION_CAMBIA_ESTADO] (?, ?)}";
    $params_estado = array(
        array($id_docdeudores,  SQLSRV_PARAM_IN),
        array($estado_canal,    SQLSRV_PARAM_IN)
    );
    $stmt_estado = sqlsrv_query($conn, $sql_estado, $params_estado);
    if ($stmt_estado === false) {
        echo "Error in executing statement estado.\n";
        die(print_r(sqlsrv_errors(), true));
    }
}


$sql5 = "{call [_SP_CONCILIACIONES_OPERACION_INSERTA] (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
$params5 = [
    [$idpareo_sistema,              SQLSRV_PARAM_IN],
    [$idpareo_docdeudores,          SQLSRV_PARAM_IN],
    [$rut_deudor,                   SQLSRV_PARAM_IN],
    [$rut_cliente,                  SQLSRV_PARAM_IN],
    [$transaccion,                  SQLSRV_PARAM_IN],
    [$monto_transferido,            SQLSRV_PARAM_IN],
    [$monto_diferencia,             SQLSRV_PARAM_IN],
    [(new DateTime($fecha_rec))->format('Y-m-d'),   SQLSRV_PARAM_IN],
    [$rut_ordenante,                SQLSRV_PARAM_IN],
    [$nombre_ordenante,             SQLSRV_PARAM_IN],
    [$cuenta,                       SQLSRV_PARAM_IN],
    [$id_docdeudores,               SQLSRV_PARAM_IN],
    [$n_documentos,                 SQLSRV_PARAM_IN],
    [$fechas_venc,                  SQLSRV_PARAM_IN],
    [$montos_docs_orig,             SQLSRV_PARAM_IN],
    [$montos_ingresados,            SQLSRV_PARAM_IN],
    [$subproductos,                 SQLSRV_PARAM_IN],
    [$etapa,                        SQLSRV_PARAM_IN],
    [$idusuario,                    SQLSRV_PARAM_IN]
];

$stmt5 = sqlsrv_query($conn, $sql5, $params5);
if ($stmt5 === false) {
    echo "Error in executing statement 5.\n";
    die(print_r(sqlsrv_errors(), true));

}

//print_r($saldo_disponible);

if ($saldo_disponible > 0) {
    $sql_saldo = "{call [_SP_CONCILIACIONES_SALDO_INSERTA] (?, ?)}";
    $params_saldo = array(
        array($idpareo_sistema,     SQLSRV_PARAM_IN),
        array($saldo_disponible,    SQLSRV_PARAM_IN)
    );
    $stmt_saldo = sqlsrv_query($conn, $sql_saldo, $params_saldo);
    if ($stmt_saldo === false) {
        echo "Error in executing statement saldo.\n";
        die(print_r(sqlsrv_errors(), true));
    }
} else {
    // Opcional: Manejo del caso cuando $saldo_disponible <= 0
    print_r("Saldo no disponible o menor o igual a cero.\n");
}

header("Location: conciliaciones_transferencias_pendientes.php?op=1");
exit;
