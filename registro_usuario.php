<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.html");
    exit;
}

$db = conectarDB();

$nombre = $_POST['nombre'] ?? '';
$email  = $_POST['email'] ?? '';
$pwd    = $_POST['pwd'] ?? '';

// 🔥 SOLO validar nombre si realmente viene del registro
if (!isset($_POST['nombre'])) {
    header("Location: index.html");
    exit;
}

if (empty($nombre) || empty($email) || empty($pwd)) {
    echo "❌ Todos los campos son obligatorios";
    exit;
}

try {

    // Verificar duplicado
    $sqlCheck = "SELECT * FROM usuarios WHERE email = :email";
    $q = $db->prepare($sqlCheck);
    $q->execute(['email' => $email]);

    if ($q->fetch()) {
        echo "⚠️ El correo ya está registrado";
        exit;
    }

    // Encriptar
    $passwordHash = password_hash($pwd, PASSWORD_DEFAULT);

    // Insertar
    $sql = "INSERT INTO usuarios (nombre, email, password)
            VALUES (:nombre, :email, :password)";

    $query = $db->prepare($sql);
    $query->execute([
        'nombre'   => $nombre,
        'email'    => $email,
        'password' => $passwordHash
    ]);

    session_start();
    $_SESSION['id'] = $db->lastInsertId();
    $_SESSION['email'] = $email;

    header("Location: dashboard.php");
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
