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

print_r($_POST);

if (isset($_POST['iddocumento_checkbox'])) {

    // Creamos arreglos vacíos para almacenar los valores separados
    $id_documentos          = array();
    $montos_docs            = array();
    $fechas_venc            = array();
    $subproductos           = array();
    $montos_pareodocs       = array();
    $montos_ingresados      = array();
    $prestamos_ingresados   = array();
    $suma_docs              = 0;
    $cant_docs              = 0;

    // Recorremos cada valor del arreglo de checkboxes
    foreach ($_POST['iddocumento_checkbox'] as $checkbox_value) {

        // Dividimos el valor usando la coma como delimitador
        $valores = explode(',', $checkbox_value);

        // Verificamos que $valores tenga el número correcto de elementos
        if (count($valores) >= 8) {
            $id_documentos[]        = $valores[0];
            $montos_docs[]          = $valores[1];
            $montos_docs_orig[]     = $valores[1];
            $fechas_venc[]          = $valores[2];
            $subproductos[]         = $valores[3];
            $montos_pareodocs[]     = $valores[4];
            $montos_ingresados[]    = $valores[5];
            $prestamos_ingresados[] = $valores[6];
            $n_documentos[]         = $valores[7];
            $suma_docs             += $valores[1];
            $cant_docs++;
        }
    }
/*
    print_r($valores[0] . '; ');
    print_r($valores[1] . '; ');
    print_r($valores[2] . '; ');
    print_r($valores[3] . '; ');
    print_r($valores[4] . '; ');
    print_r($valores[5] . '; ');
    print_r($valores[6] . '; ');
    print_r($valores[7] . '; ');
    exit;
*/
    // Combinar los arreglos en uno solo
    $docs_combined = [];
    foreach ($id_documentos as $index => $id) {
        $docs_combined[] = [
            'id_documento'          => $id,
            'monto_doc'             => $montos_docs[$index],
            'monto_doc_orig'        => $montos_docs_orig[$index],
            'fecha_venc'            => $fechas_venc[$index],
            'subproducto'           => $subproductos[$index],
            'monto_pareodocs'       => $montos_pareodocs[$index],
            'monto_ingresado'       => $montos_ingresados[$index],
            'prestamo_ingresado'    => $prestamos_ingresados[$index],
            'n_documento'           => $n_documentos[$index]
        ];
    }

    //Reemplazar el monto del documento si el monto ingresado es distinto de cero
    foreach ($docs_combined as $index => $doc) {
        if ($doc['monto_ingresado'] != '0') {
            $docs_combined[$index]['monto_doc'] = $doc['monto_ingresado'];
        }
    }    

    // Ordenar el arreglo combinado por fecha de vencimiento
    usort($docs_combined, function ($a, $b) {
        return strtotime($a['fecha_venc']) - strtotime($b['fecha_venc']);
    });

    // Descomponer el arreglo ordenado en los arreglos originales
    $id_documentos          = array_column($docs_combined, 'id_documento');
    $montos_docs            = array_column($docs_combined, 'monto_doc');
    $montos_docs_orig       = array_column($docs_combined, 'monto_doc_orig');
    $fechas_venc            = array_column($docs_combined, 'fecha_venc');
    $subproductos           = array_column($docs_combined, 'subproducto');
    $montos_pareodocs       = array_column($docs_combined, 'monto_pareodocs');
    $montos_ingresados      = array_column($docs_combined, 'monto_ingresado');
    $prestamos_ingresados   = array_column($docs_combined, 'prestamo_ingresado');
    $n_documentos           = array_column($docs_combined, 'n_documento');

}

/*
print_r($id_documentos);
print_r($montos_docs);
print_r($montos_docs_orig);
print_r($fechas_venc);
print_r($subproductos);
print_r($montos_pareodocs);
print_r($montos_ingresados);
print_r($prestamos_ingresados);
print_r($n_documentos);

exit;
*/

//print_r($cant_docs);
//exit;

$rut_cliente                    = $_POST['cliente'];
$es_entrecuentas                = $_POST['es_entrecuentas'];
$rut_ordenante                  = $_GET['rut_ordenante'];
$nombre_ordenante               = $_GET['nombre_ordenante'];
$rut_deudor                     = $_GET['rut_deudor'];
$transaccion                    = $_GET['transaccion'];
$cuenta_benef                   = $_GET['cuenta'];
$fecha_rec                      = $_GET['fecha_rec'];
$monto_diferencia               = $_GET['monto_diferencia'];
$monto_transferido_con_puntos   = $_GET['monto'];
$monto_transferido              = str_replace(['.', ' '], '', $monto_transferido_con_puntos);
$idusuario                      = 1;
$trae_cobertura                 = 0;
$diferencia_prestamo            = ($monto_diferencia - $monto_transferido);
$etapa                          = 1;

if ($es_entrecuentas == 1) {
    $tipo_pareosistema          = 3;
} else {
    $tipo_pareosistema          = 1;
}
/*
print_r($_POST) . ";";
print_r($_GET);
print_r($monto_diferencia - $monto_transferido);
exit;
*/
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

$sql_tipo_ps = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_DETALLES_INSERTA](?, ?, ?, ?)}";
$params_tipo_ps = array(
    array($transaccion,         SQLSRV_PARAM_IN),
    array($idpareo_sistema,     SQLSRV_PARAM_IN),
    array($tipo_pareosistema,   SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN)
);
// Ejecutar la consulta
$stmt_tipo_ps = sqlsrv_query($conn, $sql_tipo_ps, $params_tipo_ps);

if ($stmt_tipo_ps === false) {
    echo "Error in executing statement tipo_ps.\n";
    die(print_r(sqlsrv_errors(), true));
}

$tipo_ps = sqlsrv_fetch_array($stmt_tipo_ps, SQLSRV_FETCH_ASSOC);

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

    // Inicializar variables
    $concilia_doc           = 0;
    $idpareo_docdeudores    = 0;
    $tipo_pago              = 0;
    $haber                  = 0;
    $debe                   = 0;
    $aplica_cobertura       = 0;
    print_r('transferido: ' . $saldo_disponible . "; ");

    /*
    print_r($idpareo_sistema . '; ');
    print_r($id_docdeudores . '; ');
    print_r($montos_docs[$index] . '; ');
    print_r($subproductos[$index] . '; ');
    print_r($prestamos_ingresados[$index] . '; ');
    print_r($idusuario . '; ');
    print_r($saldo_disponible . '; ');
    exit;
    */

    // Determinar si estamos en la última iteración
    if ($index === count($id_documentos) - 1 && $trae_cobertura == 1) {
        $aplica_cobertura = 1;  // Aplicar cobertura solo en la última iteración
    }

    $sql2 = "{call [_SP_CONCILIACIONES_PAREO_DOCDEUDORES_INSERTA] (?, ?, ?, ?, ?, ?, ?, ?, ?)}";
    $params2 = [
        [$idpareo_sistema,              SQLSRV_PARAM_IN],
        [$id_docdeudores,               SQLSRV_PARAM_IN],
        [$montos_docs[$index],          SQLSRV_PARAM_IN],
        [$subproductos[$index],         SQLSRV_PARAM_IN],
        [$prestamos_ingresados[$index], SQLSRV_PARAM_IN],
        [$idusuario,                    SQLSRV_PARAM_IN],
        [&$idpareo_docdeudores,         SQLSRV_PARAM_OUT],
        [&$concilia_doc,                SQLSRV_PARAM_OUT],
        [&$saldo_disponible,            SQLSRV_PARAM_INOUT]
    ];

    $stmt2 = sqlsrv_query($conn, $sql2, $params2);
    if ($stmt2 === false) {
        echo "Error in executing statement 2.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    $leidos++;
    print_r('id pareo docdeudores: ' . $idpareo_docdeudores . '; ');

    if ($concilia_doc == 0) {
        $sql3 = "{call [_SP_CONCILIACIONES_PAREO_DOCDEUDORES_TIPIFICA] (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
        $params3 = [
            [$idpareo_sistema,          SQLSRV_PARAM_IN],
            [$idpareo_docdeudores,      SQLSRV_PARAM_IN],
            [$id_docdeudores,           SQLSRV_PARAM_IN],
            [$rut_cliente,              SQLSRV_PARAM_IN],
            [$montos_docs[$index],      SQLSRV_PARAM_IN],
            [$subproductos[$index],     SQLSRV_PARAM_IN],
            [$aplica_cobertura,         SQLSRV_PARAM_IN],
            [&$tipo_pago,               SQLSRV_PARAM_OUT],
            [&$debe,                    SQLSRV_PARAM_OUT],
            [&$saldo_disponible,        SQLSRV_PARAM_INOUT]
        ];

        $stmt3 = sqlsrv_query($conn, $sql3, $params3);
        if ($stmt3 === false) {
            echo "Error in executing statement 3.\n";
            die(print_r(sqlsrv_errors(), true));
        }

        $sql4 = "{call [_SP_CONCILIACIONES_MOVIMIENTO_INSERTA] (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
        $params4 = [
            [$id_docdeudores,           SQLSRV_PARAM_IN],
            [$cuenta_benef,             SQLSRV_PARAM_IN],
            [$transaccion,              SQLSRV_PARAM_IN],
            [$idpareo_sistema,          SQLSRV_PARAM_IN],
            [$idpareo_docdeudores,      SQLSRV_PARAM_IN],
            [$n_documentos[$index],     SQLSRV_PARAM_IN],
            [$fechas_venc[$index],      SQLSRV_PARAM_IN],
            [$fecha_rec,                SQLSRV_PARAM_IN],
            [$montos_docs[$index],      SQLSRV_PARAM_IN],
            [$debe,                     SQLSRV_PARAM_IN],
            [$aplica_cobertura,         SQLSRV_PARAM_IN],
            [$diferencia_prestamo,      SQLSRV_PARAM_IN],
            [$es_entrecuentas,          SQLSRV_PARAM_IN],
            [$idusuario,                SQLSRV_PARAM_IN]
        ];

        $stmt4 = sqlsrv_query($conn, $sql4, $params4);
        if ($stmt4 === false) {
            echo "Error in executing statement 4.\n";
            die(print_r(sqlsrv_errors(), true));
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
            [(new DateTime($fecha_rec))->format('Y-m-d'),           SQLSRV_PARAM_IN],
            [$rut_ordenante,                SQLSRV_PARAM_IN],
            [$nombre_ordenante,             SQLSRV_PARAM_IN],
            [$cuenta_benef,                 SQLSRV_PARAM_IN],
            [$id_docdeudores,               SQLSRV_PARAM_IN],
            [$n_documentos[$index],         SQLSRV_PARAM_IN],
            [(new DateTime($fechas_venc[$index]))->format('Y-m-d'), SQLSRV_PARAM_IN],
            [$montos_docs_orig[$index],     SQLSRV_PARAM_IN],
            [$montos_ingresados[$index],    SQLSRV_PARAM_IN],
            [$subproductos[$index],         SQLSRV_PARAM_IN],
            [$etapa,                        SQLSRV_PARAM_IN],
            [$idusuario,                    SQLSRV_PARAM_IN]
        ];

        $stmt5 = sqlsrv_query($conn, $sql5, $params5);
        if ($stmt5 === false) {
            echo "Error in executing statement 5.\n";
            die(print_r(sqlsrv_errors(), true));
        }


        switch ($tipo_pago) {
            case 1:
                $conciliados++;
                break;
            case 2:
                $abonados++;
                break;
            case 3:
                $pendientes++;
                break;
        }
        print_r('tipo_pago: ' . $tipo_pago . "; ");
    }

    if ($concilia_doc == 1) {
        $ya_conciliados++;
    } elseif ($concilia_doc == 2) {
        $saldo_insuf++;
    }
}



print_r('Leidos: '          . $leidos           . '; ');
print_r('conciliados: '     . $conciliados      . '; ');
print_r('abonados: '        . $abonados         . '; ');
print_r('pendientes: '      . $pendientes       . '; ');
print_r('ya conciliados: '  . $ya_conciliados   . '; ');
print_r('saldo insuf: '     . $saldo_insuf      . '; ');
print_r(" lo que quedó despues de restar el doc: " . $saldo_disponible . "; ");
print_r(" monto_diferencia: " . $monto_diferencia . ";");


if ($saldo_disponible > 0 && $monto_diferencia == 0) {

/*echo '===============================================';
    print_r($cuenta_benef . '; ');
    print_r($transaccion . '; ');
    print_r($idpareo_sistema . '; ');
    print_r($fecha_rec . '; ');
    print_r($idusuario . '; ');
    print_r($saldo_disponible . '; ');*/
    $tipo_saldo = 1;

    $sql_saldo = "{call [_SP_CONCILIACIONES_SALDO_INSERTA] (?, ?, ?, ?)}";
    $params_saldo = array(
        array($idpareo_sistema,     SQLSRV_PARAM_IN),
        array($tipo_saldo,          SQLSRV_PARAM_IN),
        array($saldo_disponible,    SQLSRV_PARAM_IN),
        array($idusuario,           SQLSRV_PARAM_IN)
    );
    // Ejecutar la consulta
    $stmt_saldo = sqlsrv_query($conn, $sql_saldo, $params_saldo);

    /*print_r($stmt_saldo . '; ');
    exit;*/

    if ($stmt_saldo === false) {
        echo "Error in executing statement saldo.\n";
        die(print_r(sqlsrv_errors(), true));
    }
/*
    print_r($cuenta_benef);
    print_r($transaccion);
    print_r($idpareo_sistema);
    print_r($fecha_rec);
    print_r($idusuario);
    print_r($saldo_disponible);
    exit;
*/
    $sql_devolucion = "{call [_SP_CONCILIACIONES_MOVIMIENTO_DEVOLUCION_INSERTA] (?, ?, ?, ?, ?, ?)}";
    $params_devolucion = array(
        array($cuenta_benef,            SQLSRV_PARAM_IN),
        array($transaccion,             SQLSRV_PARAM_IN),
        array($idpareo_sistema,         SQLSRV_PARAM_IN),
        array($fecha_rec,               SQLSRV_PARAM_IN),
        array($idusuario,               SQLSRV_PARAM_IN),
        array($saldo_disponible,        SQLSRV_PARAM_IN)
    );
    // Ejecutar la consulta
    $stmt_devolucion = sqlsrv_query($conn, $sql_devolucion, $params_devolucion);

    if ($stmt_devolucion === false) {
        echo "Error in executing statement devolucion.\n";
        die(print_r(sqlsrv_errors(), true));
    }
}

//print_r($idpareo_sistema);
print_r(" post: " . $saldo_disponible);
//exit;


if ($op == 1) {
    header("Location: conciliaciones_transferencias_pendientes.php?op=1");
}
if ($op == 2) {
    header("Location: conciliaciones_transferencias_pendientes.php?op=1");
}
