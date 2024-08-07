<?php
session_start();

include("funciones.php");
include("conexiones.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$op = 0;
if (isset($_GET["op"])) {
    $op = $_GET["op"];
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Sistema Conciliaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="CRM" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- App css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
    <link href="plugins/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- Plugins -->
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>

    <style>
        #fondo-sjud {
            width: 100%;
            height: 100vh;
            background-image: url('assets/images/fondo_sjud.png');
            background-size: cover;
            /* Ajusta el tamaño de la imagen para cubrir todo el div */
            background-position: center;
            /* Centra la imagen dentro del div */
            background-repeat: no-repeat;
            /* Evita que la imagen se repita */
            background-blend-mode: multiply;

        }
    </style>

</head>

<body class="dark-sidenav">
    <!-- Left Sidenav -->
    <?php include("menu_izquierda.php"); ?>
    <!-- end left-sidenav-->

    <div class="page-wrapper h-100" id="fondo-sjud">
        <!-- Top Bar Start -->
        <?php include("menu_top.php"); ?>
        <!-- Top Bar End -->

        <!-- Page Content-->
        <div class="page-content mt-3">

            <!-- end page title end breadcrumb -->

            <div class="row">
                <div class="col-12">
                    <BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR>
                    <h1 class="text-gray">
                        <CENTER> BIENVENIDO AL SISTEMA</CENTER>
                    </h1><BR><BR>
                    <h1 class="text-info">
                        <CENTER>USUARIO</CENTER>
                    </h1>
                    <BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR>
                </div>
            </div>

            <!-- end row -->

        </div><!-- container -->
    <?php include('footer.php'); ?>
    </div>
    <!-- end page content -->

    <!-- end page-wrapper -->


    <!-- jQuery  -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/metismenu.min.js"></script>
    <script src="assets/js/waves.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/simplebar.min.js"></script>
    <script src="assets/js/moment.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <script src="plugins/datatables/spanish.js"></script>
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
</body>

</html>