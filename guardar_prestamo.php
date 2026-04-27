<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger los IDs y asegurar que sean enteros
    $id_autor = (int) $_POST['id_autor'];
    $id_libro = (int) $_POST['id_libro'];

    if ($id_autor > 0 && $id_libro > 0) {
        try {
            $db = conectarDB();

            // Usamos el nombre de la tabla que pusiste: auto_libro
            $sql = "INSERT INTO auto_libro (id_autor, id_libro)
                    VALUES (:autor, :libro)";

            $query = $db->prepare($sql);
            $resultado = $query->execute([
                'autor' => $id_autor,
                'libro' => $id_libro
            ]);

            if ($resultado) {
                // 2. REDIRECCIÓN CON PARÁMETROS
                // foco=on: Mantiene la bombilla encendida
                // seccion=prestamo: Abre la pestaña de préstamos
                header("Location: dashboard.php?foco=on&seccion=prestamo&status=success");
                exit();
            }

        } catch (PDOException $e) {
            // Si hay error (por ejemplo, IDs que no existen), regresamos con aviso
            header("Location: dashboard.php?foco=on&seccion=prestamo&status=error");
            exit();
        }
    } else {
        header("Location: dashboard.php?foco=on&seccion=prestamo");
        exit();
    }
}
?>