<?php
session_start();
include("funciones.php");
include("conexiones.php");
include("permisos_adm.php");
noCache();

// Obtener y sanitizar los datos de entrada
$id_doc         = isset($_GET["id_doc"]) ? (int)$_GET["id_doc"] : null;
$transaccion    = isset($_GET["transaccion"]) ? $_GET["transaccion"] : null;
$id_usuario     = $_SESSION['ID_USUARIO'];

// Comprobar que los parámetros no sean nulos
if ($id_doc === null || $transaccion === null) {
    die("ID Doc o Transacción no pueden ser nulos.");
}

// Verificar si existen registros en conciliaciones_pareos_sistemas
$sql_check = "SELECT * FROM conciliaciones_pareos_sistemas WHERE TRANSACCION = ?";
$stmt_check = sqlsrv_prepare($conn, $sql_check, array($transaccion));
if ($stmt_check === false) {
    die(print_r(sqlsrv_errors(), true));
}
sqlsrv_execute($stmt_check);

// Imprimir los resultados de la verificación
$found_rows = false;
while ($row = sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC)) {
    print_r($row);
    $found_rows = true;
}

if (!$found_rows) {
    echo "No se encontraron resultados en conciliaciones_pareos_sistemas para la transacción: $transaccion<br>";
}

// Si hay resultados, procede a llamar al procedimiento almacenado
// Si hay resultados, procede a llamar al procedimiento almacenado
if ($found_rows) {
    $sql_seleccion = "EXEC [_SP_CONCILIACIONES_OPERACIONES_ASOCIADAS_IDENTIFICAR] ?, ?, ?, ?";
    $params_seleccion = array(
        array($id_doc,      SQLSRV_PARAM_IN),
        array($transaccion, SQLSRV_PARAM_IN),
        array(1,            SQLSRV_PARAM_IN), // ID_ESTADO
        array(2,            SQLSRV_PARAM_IN)  // ID_ETAPA
    
    );

    $stmt_seleccion = sqlsrv_query($conn, $sql_seleccion, $params_seleccion);
    if ($stmt_seleccion === false) {
        die("Error en la ejecución de la consulta: " . print_r(sqlsrv_errors(), true));
    }

    // Verifica si hay filas y muestra un mensaje si no hay resultados
    if (!sqlsrv_has_rows($stmt_seleccion)) {
        echo "No se encontraron resultados para los parámetros dados: ID Doc: $id_doc, Transacción: $transaccion<br>";
    } else {
        echo 'Entrando al bucle while<br>';
        
        // Modificar esta parte
        while ($seleccion = sqlsrv_fetch_array($stmt_seleccion, SQLSRV_FETCH_ASSOC)) {
            print_r($seleccion); // Esto imprimirá todo el registro
            // Puedes mantener esta verificación si deseas obtener solo el ID_DOCDEUDORES
            // if (isset($seleccion["ID_DOCDEUDORES"])) {
            //     $id_documento = $seleccion["ID_DOCDEUDORES"];
            //     print_r($id_documento);
            // } else {
            //     echo "El campo ID_DOCDEUDORES no está presente en el resultado.<br>";
            // }
        }
    }

    // Cerrar la declaración si es necesario
    sqlsrv_free_stmt($stmt_seleccion);
}
