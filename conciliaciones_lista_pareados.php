<?php
session_start();
include("funciones.php");
include("conexiones.php");
// include("permisos_adm.php");
noCache();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$op = 0;
if (isset($_GET["op"])) {
    $op = $_GET["op"];
};

$sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
        from [192.168.1.193].conciliacion.dbo.Transferencias_Recibidas_Hist";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true)); // Manejar el error aquí según tus necesidades
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$fecha_proceso = $row["FECHAPROCESO"];


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Conciliaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="CRM" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- DataTables -->
    <link href="plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css">
    <!-- Responsive datatable examples -->
    <link href="plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- App css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
    <link href="plugins/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="plugins/dropify/css/dropify.min.css" rel="stylesheet">
    <link href="assets/css/loading.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <!-- Plugins -->
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
    <style>
        @media (min-width: 1000px) and (max-width: 1299px) {
            .font_mini {
                font-size: 12px !important;
            }

            .font_mini_header {
                font-size: 11px !important;
            }

            .card_width {
                width: 90% !important;
                overflow-x: scroll;
            }

            .card_content {
                width: 100% !important;
                overflow-x: visible;
            }

            @media (min-width: 1300px) {
                .font_mini {
                    font-size: 15px !important;
                }

                .font_mini_header {
                    font-size: 15px !important;
                }

                .card_width {
                    width: 100% !important;
                }
            }
        }
    </style>

</head>

<body class="dark-sidenav">
    <!-- Left Sidenav -->
    <?php include("menu_izquierda.php"); ?>

    <!-- end left-sidenav-->
    <div class="page-wrapper">
        <!-- Top Bar Start -->
        <?php include("menu_top.php"); ?>
        <!-- Top Bar End -->

        <!-- Pantalla de Carga -->
        <div id="loading-screen">
            <div class="spinner mr-3"></div>
            <p>Cargando...</p>
        </div>


        <!-- Page Content-->
        <div class="page-content" id="content">
            <form id="form_concilia" method="post" class="mr-0" action="conciliaciones_canalizaciones_guardar.php" onsubmit="return valida_envia();return false;">
                <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="page-title-box">
                                <div class="row">
                                    <div class="col">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="menu_principal.php">Inicio</a></li>
                                            <li class="breadcrumb-item active">Conciliaciones</li>
                                        </ol>
                                    </div><!--end col-->
                                </div><!--end col-->
                            </div><!--end row-->
                        </div><!--end page-title-box-->
                    </div><!--end col-->
                </div><!--end row-->
                <!-- end page title end breadcrumb -->

                <div class="container-fluid mx-3">
                    <div class="row">
                        <div class="col">
                            <h3>
                                <b>Canalización</b>
                            </h3>
                        </div>
                        <div class="row mr-2">
                            <div class="col-12 mx-2">
                                <p>
                                    Esta herramienta permite visualizar y gestionar las transferencias ya pareadas en el sistema
                                    y asignarle a cada cual si se canalizará por <b>CHEQUE</b> o <b>TRANSFERENCIA</b>.
                                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Facere dolores sequi animi ipsa quaerat
                                    delectus veritatis veniam corrupti consequuntur cupiditate quidem totam asperiores optio at, dolore
                                    vero incidunt maxime nulla.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid px-1">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row text-start justify-content-start justify-items-stretch pl-4 mb-3">
                                <div class="col-lg-2">
                                    <label class="col-12" for="fecha_ultima_cartola">ÚLTIMA CARTOLA</label>
                                    <input type="text" class="form-control col-12" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                                </div>
                                <div class="col-lg-2">
                                    <label for="dias_mora" class="col-12">DIAS MORA</label>
                                    <select name="dias_mora" id="dias_mora" class="form-control col-12" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Mostrar todos</option>
                                        <option value="1">170 días o más</option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <div class="col-lg-12">
                                        <label for="cuenta" class="col-4">CUENTA</label>
                                        <select name="cuenta" id="cuenta" class="form-control" maxlength="50" autocomplete="off">
                                            <option value="0" selected>Mostrar todas</option>
                                            <?php
                                            $sql_cuenta = "{call [_SP_CONCILIACIONES_LISTA_CUENTAS_BENEFICIARIOS]}";
                                            $stmt_cuenta = sqlsrv_query($conn, $sql_cuenta);

                                            if ($stmt_cuenta === false) {
                                                die(print_r(sqlsrv_errors(), true));
                                            }
                                            while ($cuenta = sqlsrv_fetch_array($stmt_cuenta, SQLSRV_FETCH_ASSOC)) {
                                            ?>
                                                <option value="<?php echo $cuenta["CUENTA"] ?>"><?php echo $cuenta["CUENTA"] ?></option>
                                            <?php }; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <label for="estado_conc" class="col-12">ESTADO</label>
                                    <select name="estado_conc" id="estado_conc" class="form-control col-12" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Mostrar todos</option>
                                        <option value="CONC">CONCILIADO</option>
                                        <option value="ABON">ABONADO</option>
                                        <option value="PEND">PENDIENTE</option>
                                    </select>
                                </div>
                                <div class="col-lg-1">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light mt-4" id="guardarButton" disabled>GUARDAR</button>
                                </div>
                            </div><!--end form-group-->
                        </div><!--end col-->
                    </div>

                    <div class="col-12 px-3">
                        <div class="card card_content">
                            <div class="card-body card_width">
                                <table id="datatable2" class="table dt-responsive nowrap">
                                    <thead>
                                        <tr>
                                            <th class="font_mini_header">
                                                <div class="d-flex flex-column align-items-center">
                                                    <input class="mb-2" type="checkbox" id="select_all_checkbox1" onclick="handleMasterCheckbox(1)">
                                                    <span>CH</span>
                                                </div>
                                            </th>
                                            <th class="font_mini_header">
                                                <div class="d-flex flex-column align-items-center">
                                                    <input class="mb-2" type="checkbox" id="select_all_checkbox2" onclick="handleMasterCheckbox(2)">
                                                    <span>TR</span>
                                                </div>
                                            </th>
                                            <th class="font_mini_header">ID DOC</th>
                                            <th class="font_mini_header">CUENTA</th>
                                            <th class="font_mini_header">F. VENC</th>
                                            <th class="font_mini_header">F. RECEP</th>
                                            <th class="font_mini_header">TRANSACCIÓN</th>
                                            <th class="font_mini_header">RUT DEUD</th>
                                            <th class="font_mini_header">OPERACIÓN</th>
                                            <th class="font_mini_header">MORA</th>
                                            <th class="font_mini_header">CARTERA</th>
                                            <th class="font_mini_header">E°</th>
                                            <th class="font_mini_header">$ TRANSF</th>
                                            <th class="font_mini_header">$ DOC</th>
                                            <th class="font_mini_header">CUBIERTO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "EXEC [_SP_CONCILIACIONES_CANALIZACIONES_LISTA]";
                                        $stmt = sqlsrv_query($conn, $sql);
                                        if ($stmt === false) {
                                            die(print_r(sqlsrv_errors(), true));
                                        }
                                        while ($canalizacion = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

                                            $id_documento = $canalizacion["ID_DOC"];

                                            // Consulta para obtener el monto de abonos (solo si el estado no es '1')
                                            $sql4 = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ABONOS](?)}";
                                            $params4 = array($id_documento);
                                            $stmt4 = sqlsrv_query($conn, $sql4, $params4);

                                            if ($stmt4 === false) {
                                                die(print_r(sqlsrv_errors(), true));
                                            }

                                            $monto_pareo = 0; // Inicializa en 0
                                            while ($abonos = sqlsrv_fetch_array($stmt4, SQLSRV_FETCH_ASSOC)) {
                                                $monto_pareo = isset($abonos["MONTO_PAREO"]) ? $abonos["MONTO_PAREO"] : 0;
                                            }

                                            // Consulta para obtener el estado del documento
                                            $sql5 = "{call [_SP_CONCILIACIONES_CONSULTA_DOCDEUDORES_ESTADO](?)}";
                                            $params5 = array($id_documento);
                                            $stmt5 = sqlsrv_query($conn, $sql5, $params5);

                                            if ($stmt5 === false) {
                                                die(print_r(sqlsrv_errors(), true));
                                            }

                                            
                                            $estado_pareo_text = 'N/A'; // Valor por defecto
                                            while ($estados = sqlsrv_fetch_array($stmt5, SQLSRV_FETCH_ASSOC)) {
                                                
                                                $disabled = ($estados["ID_ESTADO"] == 3) ? 'disabled' : '';

                                                $estado_pareo = isset($estados['ID_ESTADO']) ? $estados['ID_ESTADO'] : NULL;
                                                switch ($estado_pareo) {
                                                    case '1':
                                                        $estado_pareo_text = 'CONC';
                                                        break;
                                                    case '2':
                                                        $estado_pareo_text = 'ABON';
                                                        break;
                                                    case '3':
                                                        $estado_pareo_text = 'PEND';
                                                        break;
                                                }
                                            }


                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check d-flex justify-content-center align-items-center">
                                                        <input class="form-check-input ch_checkbox" name="ch_checkbox[]" type="checkbox" value="<?php echo $canalizacion["ID_DOC"] . ',' . $canalizacion["PAR_SISTEMA"]; ?>" data-column="1" onclick="toggleRowCheckbox(this)" <?php echo $disabled; ?>>
                                                        <input type="hidden" class="checkbox_type" value="ch">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check d-flex justify-content-center align-items-center">
                                                        <input class="form-check-input tr_checkbox" name="tr_checkbox[]" type="checkbox" value="<?php echo $canalizacion["ID_DOC"] . ',' . $canalizacion["PAR_SISTEMA"]; ?>" data-column="2" onclick="toggleRowCheckbox(this)" <?php echo $disabled; ?>>
                                                        <input type="hidden" class="checkbox_type" value="tr">
                                                    </div>
                                                </td>
                                                <td class="font_mini"><input type="hidden" id="id_doc" value="<?php echo $canalizacion["ID_DOC"]; ?>"></td>
                                                <td class="font_mini"><?php echo $canalizacion["CUENTA"]; ?></td>
                                                <td class="font_mini"><?php echo $canalizacion["F_VENC"]->format('Y/m/d'); ?></td>
                                                <td class="font_mini"><?php echo $canalizacion["F_REC"]->format('Y/m/d'); ?></td>
                                                <td class="font_mini"><?php echo $canalizacion["TRANSACCION"]; ?></td>
                                                <td class="font_mini"><?php echo trim($canalizacion["RUT_DEUDOR"]) ?></td>
                                                <td class="font_mini"><?php echo $canalizacion["N_DOC"]; ?></td>
                                                <td class="font_mini"><?php echo $canalizacion['DIAS_MORA'] ?></td>
                                                <td class="font_mini"><?php echo $canalizacion["CARTERA"]; ?></td>
                                                <td class="font_mini"><?php echo $estado_pareo_text ?></td>
                                                <td class="font_mini">$<?php echo number_format($canalizacion["MONTO_TRANSFERIDO"], 0, ',', '.'); ?></td>
                                                <td class="font_mini">$<?php echo number_format($canalizacion["MONTO_DOCUMENTO"], 0, ',', '.'); ?></td>
                                                <td class="font_mini">$<?php echo number_format($canalizacion["MONTO_CUBIERTO"], 0, ',', '.'); ?></td>
                                            </tr> <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->
                <input type="hidden" id="selected_ids_docs" name="selected_ids_docs[]">
                <input type="hidden" id="selected_ids_pareosistema" name="selected_ids_pareosistema[]">
                <input type="hidden" id="selected_types" name="selected_types[]">
            </form>
        </div><!-- container -->
        <?php include('footer.php'); ?>
    </div>
    <!-- end page content -->

    <script>
        window.onload = function() {
            // Oculta la pantalla de carga y muestra el contenido principal
            document.getElementById('loading-screen').style.display = 'none';
            document.getElementById('content').style.display = 'block';
        };
    </script>


    <script>
        function handleMasterCheckbox(column) {
            // Determinar si el checkbox maestro está marcado o desmarcado
            var isChecked = $('#select_all_checkbox' + column).is(':checked');

            // Desmarcar todos los checkboxes maestros
            $('#select_all_checkbox1, #select_all_checkbox2').not('#select_all_checkbox' + column).prop('checked', false);

            // Obtener la instancia de DataTable
            var table = $('#datatable2').DataTable();

            // Marcar o desmarcar los checkboxes en la columna seleccionada
            table.rows({
                search: 'applied'
            }).nodes().to$().each(function() {
                var row = $(this);
                var checkboxes = row.find('input[data-column="' + column + '"]');

                checkboxes.each(function() {
                    if (!$(this).is(':disabled')) { // Solo marca los checkboxes habilitados
                        this.checked = isChecked;
                    }
                });

                // Desmarcar los checkboxes de la columna opuesta
                var otherColumn = column === 1 ? 2 : 1;
                row.find('input[data-column="' + otherColumn + '"]').each(function() {
                    if (!$(this).is(':disabled')) { // Solo desmarca los checkboxes habilitados
                        this.checked = false;
                    }
                });
            });

            // Actualizar el estado del encabezado después de cambiar los checkboxes en las filas
            updateHeaderCheckboxState();
        }

        function toggleRowCheckbox(checkbox) {
            var row = $(checkbox).closest('tr');
            var checkboxes = row.find('input[type="checkbox"]');

            // Marcar solo el checkbox clickeado y desmarcar los demás en la misma fila
            checkboxes.each(function() {
                if (this !== checkbox) {
                    this.checked = false;
                }
            });

            // Actualizar el estado de los checkboxes maestros
            updateHeaderCheckboxState();
        }

        function updateHeaderCheckboxState() {
            var table = $('#datatable2').DataTable();

            // Comprobar si todos los checkboxes habilitados de la columna 1 están marcados
            var allCheckedColumn1 = table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="1"]').not(':disabled').length &&
                table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="1"]').filter(':checked').length ===
                table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="1"]').not(':disabled').length;

            // Comprobar si todos los checkboxes habilitados de la columna 2 están marcados
            var allCheckedColumn2 = table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="2"]').not(':disabled').length &&
                table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="2"]').filter(':checked').length ===
                table.rows({
                    search: 'applied'
                }).nodes().to$().find('input[data-column="2"]').not(':disabled').length;

            // Actualizar el estado de los checkboxes maestros
            $('#select_all_checkbox1').prop('checked', allCheckedColumn1);
            $('#select_all_checkbox2').prop('checked', allCheckedColumn2);
        }
    </script>

    <script>
        function habilitarBoton() {
            // Verifica si hay al menos un checkbox con las clases 'ch_checkbox' o 'tr_checkbox' marcado
            const checkboxesCh = document.querySelectorAll('.ch_checkbox:checked');
            const checkboxesTr = document.querySelectorAll('.tr_checkbox:checked');

            // Verifica el estado de los master checkboxes
            const masterCheckbox1 = document.getElementById('select_all_checkbox1').checked;
            const masterCheckbox2 = document.getElementById('select_all_checkbox2').checked;

            const botonGuardar = document.getElementById('guardarButton');

            if (checkboxesCh.length > 0 || checkboxesTr.length > 0 || masterCheckbox1 || masterCheckbox2) {
                botonGuardar.disabled = false;
            } else {
                botonGuardar.disabled = true;
            }
        }

        // Agrega el evento change a todos los checkboxes
        document.querySelectorAll('.ch_checkbox, .tr_checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', habilitarBoton);
        });

        // Agrega el evento change a los master checkboxes
        document.querySelectorAll('#select_all_checkbox1, #select_all_checkbox2').forEach(checkbox => {
            checkbox.addEventListener('change', habilitarBoton);
        });

        // Inicializa el estado del botón al cargar la página
        document.addEventListener('DOMContentLoaded', habilitarBoton);
    </script>

    <script>
        function valida_envia() {
            var selectedIdsDocs = [];
            var selectedIdsPareoSistema = [];
            var selectedTypes = [];

            // Obtener los checkboxes seleccionados, excluyendo los checkboxes maestros
            document.querySelectorAll('input[type=checkbox]:checked:not(#select_all_checkbox1):not(#select_all_checkbox2)').forEach(function(checkbox) {
                var ids = checkbox.value.split(',');
                var idDoc = ids[0];
                var idPareoSistema = ids[1];

                // Obtener el valor de data-column
                var checkboxType = checkbox.getAttribute('data-column');

                // Agregar valores a los arreglos
                selectedIdsDocs.push(idDoc);
                selectedIdsPareoSistema.push(idPareoSistema);
                selectedTypes.push(checkboxType);
            });

            // Asignar los valores a los campos ocultos
            document.getElementById('selected_ids_docs').value = selectedIdsDocs.join(',');
            document.getElementById('selected_ids_pareosistema').value = selectedIdsPareoSistema.join(',');
            document.getElementById('selected_types').value = selectedTypes.join(',');

            return true; // Asegúrate de que el formulario se envíe
        }
    </script>

</body>

<!-- jQuery  -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/metismenu.min.js"></script>
<script src="assets/js/waves.js"></script>
<script src="assets/js/feather.min.js"></script>
<script src="assets/js/simplebar.min.js"></script>
<script src="assets/js/moment.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Required datatable js -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="plugins/datatables/dataTables.buttons.min.js"></script>
<script src="plugins/datatables/buttons.bootstrap4.min.js"></script>
<script src="plugins/datatables/jszip.min.js"></script>
<script src="plugins/datatables/pdfmake.min.js"></script>
<script src="plugins/datatables/vfs_fonts.js"></script>
<script src="plugins/datatables/buttons.html5.min.js"></script>
<script src="plugins/datatables/buttons.print.min.js"></script>
<script src="plugins/datatables/buttons.colVis.min.js"></script>
<!-- Responsive examples -->
<script src="plugins/datatables/dataTables.responsive.min.js"></script>
<script src="plugins/datatables/responsive.bootstrap4.min.js"></script>
<script src="assets/pages/jquery.datatable.init.js"></script>
<!-- App js -->
<script src="assets/js/app.js"></script>
<script src="plugins/datatables/spanish.js"></script>
<script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>



<script>
    // Inicializar Feather Icons
    feather.replace();

    $(document).ready(function() {
        $('#idcliente').select2();
    });

    // Inicializar Tooltip de Bootstrap
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // DataTables Initialization
    $(document).ready(function() {

        $('#datatable2').on('change', '#select_all_checkbox1', function() {
            toggleAllCheckboxes(this, 1);
        });

        $('#datatable2').on('change', '#select_all_checkbox2', function() {
            toggleAllCheckboxes(this, 2);
        });

        var table = $('#datatable2').DataTable({
            responsive: true,
            order: [
                [4, 'asc'] // Ordena la columna 8 en orden ascendente
            ],
            columnDefs: [{
                    targets: 0,
                    orderable: false
                },
                {
                    targets: 1,
                    orderable: false
                },
                {
                    targets: 2,
                    visible: false
                },
                {
                    targets: 9,
                    render: function(data, type, row, meta) {
                        if (data > 170) { // Valor desde el cual se mostrará el texto en rojo
                            return '<span class="text-danger"><b>' + data + '</b></span>';
                        }
                        return data;
                    }
                }
            ],
            createdRow: function(row, data, dataIndex) {
                // Verifica el valor en la columna 7 (índice 7)
                if (data[9] > 170) { // Valor desde el cual se mostrará el fondo en rojo claro
                    $(row).css('background-color', 'rgba(255, 0, 0, 0.06)'); // Color rojo claro con transparencia
                }
                if (data[9] < 0) { // Valor desde el cual se mostrará el fondo en rojo claro
                    $(row).css('background-color', 'rgba(255, 255, 0, 0.15)'); // Color amarillo claro con transparencia
                }
            }
        });

        // Function to apply filters based on stored values
        function applyFilters() {
            var storedCuentaValue = sessionStorage.getItem('selected_cuenta');
            var storedFiltroValue = sessionStorage.getItem('selected_diasmora');
            var storedEstadoValue = sessionStorage.getItem('selected_estado');

            // Apply cuenta filter
            if (storedCuentaValue && storedCuentaValue !== "0") {
                $('#cuenta').val(storedCuentaValue).change();
            } else {
                $('#cuenta').val("0").change(); // Reset to default
            }

            // Apply dias_mora filter
            if (storedFiltroValue && storedFiltroValue !== "0") {
                $('#dias_mora').val(storedFiltroValue).change();
            } else {
                $('#dias_mora').val("0").change(); // Reset to default
            }

            // Apply estado filter
            if (storedEstadoValue && storedEstadoValue !== "0") {
                $('#estado_conc').val(storedEstadoValue).change();
            } else {
                $('#estado_conc').val("0").change(); // Reset to default
            }
        }

        // Custom filter function for dias_mora and estado
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var diasMoraFilter = $('#dias_mora').val();
                var estadoFilter = $('#estado_conc').val();
                var diasMoraValue = parseFloat(data[7]) || 0; // Convert the value to a number
                var estadoValue = data[10]; // Assuming column 9 is the ESTADO column

                // Filter by dias_mora
                if (diasMoraFilter === "1") {
                    if (diasMoraValue < 170) {
                        return false; // Exclude rows that don't meet the criteria
                    }
                }

                // Filter by estado
                if (estadoFilter !== "0" && estadoValue != estadoFilter) {
                    return false; // Exclude rows that don't match the estado filter
                }

                return true; // Show all rows that pass the filters
            }
        );

        // Add event listener to the cuenta select element
        $('#cuenta').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_cuenta', filterValue);

            if (filterValue == "0") {
                table.column(2).search('').draw(); // Clear the cuenta filter
            } else {
                table.column(2).search(filterValue).draw();
            }
        });

        // Add event listener to the dias_mora select element
        $('#dias_mora').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_diasmora', filterValue);

            // Redraw table to apply the dias_mora filter
            table.draw();
        });

        // Add event listener to the estado select element
        $('#estado_conc').on('change', function() {
            var filterValue = $(this).val();
            sessionStorage.setItem('selected_estado', filterValue);

            // Redraw table to apply the estado filter
            table.draw();
        });

        // Apply filters on page load
        applyFilters();
    });



    <?php if ($op == 1) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Canalizacion realizada con éxito.',
            showConfirmButton: true
        });
    <?php } ?>

    <?php if ($op == 2) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Estado actualizado.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>

    <?php if ($op == 3) { ?>
        Swal.fire({
            width: 600,
            icon: 'error',
            title: 'Error: Ya existe una conciliación para esta transacción.',
            showConfirmButton: false,
            timer: 3000,
        });
    <?php } ?>

    <?php if ($op == 4) { ?>
        Swal.fire({
            width: 600,
            icon: 'error',
            title: 'Error: Los documentos seleccionados, ya están conciliados.',
            showConfirmButton: false,
            timer: 2000,
        });
    <?php } ?>
</script>

</html>