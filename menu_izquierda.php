<?php
// include("permisos_adm.php");
noCache();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<head>

    <style>


    </style>

</head>

<div class="left-sidenav border-0">
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
        <ul class="metismenu left-sidenav-menu mb-5">
            <li>
                <a href="cargas_conciliaciones.php">CARGA CARTOLA</a>
            </li>
            <li>
                <a href="conciliaciones_transferencias_pendientes.php">TRANSFERENCIAS PENDIENTES</a>
            </li>
            <li>
                <a href="conciliaciones_lista_pareados.php">CANALIZACION</a>
            </li>
            <li>
                <a href="conciliaciones_lista_canalizados.php">CANALIZADOS</a>
            </li>
            <!--   
            <li>
                <a href="conciliaciones_lista_conciliados.php">CONCILIADOS</a>
            </li>    
            <li>
                <a href="conciliaciones_lista_abonados.php">ABONOS</a>
            </li>
            -->
            <li>
                <a href="conciliaciones_lista_canalizados_pendientes.php">CANALIZADOS PENDIENTES</a>
            </li>
            <li>
                <a href="conciliaciones_lista_pendientes.php">PAREADOS PENDIENTES</a>
            </li>
            <li>
                <a href="conciliaciones_lista_saldos.php">SALDOS</a>
            </li>
            <li>
                <a href="conciliaciones_exportar_indeterminados.php">EXPORTAR INDETERMINADOS</a>
            </li>
        </ul>
    </div>

</div>