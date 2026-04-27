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
