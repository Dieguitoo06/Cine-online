<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php?error=acceso_denegado");
    exit();
}

// Obtener estadísticas básicas para el dashboard
$stmt = $conn->prepare("SELECT COUNT(*) AS total_peliculas FROM peliculas");
$stmt->execute();
$total_peliculas = $stmt->fetch(PDO::FETCH_ASSOC)['total_peliculas'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total_usuarios FROM usuarios");
$stmt->execute();
$total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total_usuarios'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total_reservas FROM reservas");
$stmt->execute();
$total_reservas = $stmt->fetch(PDO::FETCH_ASSOC)['total_reservas'];

// Obtener las últimas 5 reservas
$stmt = $conn->prepare("SELECT r.id, u.nombre, p.titulo, r.fecha_reserva, r.estado 
                        FROM reservas r 
                        JOIN usuarios u ON r.usuario_id = u.id 
                        JOIN peliculas p ON r.pelicula_id = p.id 
                        ORDER BY r.fecha_reserva DESC 
                        LIMIT 5");
$stmt->execute();
$ultimas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - CINE-ONLINE 2</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <h1>Panel de Administración</h1>
        
        <div class="dashboard">
            <div class="dashboard-item">
                <h2>Películas</h2>
                <p class="dashboard-number"><?php echo $total_peliculas; ?></p>
                <a href="peliculas.php" class="btn">Gestionar Películas</a>
            </div>
            
            <div class="dashboard-item">
                <h2>Usuarios</h2>
                <p class="dashboard-number"><?php echo $total_usuarios; ?></p>
                <a href="usuarios.php" class="btn">Gestionar Usuarios</a>
            </div>
            
            <div class="dashboard-item">
                <h2>Reservas</h2>
                <p class="dashboard-number"><?php echo $total_reservas; ?></p>
                <a href="reservas.php" class="btn">Gestionar Reservas</a>
            </div>
            
            <div class="dashboard-item">
                <h2>Horarios</h2>
                <a href="horarios.php" class="btn">Gestionar Horarios</a>
            </div>
        </div>
        
        <div class="recent-section">
            <h2>Últimas Reservas</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Película</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimas_reservas as $reserva): ?>
                    <tr>
                        <td><?php echo $reserva['id']; ?></td>
                        <td><?php echo htmlspecialchars($reserva['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($reserva['titulo']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])); ?></td>
                        <td class="estado-<?php echo $reserva['estado']; ?>"><?php echo $reserva['estado']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>