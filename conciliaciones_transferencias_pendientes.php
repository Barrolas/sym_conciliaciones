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
    from dbo.Transferencias_Recibidas_Hist";
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
    <title>Pareo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                            <b>Transferencias recibidas</b>
                        </h3>
                    </div>
                </div>
                <div class="row mr-2">
                    <div class="col-12 mx-2">
                        <p>
                            Esta herramienta permite visualizar las transferencias que aún no han sido pareadas en el sistema. Las transferencias se identifican según el estado de su coincidencia a través de botones con distintos colores:
                        </p>
                        <ul>
                            <li><span class="text-info"><i class="feather-24 mr-2" data-feather="folder"></i></span><b>Rut coincidente:</b> Indica que la transferencia tiene coincidencia, ya que el RUT del ordenante coincide con el RUT del deudor.</li>
                            <li><span class="text-success"><i class="feather-24 mr-2" data-feather="plus"></i></span><b>Sin coincidencia:</b> Corresponde a transferencias donde el RUT del deudor no está determinado. Estas transferencias están disponibles para ser asignadas manualmente.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row text-start justify-content-start justify-items-stretch pl-4 mb-3">
                            <div class="col-lg-3">
                                <label class="col-9 mt-3" for="fecha_ultima_cartola">ÚLT ACTUALIZACIÓN</label>
                                <input type="text" class="form-control col-9" name="fecha_ultima_cartola" id="fecha_ultima_cartola" value="<?php echo $fecha_proceso ?>" disabled>
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
                                    <i class="fa fa-times pr-2"></i>LIMPIAR
                                </button>
                                <!-- <button id="testSave">Guardar preferencias manualmente</button> -->
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
                                        <th>F. RECEP</th>
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
                                                    <a data-toggle="tooltip" title="Ver documentos" href="conciliaciones_documentos.php?transaccion=<?php echo $transferencia["TRANSACCION"]; ?>&rut_ordenante=<?php echo $transferencia["RUT"]; ?>&cuenta=<?php echo $transferencia["CUENTA"]; ?>&matched=0" class="btn btn-icon btn-rounded btn-success ml-2">
                                                        <i class="feather-24" data-feather="plus"></i>
                                                    </a>
                                                </td>
                                            <?php } else { ?>
                                                <td class="col-1">
                                                    <a data-toggle="tooltip" title="Ver documentos" href="conciliaciones_documentos.php?transaccion=<?php echo $transferencia["TRANSACCION"]; ?>&rut_ordenante=<?php echo $transferencia["RUT"]; ?>&rut_deudor=<?php echo $transferencia["RUT_DEUDOR"]; ?>&cuenta=<?php echo $transferencia["CUENTA"]; ?>&matched=1" class="btn btn-icon btn-rounded btn-info ml-2">
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
        // Inicializar DataTable
        $('#datatable2').DataTable({
            "paging": false, // Desactiva la paginación
            "searching": true, // Habilita la búsqueda
            "ordering": true, // Habilita el ordenamiento
        });

        // Cargar valor de cuenta almacenado en localStorage
        var storedCuentaValue = localStorage.getItem('selected_cuenta');
        if (storedCuentaValue) {
            $('#cuenta_filter').val(storedCuentaValue); // Establecer el valor en el select
            filterTableByCuenta(); // Aplicar el filtro
        }

        // Evento para detectar cambios en el filtro de cuenta
        $('#cuenta_filter').on('change', function() {
            var selectedCuenta = $(this).val();
            localStorage.setItem('selected_cuenta', selectedCuenta); // Guardar la selección en localStorage
            filterTableByCuenta(); // Aplicar el filtro
        });

        // Función para filtrar la tabla por cuenta
        function filterTableByCuenta() {
            var selectedCuenta = $('#cuenta_filter').val();

            $('#datatable2 tbody tr').each(function() {
                var rowCuenta = $(this).find('td').eq(4).text().trim(); // Cambia el índice según tu tabla
                if (selectedCuenta === "0" || rowCuenta === selectedCuenta) {
                    $(this).show(); // Muestra la fila si coincide o si se selecciona "0"
                } else {
                    $(this).hide(); // Oculta la fila si no coincide
                }
            });
        }
    });



    // Función para manejar el checkbox de "Excluir"
    function handleExcludeCheckbox() {
        const exclude = document.getElementById('excluir_tags').checked;
        const filterData = JSON.parse(localStorage.getItem('filterData')) || {
            tags: [],
            exclude: false
        };

        // Actualizar el estado del filtro
        filterData.exclude = exclude;

        // Guardar el filtro en LocalStorage
        localStorage.setItem('filterData', JSON.stringify(filterData));

        applyTagFilter(); // Aplicar los filtros después de actualizar
    }

    // Función para manejar los filtros de etiquetas seleccionadas
    function handleTagFilterSelection() {
        const filterData = JSON.parse(localStorage.getItem('filterData')) || {
            tags: [],
            exclude: false
        };

        // Obtener las etiquetas seleccionadas desde los íconos del filtro
        const selectedIcons = document.querySelectorAll('.icon-filter-filter.selected');
        const tags = [];

        selectedIcons.forEach(icon => {
            tags.push(icon.getAttribute('data-tag'));
        });

        // Actualizar el estado de los filtros
        filterData.tags = tags;

        // Guardar los filtros en LocalStorage
        localStorage.setItem('filterData', JSON.stringify(filterData));

        applyTagFilter(); // Aplicar los filtros después de guardar
    }

    // Función para manejar el clic en los íconos de filtro
    function handleTagFilterClick(event) {
        const icon = event.target;
        const tag = icon.getAttribute('data-tag');

        if (icon.classList.contains('selected')) {
            icon.classList.remove('selected');
        } else {
            icon.classList.add('selected');
        }

        handleTagFilterSelection(); // Actualiza los filtros después del clic
    }

    // Asignar eventos a los íconos de filtro y al checkbox de "Excluir"
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.icon-filter-filter').forEach(icon => {
            icon.addEventListener('click', handleTagFilterClick);
        });

        // Asignar evento al checkbox "Excluir"
        document.getElementById('excluir_tags').addEventListener('change', handleExcludeCheckbox);

        // Cargar el estado del checkbox de "Excluir" desde LocalStorage
        const filterData = JSON.parse(localStorage.getItem('filterData')) || {
            exclude: false
        };
        document.getElementById('excluir_tags').checked = filterData.exclude;

        // Cargar las selecciones de etiquetas y aplicar los filtros
        loadTagSelection();
        applyTagFilter(); // Aplicar los filtros al cargar
    });

    // Función para cargar los filtros visualmente desde LocalStorage
    function loadFiltersFromLocalStorage() {
        const filterData = JSON.parse(localStorage.getItem('filterData')) || {
            exclude: false,
            tags: []
        };

        // Restaurar el estado visual del checkbox "Excluir"
        document.getElementById('excluir_tags').checked = filterData.exclude;

        // Restaurar los íconos de filtro seleccionados visualmente
        filterData.tags.forEach(tag => {
            const icon = document.querySelector(`.icon-filter-filter[data-tag="${tag}"]`);
            if (icon) {
                icon.classList.add('selected'); // Añadir la clase 'selected' a los íconos seleccionados
            }
        });
    }

    // Asignar eventos a los íconos de filtro
    document.addEventListener('DOMContentLoaded', () => {
        // Cargar los filtros visualmente desde LocalStorage al cargar la página
        loadFiltersFromLocalStorage();
    });

    // Función para aplicar los filtros de etiquetas a las filas de la tabla
    function applyTagFilter() {
        const selectedTags = JSON.parse(localStorage.getItem('selectedTags'));
        const filterData = JSON.parse(localStorage.getItem('filterData'));

        // Si no hay filtros o etiquetas seleccionadas, mostramos todos los registros
        if (!filterData || !selectedTags) {
            document.querySelectorAll('#datatable2 tbody tr').forEach(row => {
                row.style.display = ''; // Mostrar todas las filas si no hay filtros aplicados
            });
            return;
        }

        const shouldExclude = filterData.exclude;

        // Si no hay etiquetas seleccionadas en el filtro de la interfaz, mostramos todos los registros
        if (filterData.tags.length === 0) {
            document.querySelectorAll('#datatable2 tbody tr').forEach(row => {
                row.style.display = ''; // Mostrar todas las filas si no hay etiquetas seleccionadas
            });
            return;
        }

        // Recorrer todas las filas de la tabla
        document.querySelectorAll('#datatable2 tbody tr').forEach(row => {
            const transactionId = row.getAttribute('data-id'); // Usamos el data-id de cada fila
            const rowTags = selectedTags[transactionId] || [];

            const hasMatchingTag = rowTags.some(tag => filterData.tags.includes(tag));

            if (shouldExclude) {
                // Si "Excluir" está activado, ocultamos las filas que tienen etiquetas seleccionadas en el filtro
                if (hasMatchingTag) {
                    row.style.display = 'none'; // Ocultar la fila si tiene alguna de las etiquetas seleccionadas
                } else {
                    row.style.display = ''; // Mostrar la fila si no tiene ninguna de las etiquetas seleccionadas
                }
            } else {
                // Si "Excluir" NO está activado, solo mostramos las filas que tienen al menos una etiqueta seleccionada
                if (hasMatchingTag) {
                    row.style.display = ''; // Mostrar la fila si tiene al menos una de las etiquetas seleccionadas
                } else {
                    row.style.display = 'none'; // Ocultar la fila si no tiene ninguna de las etiquetas seleccionadas
                }
            }
        });
    }

    // Función para guardar las selecciones de etiquetas de las filas de la tabla en LocalStorage
    function saveTagSelection() {
        const selectedTags = {};

        // Recorrer todas las filas de la tabla
        document.querySelectorAll('#datatable2 tbody tr').forEach(row => {
            const transactionId = row.getAttribute('data-id');
            const rowTags = [];

            // Recoger las etiquetas seleccionadas en esta fila
            row.querySelectorAll('.icon-row-filter.selected').forEach(icon => {
                rowTags.push(icon.getAttribute('data-tag'));
            });

            if (rowTags.length > 0) {
                selectedTags[transactionId] = rowTags;
            }
        });

        // Guardar en LocalStorage
        localStorage.setItem('selectedTags', JSON.stringify(selectedTags));
    }

    // Función para cargar las etiquetas desde LocalStorage y aplicarlas a las filas de la tabla
    function loadTagSelection() {
        const selectedTags = JSON.parse(localStorage.getItem('selectedTags'));

        if (selectedTags) {
            document.querySelectorAll('#datatable2 tbody tr').forEach(row => {
                const transactionId = row.getAttribute('data-id');

                if (selectedTags[transactionId]) {
                    selectedTags[transactionId].forEach(tag => {
                        row.querySelector(`.icon-row-filter[data-tag="${tag}"]`).classList.add('selected');
                    });
                }
            });
        }
    }

    // Función para manejar el clic en las etiquetas dentro de la fila
    function handleRowTagClick(event) {
        const icon = event.target;
        const tag = icon.getAttribute('data-tag');
        const row = icon.closest('tr');
        const transactionId = row.getAttribute('data-id');

        if (icon.classList.contains('selected')) {
            icon.classList.remove('selected');
        } else {
            icon.classList.add('selected');
        }

        saveTagSelection();
        applyTagFilter(); // Aplicar los filtros después de guardar
    }

    // Asignar eventos de clic a las etiquetas dentro de las filas
    document.addEventListener('DOMContentLoaded', () => {
        loadTagSelection(); // Cargar las selecciones guardadas al cargar la página
        applyTagFilter(); // Aplicar los filtros al cargar

        // Asignar eventos de clic en las etiquetas de las filas
        document.querySelectorAll('.icon-row-filter').forEach(icon => {
            icon.addEventListener('click', handleRowTagClick);
        });
    });

    $(document).ready(function() {

        // Evento para el botón de limpiar filtros con confirmación Swal
        $('#clear-filters-btn').on('click', function() {
            Swal.fire({
                title: '¿Confirmas la acción?',
                text: "Esto eliminará todos los filtros y etiquetas aplicadas.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Llamar a la función para limpiar filtros
                    clearFilters();

                    // Mostrar mensaje de éxito y recargar la página
                    Swal.fire(
                        'Filtros limpiados',
                        'Todos los filtros y etiquetas se han eliminado.',
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página
                    });
                }
            });
        });

        // Función para limpiar filtros
        function clearFilters() {
            // Eliminar todos los filtros del LocalStorage
            localStorage.removeItem('selected_cuenta'); // Filtro de cuenta
            localStorage.removeItem('filterData'); // Datos de filtros y exclusión
            localStorage.removeItem('selectedTags'); // Selecciones de etiquetas en filas

            // Restablecer el filtro de cuenta
            $('#cuenta_filter').val("0").change(); // Cambiar el valor y disparar el evento de cambio

            // Restablecer el checkbox de excluir etiquetas
            $('#excluir_tags').prop('checked', false);

            // Restablecer los íconos de los filtros
            $('.icon-filter-filter').removeClass('selected fas').addClass('far'); // Íconos de la interfaz de filtros
            $('.icon-row-filter').removeClass('selected fas').addClass('far'); // Íconos en las filas de la tabla

            // Mostrar todas las filas de la DataTable
            $('#datatable2 tbody tr').each(function() {
                $(this).show();
            });
        }


    });
</script>

<script>
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