<?php
session_start();
// Finalmente, destruir la sesión.
session_destroy();
header("Location: login.php");
?>