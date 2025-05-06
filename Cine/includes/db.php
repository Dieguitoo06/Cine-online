<?php
require_once 'config.php';

// Crear conexión a la base de datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar codificación UTF-8
$conn->set_charset("utf8");

// Función para limpiar datos de entrada
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para redirigir al usuario si no está logueado
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . APP_URL . "/auth/login.php");
        exit;
    }
}
?>