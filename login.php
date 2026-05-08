<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'] ?? '';
    $pwd   = $_POST['pwd'] ?? '';

    try {

        $db = conectarDB();

        $sql = "SELECT id_usuario, email, password 
                FROM usuarios 
                WHERE email = :email";

        $query = $db->prepare($sql);
        $query->execute(['email' => $email]);

        $usuario = $query->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {

            if (password_verify($pwd, $usuario['password'])) {

                session_start();
                $_SESSION['id'] = $usuario['id_usuario'];
                $_SESSION['email'] = $usuario['email'];

                // --- INICIO DE AGREGADO PARA COOKIES ---
                // Definimos el nombre y valor de la cookie basado en tu archivo "WhatsApp Image 2026-05-06 at 9.52.56 AM.jpeg"
                $cookie_name = "id_usuario";
                $cookie_value = $usuario['id_usuario'];
                // Expira en 30 días: 86400 segundos (1 día) * 30
                $expiry = time() + (86400 * 30);
                
                // Se crea la cookie para que esté disponible en todo el sitio ("/")
                setcookie($cookie_name, $cookie_value, $expiry, "/");
                // --- FIN DE AGREGADO PARA COOKIES ---

                header("Location: /omillan/dashboard.php");
                exit;

            } else {
                echo "❌ Contraseña incorrecta";
            }

        } else {
            echo "❌ Usuario no encontrado";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>