<?php
require_once 'db.php';
$db = conectarDB();

$mensaje = ""; // Variable para feedback

/* =========================
    GUARDAR LIBRO
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['titulo'])) {

    $titulo = $_POST['titulo'];
    $paginas = (int) $_POST['paginas'];

    try {
        $sql = "INSERT INTO libros (titulo, numero_de_paginas) VALUES (:titulo, :paginas)";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([
            'titulo' => $titulo,
            'paginas' => $paginas
        ]);

        if ($res) {
            // Redirigir para evitar reenvío de formulario y refrescar la lista
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
            exit;
        }
    } catch (PDOException $e) {
        $mensaje = "Error al guardar: " . $e->getMessage();
    }
}

// Capturar mensaje de éxito de la URL
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $mensaje = "¡Libro registrado correctamente!";
}

/* =========================
    CONSULTAR LIBROS
========================= */
$sql = "SELECT * FROM libros";
$stmt = $db->query($sql);
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Libros</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .success { color: white; background-color: #28a745; padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #004d99; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <?php if ($mensaje): ?>
        <div class="success"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <h2>Registrar nuevo libro</h2>
    <form method="POST">
        <input type="text" name="titulo" placeholder="Título del libro" required>
        <input type="number" name="paginas" placeholder="Número de páginas" required>
        <button type="submit" style="background-color: #a50044; color: white; border: none; padding: 5px 15px; cursor: pointer;">
            Guardar Libro
        </button>
    </form>

    <hr>

    <h2>Inventario de Libros</h2>

    <?php if (count($libros) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Páginas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($libros as $libro): ?>
                <tr>
                    <td><?= htmlspecialchars($libro['id']) ?></td>
                    <td><?= htmlspecialchars($libro['titulo']) ?></td>
                    <td><?= htmlspecialchars($libro['numero_de_paginas']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No se encontraron registros en la base de datos.</p>
    <?php endif; ?>

</body>
</html>