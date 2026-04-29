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
    /* --- NUEVA PALETA DE COLORES "BIBLIOTECA" --- */
    :root {
        /* Tonos de madera y papel */
        --lib-wood-dark: #5D4037;   /* Café madera oscuro */
        --lib-wood-light: #8D6E63;  /* Café madera claro */
        --lib-gold: #D4AF37;       /* Oro antiguo / Detalles */
        
        /* Tonos de fondo y texto */
        --lib-paper: #fdf8f5;      /* Crema papel envejecido */
        --lib-text: #212121;       /* Texto casi negro */
        --lib-text-muted: #757575; /* Texto secundario */
        
        /* Capa superpuesta para el fondo de imagen */
        --lib-bg-overlay: rgba(33, 33, 33, 0.6); /* Oscurece la foto de fondo */
    }

    body {
        font-family: 'Segoe UI', Roboto, sans-serif;
        color: var(--lib-text);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        
        /* --- IMAGEN DE FONDO REAL --- */
        /* Imagen de biblioteca real con alta resolución */
        background-image: url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=2070&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
        background-attachment: fixed; /* Mantiene el fondo fijo al hacer scroll */
        position: relative;
    }

    /* Capa para oscurecer la imagen de fondo y que resalte el contenido */
    body::before {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        background-color: var(--lib-bg-overlay);
        z-index: -1;
    }

    .container {
        position: relative;
        z-index: 1; /* Asegura que el contenido esté sobre la capa oscura */
    }

    /* --- ENCABEZADO "ESTILO BARÇA" -> "ESTILO BIBLIOTECA" --- */
    /* Reemplazamos el degradado blaugrana por madera oscura */
    .bg-fcb-gradient {
        background-color: var(--lib-wood-dark);
        color: var(--lib-paper) !important;
        border: 2px solid var(--lib-gold);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    .bg-fcb-gradient h3 {
        color: var(--lib-paper) !important;
        font-family: 'Georgia', serif; /* Un toque más clásico */
    }
    
    .bg-fcb-gradient .btn-outline-light {
        color: var(--lib-gold);
        border-color: var(--lib-gold);
    }
    .bg-fcb-gradient .btn-outline-light:hover {
        background-color: var(--lib-gold);
        color: var(--lib-wood-dark);
    }

    /* --- TARJETAS (CARDS) --- */
    .card {
        border-radius: 12px;
        transition: transform 0.2s;
        /* Usamos el color papel con un poco de transparencia */
        background-color: rgba(254, 204, 172, 0.95); 
        border: 1px solid rgba(141, 110, 99, 0.2);
        box-shadow: 0 8px 30px rgba(0,0,0,0.2) !important;
    }

    .card-body h5 {
        color: var(--lib-wood-dark);
        font-family: 'Georgia', serif;
    }

    /* --- PANEL DEL FOCO --- */
    .foco-container {
        background: white;
        border: 2px solid #D7CCC8; /* Madera muy clara */
        box-shadow: inset 0 0 15px rgba(0,0,0,0.03);
    }

    /* Botones de Encender/Apagar */
    .btn-warning {
        background-color: var(--lib-gold);
        border-color: var(--lib-gold);
        color: var(--lib-wood-dark);
    }
    .btn-warning:hover {
        background-color: #C19A2D; /* Oro más oscuro */
        border-color: #C19A2D;
    }

    /* --- BOTONES DE GESTIÓN (NAVEGACIÓN) --- */
    .btn-gestion {
        border-width: 2px;
        font-weight: 600;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.85rem;
    }

    /* Libros (Verde estantería) */
    .btn-outline-success {
        color: #388E3C;
        border-color: #388E3C;
    }
    .btn-outline-success:hover { 
        background-color: #388E3C; 
        color: white; 
    }

    /* Autores (Marrón madera) */
    .btn-outline-primary {
        color: var(--lib-wood-light);
        border-color: var(--lib-wood-light);
    }
    .btn-outline-primary:hover { 
        background-color: var(--lib-wood-light); 
        color: white; 
    }

    /* Préstamos (Oro/Detalles) */
    .btn-outline-warning {
        color: #A1887F; /* Marrón grisáceo */
        border-color: #A1887F;
    }
    .btn-outline-warning:hover { 
        background-color: #A1887F; 
        color: white; 
    }

    /* --- TABLAS "ESTILO PAPEL Y MADERA" --- */
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #D7CCC8;
    }

    .table {
        background-color: white;
        color: var(--lib-text);
        margin-bottom: 0;
    }

    /* Cabecera de la tabla estilo madera */
    .table thead {
        background-color: var(--lib-wood-light);
        color: white;
    }
    
    .table thead th {
        border: none;
        padding: 12px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(212, 175, 55, 0.05); /* Resaltado color oro muy suave */
    }

    /* Estilos específicos para las tablas de cada sección (color de cabecera) */
    #libro .table thead { background-color: #43A047; } /* Verde Libros */
    #autor .table thead { background-color: var(--lib-wood-dark); } /* Café Autores */
    #prestamo .table thead { background-color: #A1887F; } /* Café suave Préstamos */

    /* Badges de páginas */
    .badge.bg-light.text-dark.border {
        background-color: #e86f33 !important;
        color: var(--lib-wood-dark) !important;
        border-color: #dd6235 !important;
    }
</style>
</head>

<body class="bg-light">

<div class="container mt-4" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded shadow-sm" 
         style="background-color: rgba(254, 204, 172, 0.95) !important; border: 1px solid rgba(141, 110, 99, 0.2);">
        
        <h3 class="mb-0" style="color: #5D4037; font-family: 'Georgia', serif; font-weight: bold;">
            📚 Biblioteca Omillan
        </h3>
        
        <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold">Salir</a>
    </div>
</div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <p class="small text-uppercase fw-bold text-secondary">Estado del Sistema</p>
                    <div class="d-flex justify-content-center align-items-center bg-light rounded-circle mx-auto mb-4" style="width: 140px; height: 140px;">
                        <img id="lightbulb" src="./wwwroot/img/bulboff.gif" width="100" alt="Foco">
                    </div>

                    <div class="btn-group w-100 mb-4" role="group">
                        <button class="btn btn-warning fw-bold" onclick="encenderFoco()">Encender 💡</button>
                        <button class="btn btn-dark" onclick="apagarFoco()">Apagar</button>
                    </div>

                    <hr>

                    <p class="small text-uppercase fw-bold text-secondary">Navegación</p>
                    <div class="d-grid gap-2">
                        <button id="btnLibro" class="btn btn-outline-success" disabled onclick="mostrar('libro')">Gestionar Libros</button>
                        <button id="btnAutor" class="btn btn-outline-primary" disabled onclick="mostrar('autor')">Gestionar Autores</button>
                        <button id="btnPrestamo" class="btn btn-outline-warning" disabled onclick="mostrar('prestamo')">Nuevo Préstamo</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                        <div class="alert alert-success border-0 shadow-sm py-2">✅ Guardado correctamente</div>
                    <?php endif; ?>

                    <div id="placeholder" class="text-center text-muted mt-5">
                        <i class="fas fa-arrow-left d-block mb-2"></i>
                        Enciende el foco para comenzar a gestionar.
                    </div>

                    <div id="libro" class="seccion d-none">
                        <h5 class="mb-3 text-success">Registrar Libro</h5>
                        <form method="POST" class="mb-4">
                            <input class="form-control mb-2" name="titulo" placeholder="Título del libro" required>
                            <input class="form-control mb-2" name="paginas" type="number" placeholder="Número de páginas" required>
                            <button class="btn btn-success w-100 shadow-sm">Guardar Libro</button>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Págs.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($libros as $l): ?>
                                    <tr>
                                        <td><?= $l['id_libro'] ?></td>
                                        <td><?= htmlspecialchars($l['titulo']) ?></td>
                                        <td><?= $l['numero_de_paginas'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="autor" class="seccion d-none">
                        <h5 class="mb-3 text-primary">Registrar Autor</h5>
                        <form method="POST" class="mb-4">
                            <input class="form-control mb-2" name="nombre" placeholder="Nombre completo" required>
                            <button class="btn btn-primary w-100 shadow-sm">Guardar Autor</button>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-primary text-white">
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
                    </div>

                    <div id="prestamo" class="seccion d-none">
                        <h5 class="mb-3 text-warning">Nuevo Préstamo</h5>
                        <form method="POST" class="mb-4 bg-light p-3 rounded">
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-muted">Autor</label>
                                <select class="form-select" name="id_autor" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($autores as $a): ?>
                                        <option value="<?= $a['id_autor'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Libro</label>
                                <select class="form-select" name="id_libro" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($libros as $l): ?>
                                        <option value="<?= $l['id_libro'] ?>"><?= htmlspecialchars($l['titulo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-warning w-100 fw-bold shadow-sm">Confirmar Préstamo</button>
                        </form>
                        <table class="table table-striped table-sm border">
                            <thead class="bg-light">
                                <tr>
                                    <th>Autor</th>
                                    <th>Libro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prestamos as $p): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($p['autor']) ?></td>
                                    <td class="small"><?= htmlspecialchars($p['libro']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Referencias a botones para mayor limpieza
const btnLibro = document.getElementById('btnLibro');
const btnAutor = document.getElementById('btnAutor');
const btnPrestamo = document.getElementById('btnPrestamo');
const placeholder = document.getElementById('placeholder');

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
    placeholder.classList.remove('d-none');
}

function mostrar(id){
    ocultar();
    placeholder.classList.add('d-none');
    document.getElementById(id).classList.remove('d-none');
}

function ocultar(){
    ['libro','autor','prestamo'].forEach(x=>{
        document.getElementById(x).classList.add('d-none');
    });
}

window.onload = function() {
    const p = new URLSearchParams(window.location.search);
    if (p.get('foco') === 'on') {
        encenderFoco();
        const seccion = p.get('seccion');
        if (seccion) mostrar(seccion);
    }
};
</script>

</body>
</html>
