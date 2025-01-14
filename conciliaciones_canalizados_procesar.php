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

$idproceso              = 0;
$estado_canalizacion    = 2;
$total_procesados       = 0;
$id_asignacion          = 0;

/*print_r($_POST['ch_checkbox']);
exit;*/

if (isset($_POST['ch_checkbox'])) {

    // Inicializar los arreglos para almacenar los valores separados
    $id_pareos_sis   = array();
    $id_documentos   = array();
    $operaciones     = array();
    $transacciones   = array();
    $deud_noms       = array();  // Nombre del deudor
    $deud_ruts       = array();
    $deud_dvs        = array();
    $pago_docs       = array();
    $tipos_canal     = array();
    $benef_ctas      = array();  // Arreglo para almacenar $benef_cta
    $monto_docs      = array();  // Arreglo para almacenar $monto_doc
    $f_vencs         = array();  // Nuevo arreglo para almacenar $f_venc

    // Recorremos cada valor del arreglo de checkboxes
    foreach ($_POST['ch_checkbox'] as $checkbox_value) {
        // Dividimos el valor usando la coma como delimitador
        $valores = explode(',', $checkbox_value);

        // Verificamos que $valores tenga al menos 12 elementos (según la nueva estructura)
        if (count($valores) === 12) {
            $id_pareos_sis[]   = $valores[0];
            $id_documentos[]   = $valores[1];
            $operaciones[]     = $valores[2];
            $transacciones[]   = $valores[3];
            $deud_noms[]       = $valores[4];  // Guardamos el nombre del deudor
            $deud_ruts[]       = $valores[5];
            $deud_dvs[]        = $valores[6];
            $pago_docs[]       = $valores[7];
            $tipos_canal[]     = $valores[8];
            $benef_ctas[]      = $valores[9];  // Guardamos $benef_cta
            $monto_docs[]      = $valores[10]; // Guardamos $monto_doc
            $f_vencs[]         = $valores[11]; // Guardamos $f_venc
        }
    }

    // Combinar los arreglos en uno solo para facilitar el procesamiento
    $docs_combined = [];
    foreach ($id_documentos as $index => $id_documento) {
        $docs_combined[] = [
            'id_pareo_sis'    => $id_pareos_sis[$index],
            'id_documento'    => $id_documentos[$index],
            'operacion'       => $operaciones[$index],
            'transaccion'     => $transacciones[$index],
            'deud_nom'        => $deud_noms[$index],
            'deud_rut'        => $deud_ruts[$index],
            'deud_dv'         => $deud_dvs[$index],
            'pago_doc'        => $pago_docs[$index],
            'tipo_canal'      => $tipos_canal[$index],
            'benef_cta'       => $benef_ctas[$index],  // Añadimos $benef_cta
            'monto_doc'       => $monto_docs[$index],  // Añadimos $monto_doc
            'f_venc'          => $f_vencs[$index]      // Añadimos $f_venc
        ];
    }

    // Ejemplo: acceder a los valores individuales
    /*foreach ($docs_combined as $doc) {
        echo "ID Pareo Sis: " . $doc['id_pareo_sis'] . ", ID Documento: " . $doc['id_documento'] . ", Operación: " . $doc['operacion'] . ", Monto Documento: " . $doc['monto_doc'] . ", Fecha Vencimiento: " . $doc['f_venc'] . "<br>";
    }
    exit;*/
}

$sql_proceso = "{call [_SP_CONCILIACIONES_CANALIZACION_PROCESO_INSERTA](?, ?)}";
$params_proceso = array(
    array($idusuario,   SQLSRV_PARAM_IN),
    array(&$idproceso,  SQLSRV_PARAM_INOUT)
);

$stmt_proceso = sqlsrv_query($conn, $sql_proceso, $params_proceso);

if ($stmt_proceso === false) {
    echo "Error in executing statement proceso.\n";
    die(print_r(sqlsrv_errors(), true));
}

foreach ($docs_combined as $index => $conciliacion) {

    // Acceder a los datos dentro del arreglo docs_combined
    $id_pareo_sis   = $conciliacion['id_pareo_sis'];
    $id_documento   = $conciliacion['id_documento'];
    $operacion      = $conciliacion['operacion'];
    $transaccion    = $conciliacion['transaccion'];
    $deud_nom       = $conciliacion['deud_nom'];
    $deud_rut       = $conciliacion['deud_rut'];
    $deud_dv        = $conciliacion['deud_dv'];
    $pago_doc       = $conciliacion['pago_doc'];
    $tipo_canal     = $conciliacion['tipo_canal'];
    $benef_cta      = $conciliacion['benef_cta'];
    $monto_doc      = $conciliacion['monto_doc'];
    $f_venc         = $conciliacion['f_venc'];

    /* var_dump('id_documento: ' .  $id_documento . '; ');
    exit;*/

    $diferencia_doc = 0;

    $sql_diferencia = "{call [_SP_CONCILIACIONES_DIFERENCIAS_VALIDA](?, ?)}";
    $params_diferencia = array(
        array($id_documento,        SQLSRV_PARAM_IN),
        array(&$diferencia_doc,     SQLSRV_PARAM_OUT)
    );
    $stmt_diferencia = sqlsrv_query($conn, $sql_diferencia, $params_diferencia);
    if ($stmt_diferencia === false) {
        echo "Error in executing statement diferencia.\n";
        die(print_r(sqlsrv_errors(), true));
    }
    $diferencia = sqlsrv_fetch_array($stmt_diferencia, SQLSRV_FETCH_ASSOC);

/*print_r($diferencia_doc);
exit;*/
    if ($diferencia_doc == 0) {
/*
        print_r('PRINT PREVIO AL SP: ');
        var_dump('id_pareo_sis: ' .  $id_pareo_sis . '; ');
        var_dump('id_documento: ' .  $id_documento . '; ');
        var_dump('operacion: ' .     $operacion . '; ');
        var_dump('transaccion: ' .   $transaccion . '; ');
        var_dump('benef_cta: ' .     $benef_cta . '; ');
        var_dump('deud_nom: ' .      $deud_nom . '; ');
        var_dump('deud_rut: ' .      $deud_rut . '; ');
        var_dump('deud_dv: ' .       $deud_dv . '; ');
        var_dump('monto_doc: ' .     $monto_doc . '; ');
        var_dump('f_venc: ' .        $f_venc . '; ');
        var_dump('pago_doc: ' .      $pago_doc . '; ');
        var_dump('tipo_canal: ' .    $tipo_canal . '; ');
        var_dump('idusuario: ' .     $idusuario . '; ');
        exit;*/
    

        $sql_asignacion = "{call [_SP_CONCILIACIONES_ASIGNACION_INSERTAR](?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
        $params_asignacion = array(
            array($id_pareo_sis,    SQLSRV_PARAM_IN),
            array($id_documento,    SQLSRV_PARAM_IN),
            array($operacion,       SQLSRV_PARAM_IN),
            array($transaccion,     SQLSRV_PARAM_IN),
            array($benef_cta,       SQLSRV_PARAM_IN),
            array($deud_nom,        SQLSRV_PARAM_IN),
            array($deud_rut,        SQLSRV_PARAM_IN),
            array($deud_dv,         SQLSRV_PARAM_IN),
            array($monto_doc,       SQLSRV_PARAM_IN),
            array($f_venc,          SQLSRV_PARAM_IN),
            array($pago_doc,        SQLSRV_PARAM_IN),
            array($tipo_canal,      SQLSRV_PARAM_IN),
            array($idusuario,       SQLSRV_PARAM_IN)
        );
        /* print_r('PARAMS: ');
        var_dump($params_asignacion);
        exit;*/
        $stmt_asignacion = sqlsrv_prepare($conn, $sql_asignacion, $params_asignacion);
        if ($stmt_asignacion === false) {
            echo "Error in preparing statement asignacion.\n";
            die(print_r(sqlsrv_errors(), true));
        }
        if (sqlsrv_execute($stmt_asignacion) === false) {
            echo "Error in executing statement asignacion.\n";
            die(print_r(sqlsrv_errors(), true));
        }

        $sql_row = "{call [_SP_CONCILIACIONES_ASIGNACION_ULTIMA_CONSULTA]}";
        $stmt_row = sqlsrv_query($conn, $sql_row);
        if ($stmt_row === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $row = sqlsrv_fetch_array($stmt_row, SQLSRV_FETCH_ASSOC);
        $id_asignacion = $row['ID_ASIGNACION'];
        
        //print_r('Transaccion print: ' . $transaccion . '; ');

        $sql_ps = "{call [_SP_CONCILIACIONES_PAREO_SISTEMA_TRANSACCION_CONSULTA](?)}";
        $params_ps = array(
            array($transaccion,    SQLSRV_PARAM_IN),
        );
        $stmt_ps = sqlsrv_query($conn, $sql_ps, $params_ps);
        if ($stmt_ps === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $ps = sqlsrv_fetch_array($stmt_ps, SQLSRV_FETCH_ASSOC);
        
        $id_pareo_sis = $ps['ID_PAREO_SISTEMA'];
        
        //print_r(' ' .$id_pareo_sis. '; ');

        $sql_operacion = "{call [_SP_CONCILIACIONES_OPERACION_CONSULTA](?)}";
        $params_operacion = array(
            array($id_pareo_sis,    SQLSRV_PARAM_IN),
        );
        $stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
        if ($stmt_operacion === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $operaciones = sqlsrv_fetch_array($stmt_operacion, SQLSRV_FETCH_ASSOC);

        //print_r($operaciones);

        $transaccion_op = $operaciones['TRANSACCION'];

        $sql_asociados = "{call [_SP_CONCILIACIONES_OPERACIONES_ASOCIADAS_IDENTIFICAR](?, ?, ?, ?)}";
        $params_asociados = array(
            array($id_documento,    SQLSRV_PARAM_IN),
            array($transaccion_op,  SQLSRV_PARAM_IN),
            array(1,                SQLSRV_PARAM_IN), // ID_ESTADO
            array('2-3',            SQLSRV_PARAM_IN)  // ID_ETAPA       
        );
        $stmt_asociados = sqlsrv_query($conn, $sql_asociados, $params_asociados);
        if ($stmt_asociados === false) {
            echo "Error in executing statement asociados.\n";
            die(print_r(sqlsrv_errors(), true));
        }
        while ($asociados = sqlsrv_fetch_array($stmt_asociados, SQLSRV_FETCH_ASSOC)) {

            $iddoc_asociado = $asociados['ID_DOCDEUDORES'];

            $sql_detalles = "{call [_SP_CONCILIACIONES_CANALIZACION_PROCESO_DETALLES_INSERTA](?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
            $params_detalles = array(
                array($idproceso,       SQLSRV_PARAM_IN),
                array($id_asignacion,   SQLSRV_PARAM_IN),
                array($id_pareo_sis,    SQLSRV_PARAM_IN),
                array($id_documento,    SQLSRV_PARAM_IN),
                array($operacion,       SQLSRV_PARAM_IN),
                array($transaccion,     SQLSRV_PARAM_IN),
                array($benef_cta,       SQLSRV_PARAM_IN),
                array($deud_nom,        SQLSRV_PARAM_IN),
                array($deud_rut,        SQLSRV_PARAM_IN),
                array($deud_dv,         SQLSRV_PARAM_IN),
                array($monto_doc,       SQLSRV_PARAM_IN),
                array($f_venc,          SQLSRV_PARAM_IN),
                array($pago_doc,        SQLSRV_PARAM_IN),
                array($tipo_canal,      SQLSRV_PARAM_IN),
            );
            $stmt_detalles = sqlsrv_query($conn, $sql_detalles, $params_detalles);
            if ($stmt_detalles === false) {
                echo "Error in executing statement detalles.\n";
                die(print_r(sqlsrv_errors(), true));
            }

            $sql_estado = "{call [_SP_CONCILIACIONES_CANALIZACION_CAMBIA_ESTADO](?, ?)}";
            $params_estado = array(
                array($iddoc_asociado,      SQLSRV_PARAM_IN),
                array($estado_canalizacion, SQLSRV_PARAM_IN)
            );

            $stmt_estado = sqlsrv_query($conn, $sql_estado, $params_estado);

            if ($stmt_estado === false) {
                echo "Error in executing statement estado.\n";
                die(print_r(sqlsrv_errors(), true));
            }

            $sql_operacion = "{call [_SP_CONCILIACIONES_OPERACION_PROCESO_INSERTA](?, ?)}";
            $params_operacion = array(
                array($iddoc_asociado,  SQLSRV_PARAM_IN),
                array($idusuario,       SQLSRV_PARAM_IN)
            );
            $stmt_operacion = sqlsrv_query($conn, $sql_operacion, $params_operacion);
            if ($stmt_operacion === false) {
                echo "Error in executing statement operacion.\n";
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }
    $total_procesados++;
}

// Finalmente, puedes mostrar el total de procesados si es necesario
//echo "Total procesados: " . $total_procesados;

$sql_actualiza = "{call [_SP_CONCILIACIONES_CANALIZACION_PROCESO_ACTUALIZA](?, ?, ?)}";
$params_actualiza = array(
    array($idproceso,           SQLSRV_PARAM_IN),
    array($total_procesados,    SQLSRV_PARAM_IN),
    array($idusuario,           SQLSRV_PARAM_IN)
);

$stmt_actualiza = sqlsrv_query($conn, $sql_actualiza, $params_actualiza);

if ($stmt_actualiza === false) {
    echo "Error in executing statement actualiza.\n";
    die(print_r(sqlsrv_errors(), true));
}

header("Location: conciliaciones_lista_canalizados.php?op=1");
exit;