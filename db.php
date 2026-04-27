<?php
function conectarDB() {
    $host = "localhost";
    $dbname = "omillan_db"; // cambia si tu BD tiene otro nombre
    $user = "root";
    $pass = "1234";

    try {
        $conexion = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8",
            $user,
            $pass
        );

        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conexion;

    } catch (PDOException $e) {
        echo "Error de conexión: " . $e->getMessage();
        exit;
    }
}
?>
