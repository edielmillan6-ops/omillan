<?php

require_once 'db.php';

// Validar que venga del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'] ?? '';
    $pwd   = $_POST['pwd'] ?? '';

    try {

        $db = conectarDB();

        $sql = "SELECT id_usuario, password, email 
                FROM usuarios 
                WHERE email = :email";

        $query = $db->prepare($sql);

        $query->execute([
            'email' => $email
        ]);

        $usuario = $query->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {

            // Verificar contraseña encriptada
            if (password_verify($pwd, $usuario['password'])) {

                session_start();
                $_SESSION['username'] = $usuario['email'];
                $_SESSION['id'] = $usuario['id_usuario'];

                header("Location: dashboard.php");
                exit;

            } else {
                echo "❌ Contraseña incorrecta";
            }

        } else {
            echo "❌ Usuario no encontrado";
        }

    } catch (PDOException $e) {
        echo "Error de BD: " . $e->getMessage();
    }

} else {
    echo "Acceso no permitido";
}
?>
