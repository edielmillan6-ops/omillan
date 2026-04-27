<?php
require_once 'db.php';

$db = conectarDB();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {

        // =========================
        // 🔥 LIBRO
        // =========================
        if (isset($_POST['titulo']) && isset($_POST['paginas'])) {

            $titulo = trim($_POST['titulo']);
            $paginas = (int) $_POST['paginas'];

            $sql = "INSERT INTO libros (titulo, numero_de_paginas) VALUES (:titulo, :paginas)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'titulo' => $titulo,
                'paginas' => $paginas
            ]);

            header("Location: dashboard.php?foco=on&seccion=libro&status=success");
            exit;
        }

        // =========================
        // 🔥 AUTOR
        // =========================
        elseif (isset($_POST['nombre'])) {

            $nombre = trim($_POST['nombre']);

            $sql = "INSERT INTO autores (nombre) VALUES (:nombre)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre
            ]);

            header("Location: dashboard.php?foco=on&seccion=autor&status=success");
            exit;
        }

        // =========================
        // 🔥 PRÉSTAMO
        // =========================
        elseif (isset($_POST['id_autor']) && isset($_POST['id_libro'])) {

            $id_autor = (int) $_POST['id_autor'];
            $id_libro = (int) $_POST['id_libro'];

            $sql = "INSERT INTO prestamos (id_autor, id_libro, fecha_prestamo)
                    VALUES (:id_autor, :id_libro, NOW())";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_autor' => $id_autor,
                'id_libro' => $id_libro
            ]);

            header("Location: dashboard.php?foco=on&seccion=prestamo&status=success");
            exit;
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}
?>