<?php
// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '2006');
define('DB_NAME', 'cine_online');

// Configuración de la aplicación
define('APP_NAME', 'CineOnline');
define('APP_URL', 'http://localhost/CINE');

// Zona horaria
date_default_timezone_set('America/Mexico_City'); // Cambia según tu ubicación

// Inicio de sesión
session_start();
?>