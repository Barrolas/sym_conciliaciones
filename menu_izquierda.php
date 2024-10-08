<?php
// include("permisos_adm.php");
noCache();
ini_set('display_errors', 1); // Recuerda desactivar en producción
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Sistema Conciliaciones</title>
    <!-- MetisMenu CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.css" integrity="sha512-hbX9KbJ+z0JpC/uPawBybFse8O9A3bZBf90J1YGsS+sy90ZoQ/EUQdhgo8TSBzVq8klK6O9VwS6j+evHdkM1wQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Material Design Icons CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/6.5.95/css/materialdesignicons.min.css" integrity="sha512-2VHrIQ3vXUkH7d0+y1O3iBhGHo7FwS6+VhLGLR1g7uJAlQRTmDzU05eT+0ZVRTp0t6rXrLjhkP0G3Yj4mG3R3g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilo para los títulos de los menús */
        .menu-title {
            font-size: 1.1em;
            font-weight: bold;
            color: #3498db;
            /* Cambio de color a #3498db */
            padding: 10px 20px;
            cursor: pointer;
            /* Indicador de que es interactivo */
        }

        /* Estilo para íconos de flecha */
        .menu-title .menu-arrow {
            float: right;
            transition: transform 0.3s;
        }

        /* Rotar la flecha cuando el menú está abierto */
        .metisMenu li.active>a .menu-arrow {
            transform: rotate(90deg);
        }

        /* Estilos para enlaces del menú */
        .metisMenu>li>a {
            padding: 10px 20px;
            color: #adb5bd;
            display: block;
            text-decoration: none;
        }

        .metisMenu>li>a:hover {
            color: #ffffff;
            background-color: #495057;
            text-decoration: none;
        }

        /* Estilo para el sidebar */
        .left-sidenav {
            width: 250px;
            min-height: 100vh;
            background-color: #343a40;
            color: #adb5bd;
            position: fixed;
        }

        /* Ajustes para el encabezado */
        .left-sidenav .logo h5 {
            color: #ffffff;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="left-sidenav sidebar border-0">
        <!-- LOGO -->
        <div class="brand">
            <a href="menu_principal.php" class="logo">
                <span>
                    <img src="assets/images/symtrnasparente2.png" alt="logo-small" height="60" width="120" class="mt-4">
                </span>
            </a>
        </div>
        <!--end logo-->
        <br>

        <a href="menu_principal.php" class="logo">
            <h5 class="text-center text-white mt-4">SISTEMA CONCILIACIONES</h5>
        </a>

        <div class="menu-content h-100" data-simplebar>
            <ul class="metismenu left-sidenav-menu mb-5" id="side-menu">
                <!-- Paso 1 -->
                <li>
                    <a href="javascript:void(0);" class="menu-title">
                        1. PAREO DOCUMENTOS
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul>
                        <li><a href="cargas_conciliaciones.php">CARGA TRANSFERENCIAS</a></li>
                        <li><a href="conciliaciones_transferencias_pendientes.php">TRANSFERENCIAS RECIBIDAS</a></li>
                        <li><a href="conciliaciones_lista_pendientes.php">PAREADOS PENDIENTES</a></li>
                        <li><a href="conciliaciones_exportar_indeterminados.php">EXPORTAR INDETERMINADOS</a></li>
                    </ul>
                </li>

                <!-- Paso 2 -->
                <li>
                    <a href="javascript:void(0);" class="menu-title">
                        2. CANALIZACIÓN
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul>
                        <li><a href="conciliaciones_lista_pareados.php">ASIGNAR CANAL</a></li>
                        <li><a href="conciliaciones_lista_canalizados.php">PROCESAR</a></li>
                        <li><a href="conciliaciones_lista_canalizados_pendientes.php">CANALIZADOS PENDIENTES</a></li>
                        <li><a href="conciliaciones_exportar_procesados.php">EXPORTAR PROCESADOS</a></li>
                    </ul>
                </li>

                <!-- Paso 3 -->
                <li>
                    <a href="javascript:void(0);" class="menu-title">
                        3. CARGAS
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul>
                        <li><a href="cargas_cheques.php">CARGA CHEQUES</a></li>
                        <li><a href="cargas_sisrec.php">CARGA SISREC</a></li>
                        <li><a href="cargas_cartola_bancaria.php">CARGA CARTOLA</a></li>
                    </ul>
                </li>

                <!-- Paso 4 -->
                <li>
                    <a href="javascript:void(0);" class="menu-title">
                        4. CONCILIACIÓN
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul>
                        <li><a href="conciliaciones_lista_pendientes_comprobante.php">PENDIENTES DE COMPROBANTE</a></li>
                        <li><a href="conciliaciones_cartola_pendientes.php">CONCILIACIÓN</a></li>
                    </ul>
                </li>


                <!-- Reportes -->
                <li>
                    <a href="javascript:void(0);" class="menu-title">
                        5. REPORTES
                        <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
                    </a>
                    <ul>
                        <li><a href="conciliaciones_lista_saldos.php">SALDOS</a></li>
                       <!-- <li><a href="conciliaciones_lista_diferencias.php">DIFERENCIAS</a></li> -->
                        <li><a href="conciliaciones_lista_movimientos.php">MOVIMIENTOS</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <!-- Asegúrate de incluir jQuery y MetisMenu JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-..."></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.js" integrity="sha512-..."></script>
    <script>
        $(document).ready(function() {
            $('#side-menu').metisMenu();

            // Opcional: Cambiar el color del título al estar expandido
            $('#side-menu').on('shown.metisMenu', function(e) {
                $(e.target).parent('li').addClass('active');
            });

            $('#side-menu').on('hidden.metisMenu', function(e) {
                $(e.target).parent('li').removeClass('active');
            });
        });
    </script>
</body>

</html>