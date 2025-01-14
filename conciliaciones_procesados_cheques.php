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


$op = $_GET["op"];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Exportación de Cheques</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="CRM" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/loading.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
    <!-- Flatpickr CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body class="dark-sidenav">
    <!-- Left Sidenav -->
    <?php include("menu_izquierda.php"); ?>
    <div class="page-wrapper">
        <?php include("menu_top.php"); ?>

        <!-- Pantalla de Carga -->
        <div id="loading-screen">
            <div class="spinner mr-3"></div>
            <p>Cargando...</p>
        </div>

        <!-- Page Content -->
        <div class="page-content">
            <div class="container my-5">
                <div class="text-center mb-3">
                    <h1 class="display-6">Exportación de Cheques</h1>
                    <p class="text-muted">Módulo para exportar cheques según el periodo y estado seleccionados</p>
                </div>
                <div class="row align-items-center mt-5">
                    <!-- Formulario -->
                    <div class="col-lg-6 mx-auto">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <h4 class="card-title text-center mb-4">Seleccionar Criterios</h4>
                                <form method="post" action="conciliaciones_exportar_procesados_cheques.php" id="formulario">
                                    <!-- Periodos en la misma fila -->
                                    <div class="row">
                                        <!-- Periodo Inicio -->
                                        <div class="col-md-6 mb-3 position-relative">
                                            <label for="date_start" class="form-label">Periodo Inicio</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i data-feather="calendar"></i></span>
                                                </div>
                                                <input type="text" id="date_start" name="date_start" class="form-control datepicker" placeholder="Selecciona una fecha">
                                            </div>
                                        </div>
                                        <!-- Periodo Fin -->
                                        <div class="col-md-6 mb-3 position-relative">
                                            <label for="date_end" class="form-label">Periodo Fin</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i data-feather="calendar"></i></span>
                                                </div>
                                                <input type="text" id="date_end" name="date_end" class="form-control datepicker" placeholder="Selecciona una fecha">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Estado -->
                                    <div class="mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select id="estado" name="estado" class="form-control">
                                            <option value="1" selected>Todos los cheques</option>
                                            <option value="2">Pendientes de comprobante</option>
                                            <option value="3">Con comprobante sin conciliar</option>
                                            <option value="4">Conciliados</option>
                                        </select>
                                    </div>
                                    <!-- Botón -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success px-4">Exportar</button>
                                    </div>
                                </form>
                                <!-- Información del módulo -->
                                <hr>
                                <h5 class="text-center mt-4">¿Cómo funciona este módulo?</h5>
                                <ul class="list-group list-group-flush mt-3">
                                    <li class="list-group-item">
                                        <i data-feather="filter" class="text-primary me-2"></i>
                                        <strong>Filtros:</strong> Utiliza los campos de Periodo y Estado para filtrar los cheques que deseas exportar.
                                    </li>
                                    <li class="list-group-item">
                                        <i data-feather="file-text" class="text-success me-2"></i>
                                        <strong>Archivo de salida:</strong> El archivo generado será un <strong>.xlsx</strong>, compatible con Excel y otras herramientas.
                                    </li>
                                    <li class="list-group-item">
                                        <i data-feather="edit" class="text-warning me-2"></i>
                                        <strong>Pendientes de comprobante:</strong> En este caso, el campo <strong>N° Documento</strong> del archivo estará vacío. Una vez completados, el archivo puede ser ingresado en
                                        <a href="cargas_cheques.php" class="text-primary"><strong>Carga Cheques</strong></a> para la inserción de los respectivos comprobantes y validación de éstos.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <?php include('footer.php'); ?>
    </div>

    <!-- jQuery -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/metismenu.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar Flatpickr para los campos de fecha
            $(".datepicker").flatpickr({
                dateFormat: "Y-m-d", // Formato que se mostrará y usará
                locale: "es" // Configuración en español
            });

            // Obtener la fecha de hoy en formato "YYYY-MM-DD"
            const today = new Date().toISOString().split('T')[0];

            // Interceptar el envío del formulario
            $('#formulario').on('submit', function(e) {
                // Obtener valores de los campos
                let start = $('#date_start').val();
                let end = $('#date_end').val();
                const estado = $('#estado').val();

                // Si no se seleccionan los periodos, establecer valores por defecto
                if (!start && !end) {
                    start = '2020-01-01'; // Fecha más antigua predeterminada
                    end = today; // Fecha de hoy
                    $('#date_start').val(start); // Asignar el valor por defecto al input
                    $('#date_end').val(end); // Asignar el valor por defecto al input
                }

                // Validar si ambos periodos están definidos
                if (!start || !end) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar ambos periodos (Inicio y Fin).',
                    });
                    return;
                }

                // Validar que la fecha de inicio no sea mayor que la fecha de fin
                if (start > end) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El periodo de inicio no puede ser mayor al periodo de fin.',
                    });
                    return;
                }

                // Validar que se haya seleccionado un estado
                if (estado == 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar un estado.',
                    });
                }
            });

            // Ocultar el spinner al cargar la página
            $(window).on('load', function() {
                $('#loading-screen').hide();
            });
        });
    </script>
</body>

</html>