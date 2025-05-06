<?php 
require_once 'config.php';
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>../css/styles.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <a href="<?php echo APP_URL; ?>">CineOnline</a>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>">Inicio</a></li>
                    <li><a href="<?php echo APP_URL; ?>../reservas">Películas</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo APP_URL; ?> ../perfil/index.php">Mi Perfil</a></li>
                        <li><a href="<?php echo APP_URL; ?> ../auth/logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo APP_URL; ?> ../auth/login.php">Iniciar Sesión</a></li>
                        <li><a href="<?php echo APP_URL; ?> ../auth/register.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>