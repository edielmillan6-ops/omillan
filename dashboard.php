<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.html");
    exit();
}

require_once 'db.php';
$db = conectarDB();

// 🔥 OBTENER DATOS
$libros = $db->query("SELECT * FROM libros")->fetchAll(PDO::FETCH_ASSOC);
$autores = $db->query("SELECT * FROM autores")->fetchAll(PDO::FETCH_ASSOC);

$prestamos = $db->query("
    SELECT a.nombre AS autor, l.titulo AS libro
    FROM auto_libro al
    JOIN autores a ON al.id_autor = a.id_autor
    JOIN libros l ON al.id_libro = l.id_libro
")->fetchAll(PDO::FETCH_ASSOC);

// 🔥 GUARDAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // LIBRO
    if (isset($_POST['titulo']) && isset($_POST['paginas'])) {
        $stmt = $db->prepare("INSERT INTO libros (titulo, numero_de_paginas) VALUES (?, ?)");
        $stmt->execute([$_POST['titulo'], $_POST['paginas']]);
        header("Location: dashboard.php?foco=on&seccion=libro&status=success");
        exit();
    }

    // AUTOR
    if (isset($_POST['nombre']) && !isset($_POST['id_libro'])) {
        $stmt = $db->prepare("INSERT INTO autores (nombre) VALUES (?)");
        $stmt->execute([$_POST['nombre']]);
        header("Location: dashboard.php?foco=on&seccion=autor&status=success");
        exit();
    }

    // PRESTAMO
    if (isset($_POST['id_autor']) && isset($_POST['id_libro'])) {
    // ASIGNACIÓN: Debes pasar los datos del POST a variables
    $id_autor = $_POST['id_autor'];
    $id_libro = $_POST['id_libro'];

    $sql = "INSERT INTO auto_libro (id_autor, id_libro) VALUES (?, ?)";
    $stmt = $db->prepare($sql);
    
    // Ejecutar con las variables ya capturadas
    if ($stmt->execute([$id_autor, $id_libro])) {
        header("Location: dashboard.php?foco=on&seccion=prestamo&status=success");
        exit();
    } else {
        echo "Error al insertar el registro.";
    }
  }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="./wwwroot/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./wwwroot/css/bootstrap-icons.min.css">

  <style>
  body {
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
    url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f');
    background-size: cover;
    color: white;
  }

  .seccion {
    background: rgba(255,255,255,0.95);
    padding: 20px;
    border-radius: 15px;
    color: #1b4332;
  }
</style>
</head>

<body>

<div class="container mt-4 text-center">
    <h3>📚 Biblioteca Omillan</h3>
    <a href="logout.php" class="btn btn-light btn-sm">Salir</a>

    <!-- FOCO -->
    <div class="mt-4">
        <img id="lightbulb" src="./wwwroot/img/bulboff.gif" width="120">
    </div>

    <!-- BOTONES -->
    <div class="mt-3">
        <button id="btnLibro" class="btn btn-success" disabled onclick="mostrar('libro')">Libro</button>
        <button id="btnAutor" class="btn btn-primary" disabled onclick="mostrar('autor')">Autor</button>
        <button id="btnPrestamo" class="btn btn-warning" disabled onclick="mostrar('prestamo')">Préstamo</button>
    </div>

    <div class="mt-3">
        <button class="btn btn-dark" onclick="encenderFoco()">Encender 💡</button>
        <button class="btn btn-secondary" onclick="apagarFoco()">Apagar</button>
    </div>

    <!-- LIBRO -->
    <div id="libro" class="seccion mt-4 d-none">

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success">Guardado correctamente ✅</div>
        <?php endif; ?>

        <h5>Registrar Libro</h5>
        <form method="POST">
            <input class="form-control mb-2" name="titulo" placeholder="Título" required>
            <input class="form-control mb-2" name="paginas" type="number" placeholder="Páginas" required>
            <button class="btn btn-success w-100">Guardar</button>
        </form>

        <hr>

      <table class="table table-dark">
    <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Páginas</th>
    </tr>

    <?php foreach ($libros as $l): ?>
    <tr>
        <td><?= $l['id_libro'] ?></td>
        <td><?= $l['titulo'] ?></td>
        <td><?= $l['numero_de_paginas'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
    </div>

    <!-- AUTOR -->
    <div id="autor" class="seccion mt-4 d-none">
        <h5>Registrar Autor</h5>
        <form method="POST">
            <input class="form-control mb-2" name="nombre" placeholder="Nombre" required>
            <button class="btn btn-primary w-100">Guardar</button>
        </form>

        <hr>
        <table class="table table-striped table-sm">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($autores as $a): ?>
        <tr>
            <td><?= $a['id_autor'] ?></td>
            <td><?= htmlspecialchars($a['nombre']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
    </div>

    <!-- PRESTAMO -->
    <div id="prestamo" class="seccion mt-4 d-none">
        <h5>Nuevo Préstamo</h5>
        <form method="POST">
    
    <!-- SELECT AUTOR -->
    <label>Autor</label>
    <select class="form-control mb-2" name="id_autor" required>
        <option value="">Seleccione un autor</option>
        <?php foreach ($autores as $a): ?>
            <option value="<?= $a['id_autor'] ?>">
                <?= htmlspecialchars($a['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- SELECT LIBRO -->
    <label>Libro</label>
    <select class="form-control mb-2" name="id_libro" required>
        <option value="">Seleccione un libro</option>
        <?php foreach ($libros as $l): ?>
            <option value="<?= $l['id_libro'] ?>">
                <?= htmlspecialchars($l['titulo']) ?>
            </option>
        <?php endforeach; ?>
    </select>

      <button class="btn btn-warning w-100">Confirmar Préstamo</button>
    </form>

        <table class="table table-striped table-sm">
    <thead>
        <tr>
            <th>Autor</th>
            <th>Libro</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($prestamos as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['autor']) ?></td>
            <td><?= htmlspecialchars($p['libro']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
    </div>

</div>

<script>
function encenderFoco(){
    document.getElementById('lightbulb').src = './wwwroot/img/bulbon.gif';
    btnLibro.disabled = false;
    btnAutor.disabled = false;
    btnPrestamo.disabled = false;
}

function apagarFoco(){
    document.getElementById('lightbulb').src = './wwwroot/img/bulboff.gif';
    btnLibro.disabled = true;
    btnAutor.disabled = true;
    btnPrestamo.disabled = true;
    ocultar();
}

function mostrar(id){
    ocultar();
    document.getElementById(id).classList.remove('d-none');
}

function ocultar(){
    ['libro','autor','prestamo'].forEach(x=>{
        document.getElementById(x).classList.add('d-none');
    });
}

// Mantener estado
window.onload = function() {
    const p = new URLSearchParams(window.location.search);
    if (p.get('foco') === 'on') {
        encenderFoco();
        if (p.get('seccion')) mostrar(p.get('seccion'));
    }
};
</script>

</body>
</html>
