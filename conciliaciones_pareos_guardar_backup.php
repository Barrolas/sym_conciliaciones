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


//print_r($_POST['iddocumento_checkbox']);
//exit;


if (isset($_POST['iddocumento_checkbox'])) {

    // Creamos arreglos vacíos para almacenar los valores separados
    $id_documentos      = array();
    $montos_docs        = array();
    $fechas_venc        = array();
    $subproductos       = array();
    $monto_pareodocs    = array();
    $montos_ingresados  = array();
    $suma_docs          = 0;
    $cant_docs          = 0;

    // Recorremos cada valor del arreglo de checkboxes
    foreach ($_POST['iddocumento_checkbox'] as $checkbox_value) {

        // Dividimos el valor usando la coma como delimitador
        $valores = explode(',', $checkbox_value);

        // Verificamos que $valores tenga el número correcto de elementos
        if (count($valores) >= 6) {
            $id_documentos[]        = $valores[0];
            $montos_docs[]          = $valores[1];
            $fechas_venc[]          = $valores[2];
            $subproductos[]         = $valores[3];
            $monto_pareodocs[]      = $valores[4];
            $montos_ingresados[]    = $valores[5];
            $suma_docs             += $valores[1];
            $cant_docs++;
        }
    }

    // Combinar los arreglos en uno solo
    $docs_combined = [];
    foreach ($id_documentos as $index => $id) {
        $docs_combined[] = [
            'id_documento'      => $id,
            'monto_doc'         => $montos_docs[$index],
            'fecha_venc'        => $fechas_venc[$index],
            'subproducto'       => $subproductos[$index],
            'monto_pareodocs'   => $monto_pareodocs[$index],
            'monto_ingresado'   => $montos_ingresados[$index]
        ];
    }

    // Reemplazar el monto del documento si el monto ingresado es distinto de cero
    foreach ($docs_combined as $index => $doc) {
        if ($doc['monto_ingresado'] != '0') {
            $docs_combined[$index]['monto_doc'] = $doc['monto_ingresado'];
        }
    }

    // Eliminar el arreglo de montos ingresados ya que ya no es necesario
    unset($montos_ingresados);

    // Ordenar el arreglo combinado por fecha de vencimiento
    usort($docs_combined, function ($a, $b) {
        return strtotime($a['fecha_venc']) - strtotime($b['fecha_venc']);
    });

    // Descomponer el arreglo ordenado en los arreglos originales
    $id_documentos      = array_column($docs_combined, 'id_documento');
    $montos_docs        = array_column($docs_combined, 'monto_doc');
    $fechas_venc        = array_column($docs_combined, 'fecha_venc');
    $subproductos       = array_column($docs_combined, 'subproducto');
    $monto_pareodocs    = array_column($docs_combined, 'monto_pareodocs');
    // Ya no se usa $montos_ingresados
}

/*
print_r($id_documentos);
print_r($montos_docs);
print_r($fechas_venc);
print_r($subproductos);
print_r($monto_pareodocs);
*/
//exit;


//print_r($cant_docs);
//exit;

$rut_cliente                    = $_POST['cliente'];
$rut_ordenante                  = $_GET['rut_ordenante'];
$rut_deudor                     = $_GET['rut_deudor'];
$transaccion                    = $_GET['transaccion'];
$cuenta                         = $_GET['cuenta'];
$fecha_rec                      = $_GET['fecha_rec'];
$monto_diferencia               = $_GET['monto_diferencia'];
$monto_transferido_con_puntos   = $_GET['monto'];
$monto_transferido              = str_replace(['.', ' '], '', $monto_transferido_con_puntos);
$idusuario                      = 1;
$trae_cobertura                 = 0;


//print_r($_POST) . ";";
//print_r($_GET);
//print_r($monto_diferencia);
//exit;


$existe_pareo    = 0;
$idpareo_sistema = 0;
$estado_pareo    = 0;

if ($monto_transferido == $suma_docs) {
    $estado_pareo = 1;
}
if ($monto_transferido > $suma_docs) {
    $estado_pareo = 2;
}
if ($monto_transferido < $suma_docs) {
    $estado_pareo = 3;
}
/*
echo "<pre>"; // Esta etiqueta HTML ayuda a mantener el formato
echo "Monto Transferido:\n";
print_r($monto_transferido);
echo "\n\n"; // Dos saltos de línea

echo "Suma Docs:\n";
print_r($suma_docs);
echo "\n\n"; // Dos saltos de línea

echo "Estado Pareo:\n";
print_r($estado_pareo);
echo "</pre>"; // Cierra la etiqueta HTML <pre>
*/

// SP para insertar en PAREO_SISTEMA y obtener ID_PAREO_SISTEMA
$sql1 = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_INSERTA](?, ?, ?, ?, ?, ?, ?, ?, ?)}";

// Parámetros para la llamada al stored procedure
$params1 = array(
    array($rut_ordenante,       SQLSRV_PARAM_IN),
    array($rut_deudor,          SQLSRV_PARAM_IN),
    array($transaccion,         SQLSRV_PARAM_IN),
    array($monto_diferencia,    SQLSRV_PARAM_IN),
    array($rut_cliente,         SQLSRV_PARAM_IN),
    array($estado_pareo,        SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN),
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

// SP para insertar en DOCDEUDORES

$leidos                 = 0;
$conciliados            = 0;
$abonados               = 0;
$pendientes             = 0;
$ya_conciliados         = 0;
$saldo_insuf            = 0;
$concilia_doc           = 0;
$idpareo_docdeudores    = 0;
$tipo_pago              = 0;


if ($monto_diferencia == 0) {
    $saldo_disponible   = $monto_transferido;
} else {
    $saldo_disponible   = $monto_diferencia;
    $trae_cobertura     = 1;
};

//print_r($saldo_disponible);
//exit;

foreach ($id_documentos as $index => $id_docdeudores) {

    // Inicializar variable de salida para cada iteración
    $concilia_doc           = 0;
    $idpareo_docdeudores    = 0;
    $tipo_pago              = 0;
    print_r('transferido: ' . $saldo_disponible . "; ");

    $sql2 = "{call [_SP_CONCILIACIONES_PAREO_DOCDEUDORES_INSERTA] (?, ?, ?, ?, ?, ?, ?, ?)}";

    $params2 = array(
        array($idpareo_sistema,        SQLSRV_PARAM_IN),
        array($id_docdeudores,         SQLSRV_PARAM_IN),
        array($montos_docs[$index],    SQLSRV_PARAM_IN),
        array($subproductos[$index],   SQLSRV_PARAM_IN),
        array($idusuario,              SQLSRV_PARAM_IN),
        array(&$idpareo_docdeudores,   SQLSRV_PARAM_OUT),
        array(&$concilia_doc,          SQLSRV_PARAM_OUT),
        array(&$saldo_disponible,      SQLSRV_PARAM_INOUT)
    );

    $stmt2 = sqlsrv_query($conn, $sql2, $params2);

    if ($stmt2 === false) {
        echo "Error in executing statement 2.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    $leidos++;
    print_r('id pareo docdeudores: ' . $idpareo_docdeudores . '; ');

    if ($concilia_doc == 0) {

        $sql3 = "{call [_SP_CONCILIACIONES_PAREO_DOCDEUDORES_TIPIFICA] (?, ?, ?, ?, ?, ?, ?, ?)}";

        $params3 = array(
            array($idpareo_sistema,             SQLSRV_PARAM_IN),
            array($idpareo_docdeudores,         SQLSRV_PARAM_IN),
            array($id_docdeudores,              SQLSRV_PARAM_IN),
            array($rut_cliente,                 SQLSRV_PARAM_IN),
            array($montos_docs[$index],         SQLSRV_PARAM_IN),
            array($subproductos[$index],        SQLSRV_PARAM_IN),
            array(&$tipo_pago,                  SQLSRV_PARAM_OUT),
            array(&$trae_cobertura,             SQLSRV_PARAM_OUT),
            array(&$saldo_disponible,           SQLSRV_PARAM_INOUT)
        );

        $stmt3 = sqlsrv_query($conn, $sql3, $params3);

        if ($stmt3 === false) {
            echo "Error in executing statement 3.\n";
            die(print_r(sqlsrv_errors(), true));
        }
        if ($tipo_pago == 1) {
            $conciliados++;
        }
        if ($tipo_pago == 2) {
            $abonados++;
        }
        if ($tipo_pago == 3) {
            $pendientes++;
        }

        print_r('tipo_pago: ' . $tipo_pago . "; ");
    }

    if ($concilia_doc == 1) {
        $ya_conciliados++;
    }
    if ($concilia_doc == 2) {
        $saldo_insuf++;
    }
}

print_r('Leidos: '          . $leidos           . '; ');
print_r('conciliados: '     . $conciliados      . '; ');
print_r('abonados: '        . $abonados         . '; ');
print_r('pendientes: '      . $pendientes       . '; ');
print_r('ya conciliados: '  . $ya_conciliados   . '; ');
print_r('saldo insuf: '     . $saldo_insuf      . '; ');


//if ($existe_doc && $op == 1) {
//    header("Location: conciliaciones_transferencias_pendientes.php?op=4");
//}
//if ($existe_doc && $op == 2) {
//    header("Location: conciliaciones_transferencias_pendientes.php?op=4");

//} else {

//print_r($idpareo_sistema);
print_r(" lo que quedó despues de restar el doc: " . $saldo_disponible . "; ");
print_r(" monto_diferencia: " . $monto_diferencia . ";");
//exit;

if ($saldo_disponible > 0 && $monto_diferencia == 0) {
    $sql_saldo = "{call [_SP_CONCILIACIONES_SALDO_INSERTA] (?, ?)}";
    $params_saldo = array(
        array($idpareo_sistema,     SQLSRV_PARAM_IN),
        array($saldo_disponible,    SQLSRV_PARAM_IN)
    );
}

//print_r($idpareo_sistema);
print_r(" post: " . $saldo_disponible);
//exit;

// Ejecutar la consulta
$stmt_saldo = sqlsrv_query($conn, $sql_saldo, $params_saldo);

if ($stmt_saldo === false) {
    echo "Error in executing statement saldo.\n";
    die(print_r(sqlsrv_errors(), true));
}

if ($op == 1) {
    header("Location: conciliaciones_transferencias_pendientes.php?op=1");
}
if ($op == 2) {
    header("Location: conciliaciones_transferencias_pendientes.php?op=1");
}
