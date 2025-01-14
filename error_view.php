<?php

/**
 * Función para mostrar una pantalla de error moderna.
 * @param string $mensaje El mensaje principal del error.
 * @param string $soporte Un mensaje adicional para indicar qué hacer o con quién contactar.
 */
function mostrarError($mensaje, $soporte = "Por favor, contacte con soporte técnico para resolver este problema.")
{
    // Obtener la página donde ocurre el error
    $paginaError = $_SERVER['REQUEST_URI'];

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
            <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Feather Icons -->
        <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
        <style>
            .error-detail {
                max-height: 80vh; /* El texto no excederá el 80% del viewport */
                overflow: auto; /* Permitir desplazamiento si es necesario */
            }
            textarea {
                display: none; /* Ocultar el textarea usado para copiar */
            }
        </style>
    </head>
    <body class="bg-light d-flex justify-content-center align-items-center vh-100">
        <div class="container text-center">
            <div class="alert shadow-lg p-4 rounded position-relative">
                <h1><i data-feather="alert-triangle" class="text-danger"></i> Error <i data-feather="alert-triangle" class="text-danger"></i></h1>
                <p class="lead">Ocurrió un problema:</p>
                <!-- Detalle del error con formato -->
                <div id="errorMessageFormatted" class="error-detail text-start mb-3">
                    <p><b>Página:</b> ' . htmlspecialchars($paginaError) . '</p>
                    <p><b>Detalle:</b> ' . htmlspecialchars($mensaje) . '</p>
                </div>
                <!-- Cuadro de texto oculto para la copia -->
                <textarea id="errorMessage">
Página: ' . htmlspecialchars($paginaError) . '
Detalle: ' . htmlspecialchars($mensaje) . '</textarea>
                <hr>
                <!-- Botones alineados -->
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-secondary" id="copyButton">
                        <i data-feather="copy"></i> Copiar mensaje
                    </button>
                    <a href="menu_principal.php" class="btn btn-primary">
                        <i data-feather="home"></i> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Feather Icons Initialization -->
        <script>
            feather.replace();

            document.getElementById("copyButton").addEventListener("click", function() {
                const textarea = document.getElementById("errorMessage");
                const copyButton = document.getElementById("copyButton");

                // Seleccionar el texto del textarea oculto
                textarea.style.display = "block"; // Mostrar temporalmente para copiar
                textarea.select();
                textarea.setSelectionRange(0, 99999); // Para dispositivos móviles

                try {
                    // Ejecutar el comando de copiar
                    document.execCommand("copy");

                    // Cambiar el texto e icono del botón
                    copyButton.innerHTML = `<i data-feather="check-circle"></i> Copiado`;
                    copyButton.classList.remove("btn-secondary");
                    copyButton.classList.add("btn-success");
                    feather.replace(); // Actualizar íconos

                    // Restaurar el botón después de 3 segundos
                    setTimeout(() => {
                        copyButton.innerHTML = `<i data-feather="copy"></i> Copiar mensaje`;
                        copyButton.classList.remove("btn-success");
                        copyButton.classList.add("btn-secondary");
                        feather.replace(); // Actualizar íconos
                    }, 3000);
                } catch (err) {
                    console.error("Error al copiar el mensaje:", err);
                } finally {
                    textarea.style.display = "none"; // Ocultar nuevamente
                }
            });
        </script>
    </body>
    </html>';
    exit;
}