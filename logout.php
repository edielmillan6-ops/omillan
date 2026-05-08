<?php
session_start();
session_unset();    // Elimina las variables de sesión
session_destroy();  // Destruye la sesión en el servidor

// --- INICIO DE AGREGADO PARA INVALIDAR LA COOKIE ---
// Para borrar una cookie, se pone una fecha de expiración en el pasado (time() - 3600)
if (isset($_COOKIE['id_usuario'])) {
    setcookie("id_usuario", "", time() - 3600, "/");
}
// --- FIN DE AGREGADO PARA INVALIDAR LA COOKIE ---

// Redirección (Cambiado a index.php según la lógica de tu imagen)
header("Location: index.php");
exit();
?>