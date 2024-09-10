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

$matched = 0;

if ($sistema == 'desarrollo') {
    $sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
    from dbo.Transferencias_Recibidas_Hist";
} else {
    $sql = "select CONVERT(varchar,MAX(FECHAProceso),20) as FECHAPROCESO
    from [192.168.1.193].conciliacion.dbo.Transferencias_Recibidas_Hist";
}

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
    <link href="assets/css/filters.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Plugins -->
    <script src="assets/js/sweetalert2/sweetalert2.all.min.js"></script>

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
                            <b>Transferencias pendientes</b>
                        </h3>
                    </div>
                    <div class="row mr-2">
                        <div class="col-12 mx-2">
                            <p>
                                Esta herramienta permite visualizar las transferencias aun no pareadas en el sistema. Las trasnferencias con
                                con botón celeste tienen match de que el rut ordenante es el mismo del rut deudor, en el caso de aquellas
                                con botón verde, son transferencias con rut deudor indeterminado <b>(No en uso aun)</b>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row text-start justify-content-start justify-items-stretch pl-4 mb-3">
                            <div class="col-lg-3">
                                <label class="col-4" for="fecha_ultima_cartola">ÚLT ACTUALIZACIÓN</label>
                                <input type="text" class="form-control col-6" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
                            </div>
                            <div class="col-lg-3 mt-3">
                                <div class="col-lg-9">
                                    <label for="cuenta_filter" class="col-3">CUENTA</label>
                                    <select name="cuenta_filter" id="cuenta_filter" class="form-control" maxlength="50" autocomplete="off">
                                        <option value="0" selected>Todas las cuentas</option>
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
                            <div id="filter-icons" class="col-lg-3 mt-4">
                                <div class="col-lg-6" id="filter-controls">
                                    <label for="excluir_tags">
                                        <input type="checkbox" id="excluir_tags"> Excluir
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <i class="far fa-star icon-filter-filter star" data-tag="star"></i>
                                    <i class="far fa-bell icon-filter-filter bell" data-tag="bell"></i>
                                    <i class="far fa-flag icon-filter-filter flag" data-tag="flag"></i>
                                </div>

                            </div>
                            <div class="col-lg-3 mt-5">
                                <button id="clear-filters-btn" class="btn btn-secondary">
                                    Limpiar filtros
                                </button>
                            </div>

                        </div><!--end form-group-->
                    </div><!--end col-->
                </div>

                <div class="col-12 px-3">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable2" class="table dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>FECHA REC.</th>
                                        <th>RUT ORD</th>
                                        <th>NOMBRE</th>
                                        <th>TRANSACCION</th>
                                        <th>CTA. BENEF</th>
                                        <th>MONTO</th>
                                        <th>ETIQUETAS</th>
                                        <th>ASIGNAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "EXEC [_SP_CONCILIACIONES_TRANSFERENCIAS_PENDIENTES_LISTA]";
                                    $stmt = sqlsrv_query($conn, $sql);
                                    if ($stmt === false) {
                                        die(print_r(sqlsrv_errors(), true));
                                    }
                                    while ($transferencia = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    ?>
                                        <tr data-id="<?php echo $transferencia["TRANSACCION"]; ?>">
                                            <td class="col-1"><?php echo $transferencia["FECHA"]; ?></td>
                                            <td class="col-1"> <?php echo $transferencia["RUT"]; ?></td>
                                            <td class="col-3"> <?php echo $transferencia["NOMBRE"]; ?></td>
                                            <td class="col-auto"> <?php echo $transferencia["TRANSACCION"]; ?></td>
                                            <td class="col-auto"><?php echo $transferencia["CUENTA"]; ?></td>
                                            <td class="col-auto">$<?php echo $transferencia["MONTO"]; ?></td>
                                            <td class="col-auto">
                                                <i class="far fa-star icon-row-filter star" data-tag="star"></i>
                                                <i class="far fa-bell icon-row-filter bell" data-tag="bell"></i>
                                                <i class="far fa-flag icon-row-filter flag" data-tag="flag"></i>
                                            </td>
                                            <?php if ($transferencia["RUT_DEUDOR"] == NULL) { ?>
                                                <td class="col-1">
                                                    <a data-toggle="tooltip" title="Ver gestiones" href="conciliaciones_documentos.php?transaccion=<?php echo $transferencia["TRANSACCION"]; ?>&rut_ordenante=<?php echo $transferencia["RUT"]; ?>&cuenta=<?php echo $transferencia["CUENTA"]; ?>&matched=0" class="btn btn-icon btn-rounded btn-success ml-2">
                                                        <i class="feather-24" data-feather="plus"></i>
                                                    </a>
                                                </td>
                                            <?php } else { ?>
                                                <td class="col-1">
                                                    <a data-toggle="tooltip" title="Ver gestiones" href="conciliaciones_documentos.php?transaccion=<?php echo $transferencia["TRANSACCION"]; ?>&rut_ordenante=<?php echo $transferencia["RUT"]; ?>&rut_deudor=<?php echo $transferencia["RUT_DEUDOR"]; ?>&cuenta=<?php echo $transferencia["CUENTA"]; ?>&matched=1" class="btn btn-icon btn-rounded btn-info ml-2">
                                                        <i class="feather-24" data-feather="folder"></i>
                                                    </a>
                                                </td>
                                            <?php }; ?>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->
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

    $(document).ready(function() {
        var table = $('#datatable2').DataTable();

        function applyFilters() {
            // Cargar valor de cuenta
            var storedCuentaValue = localStorage.getItem('selected_cuenta');
            if (storedCuentaValue) {
                $('#cuenta_filter').val(storedCuentaValue).change();
            }

            // Cargar longitud de la página
            var storedPageLength = localStorage.getItem('page_length');
            if (storedPageLength) {
                table.page.len(parseInt(storedPageLength)).draw();
            }

            // Cargar y aplicar el estado de las etiquetas seleccionadas en cada fila
            loadTagStates();

            // Cargar estado del checkbox de exclusión
            var storedExcludeState = localStorage.getItem('exclude_tags');
            $('#excluir_tags').prop('checked', storedExcludeState === 'true');

            // Cargar los filtros guardados y actualizar el estado visual
            var selectedTags = JSON.parse(localStorage.getItem('selected_tags')) || [];
            $('#filter-icons .icon-filter-filter').each(function() {
                var tag = $(this).data('tag');
                if (selectedTags.includes(tag)) {
                    $(this).addClass('selected fas').removeClass('far');
                } else {
                    $(this).removeClass('selected fas').addClass('far');
                }
            });

            // Verificar si el checkbox debe estar habilitado
            updateExcludeCheckboxState();

            // Aplicar los filtros a la tabla
            filterTable();
        }

        function loadTagStates() {
            var tagStates = JSON.parse(localStorage.getItem('tag_states')) || {};
            $('#datatable2 tbody tr').each(function() {
                var rowId = $(this).data('id');
                if (tagStates[rowId]) {
                    $(this).find('.icon-row-filter').each(function() {
                        var tag = $(this).data('tag');
                        if (tagStates[rowId].includes(tag)) {
                            $(this).addClass('selected fas').removeClass('far');
                        } else {
                            $(this).removeClass('selected fas').addClass('far');
                        }
                    });
                }
            });
        }

        function saveTagStates() {
            var tagStates = {};
            $('#datatable2 tbody tr').each(function() {
                var rowId = $(this).data('id');
                tagStates[rowId] = [];
                $(this).find('.icon-row-filter.selected').each(function() {
                    tagStates[rowId].push($(this).data('tag'));
                });
            });
            localStorage.setItem('tag_states', JSON.stringify(tagStates));
        }

        $('#datatable2').on('click', '.icon-row-filter', function() {
            var $icon = $(this);
            var isSelected = $icon.hasClass('selected');
            var rowId = $icon.closest('tr').data('id');

            $icon.toggleClass('selected', !isSelected);
            $icon.toggleClass('fas', !isSelected);
            $icon.toggleClass('far', isSelected);

            saveTagStates();
            updateExcludeCheckboxState(); // Actualiza el estado del checkbox "excluir"
            filterTable();
        });

        $('#filter-icons').on('click', '.icon-filter-filter', function() {
            var $icon = $(this);
            var isSelected = $icon.hasClass('selected');

            $icon.toggleClass('selected', !isSelected);
            $icon.toggleClass('fas', !isSelected);
            $icon.toggleClass('far', isSelected);

            saveSelectedTags();
            updateExcludeCheckboxState(); // Actualiza el estado del checkbox "excluir"
            filterTable();
        });

        function saveSelectedTags() {
            var selectedTags = [];
            $('#filter-icons .icon-filter-filter.selected').each(function() {
                selectedTags.push($(this).data('tag'));
            });
            localStorage.setItem('selected_tags', JSON.stringify(selectedTags));
        }

        $('#cuenta_filter').on('change', function() {
            var filterValue = $(this).val();
            localStorage.setItem('selected_cuenta', filterValue);
            filterTable();
        });

        $('#datatable2_length select').on('change', function() {
            var pageLength = $(this).val();
            localStorage.setItem('page_length', pageLength);
            table.page.len(parseInt(pageLength)).draw();
        });

        $('#excluir_tags').on('change', function() {
            var isChecked = $(this).is(':checked');
            localStorage.setItem('exclude_tags', isChecked);
            filterTable();
        });

        function filterTable() {
            var selectedTags = JSON.parse(localStorage.getItem('selected_tags')) || [];
            var selectedCuenta = $('#cuenta_filter').val();
            var excludeTags = $('#excluir_tags').is(':checked');

            table.rows().every(function() {
                var row = this.node();
                var rowTags = [];
                var rowCuenta = $(row).find('td').eq(4).text(); // Cambia el índice de la columna según tu tabla

                $(row).find('.icon-row-filter').each(function() {
                    if ($(this).hasClass('selected')) {
                        rowTags.push($(this).data('tag'));
                    }
                });

                var tagMatch = selectedTags.length === 0 || selectedTags.some(tag => rowTags.includes(tag));
                if (excludeTags) {
                    tagMatch = !tagMatch; // Invertir la lógica si está marcado el checkbox
                }
                var cuentaMatch = selectedCuenta === "0" || rowCuenta === selectedCuenta;

                if (tagMatch && cuentaMatch) {
                    $(row).show();
                } else {
                    $(row).hide();
                }
            });
        }

        function updateExcludeCheckboxState() {
            var hasSelectedTags = $('#datatable2 tbody .icon-row-filter.selected').length > 0;
            $('#excluir_tags').prop('disabled', !hasSelectedTags);
        }

        // Función para limpiar todos los filtros y etiquetas guardados en localStorage
        function clearFilters() {
            // Remover los valores relevantes de localStorage
            localStorage.removeItem('selected_cuenta');
            localStorage.removeItem('page_length');
            localStorage.removeItem('exclude_tags');
            localStorage.removeItem('selected_tags');
            localStorage.removeItem('tag_states');

            // Restablecer los campos de filtro a sus valores predeterminados
            $('#cuenta_filter').val("0").change(); // Restablecer filtro de cuenta
            $('#excluir_tags').prop('checked', false); // Restablecer checkbox de exclusión
            $('#filter-icons .icon-filter-filter').removeClass('selected fas').addClass('far'); // Restablecer los íconos de filtro

            // Recargar la tabla sin los filtros aplicados
            table.search('').columns().search('').draw();
        }

        // Manejo del botón "Limpiar filtros"
        $('#clear-filters-btn').on('click', function() {
            // Confirmar con SweetAlert
            Swal.fire({
                title: '¿Confirmas la acción?',
                text: "Esto eliminará todos los filtros y etiquetas aplicadas",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Limpiar los filtros si el usuario confirma
                    clearFilters();

                    // Mostrar una notificación de éxito
                    Swal.fire(
                        'Filtros limpiados',
                        'Todos los filtros y etiquetas se han eliminado.',
                        'success'
                    ).then(() => {
                        // Recargar la página después de limpiar
                        location.reload();
                    });
                }
            });
        });

        applyFilters();
    });

    <?php if ($op == 1) { ?>
        Swal.fire({
            width: 600,
            icon: 'success',
            title: 'Pareo realizado con éxito.',
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