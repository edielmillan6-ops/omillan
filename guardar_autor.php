<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validar que el campo no esté vacío
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

    if (!empty($nombre)) {
        try {
            $db = conectarDB();

            $sql = "INSERT INTO autores (nombre) VALUES (:nombre)";
            $query = $db->prepare($sql);
            
            $resultado = $query->execute([
                'nombre' => $nombre
            ]);

            if ($resultado) {
                // 2. REDIRECCIÓN INTELIGENTE
                // foco=on -> Mantiene la bombilla encendida
                // seccion=autor -> Abre la tabla de autores automáticamente
                // status=success -> Para mostrar un mensaje si quieres
                header("Location: dashboard.php?foco=on&seccion=autor&status=success");
                exit(); 
            }

        } catch (PDOException $e) {
            // Es mejor redirigir con un error para no romper la estética del dashboard
            header("Location: dashboard.php?foco=on&seccion=autor&status=error");
            exit();
        }
    } else {
        // Si el nombre iba vacío, regresamos al dashboard
        header("Location: dashboard.php?foco=on&seccion=autor");
        exit();
    }
}
?>