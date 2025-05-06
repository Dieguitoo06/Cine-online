<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?redirigido=true');
    exit;
}

// Incluir configuración y conexión a la base de datos
require_once '../includes/config.php';
require_once '../includes/db.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener información del usuario
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Obtener las reservas del usuario
$query = "SELECT r.*, f.fecha, f.hora, p.titulo, p.imagen, s.nombre as sala_nombre
          FROM reservas r
          JOIN funciones f ON r.funcion_id = f.id
          JOIN peliculas p ON f.pelicula_id = p.id
          JOIN salas s ON f.sala_id = s.id
          WHERE r.usuario_id = ?
          ORDER BY r.fecha_reserva DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

// Mensaje de acción completada (si existe)
$mensaje = '';
if (isset($_GET['accion'])) {
    switch ($_GET['accion']) {
        case 'actualizado':
            $mensaje = "Tu perfil ha sido actualizado correctamente.";
            break;
        case 'password':
            $mensaje = "Tu contraseña ha sido actualizada correctamente.";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - CINE-ONLINE</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .perfil-contenedor {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .perfil-sidebar {
            flex: 0 0 300px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .perfil-contenido {
            flex: 1;
            min-width: 300px;
        }
        
        .perfil-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .perfil-menu li {
            margin-bottom: 10px;
        }
        
        .perfil-menu a {
            display: block;
            padding: 10px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .perfil-menu a:hover {
            background-color: #2980b9;
        }
        
        .info-usuario {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .reserva {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .reserva-imagen {
            flex: 0 0 120px;
            margin-right: 20px;
        }
        
        .reserva-detalles {
            flex: 1;
        }
        
        .reserva-codigo {
            background-color: #f1c40f;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .reserva-estado {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            color: white;
            font-size: 0.9em;
            margin-left: 10px;
        }
        
        .estado-confirmada {
            background-color: #27ae60;
        }
        
        .estado-cancelada {
            background-color: #e74c3c;
        }
        
        .mensaje-exito {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .sin-reservas {
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="contenedor">
        <h1>Mi Perfil</h1>
        
        <?php if (!empty($mensaje)): ?>
        <div class="mensaje-exito">
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>
        
        <div class="perfil-contenedor">
            <div class="perfil-sidebar">
                <div class="info-usuario">
                    <h3>Hola, <?php echo htmlspecialchars($usuario['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                    <p><small>Miembro desde: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></small></p>
                </div>
                
                <ul class="perfil-menu">
                    <li><a href="index.php" class="active">Mis Reservas</a></li>
                    <li><a href="editar-perfil.php">Editar Perfil</a></li>
                    <li><a href="cambiar-password.php">Cambiar Contraseña</a></li>
                    <li><a href="../auth/logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
            
            <div class="perfil-contenido">
                <h2>Mis Reservas</h2>
                
                <?php if ($resultado->num_rows > 0): ?>
                    <?php while ($reserva = $resultado->fetch_assoc()): ?>
                        <?php
                        // Obtener los asientos de la reserva
                        $query = "SELECT asiento FROM detalle_reservas WHERE reserva_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $reserva['id']);
                        $stmt->execute();
                        $asientos_result = $stmt->get_result();
                        
                        $asientos = [];
                        while ($asiento = $asientos_result->fetch_assoc()) {
                            $asientos[] = $asiento['asiento'];
                        }
                        ?>
                        <div class="reserva">
                            <div class="reserva-imagen">
                                <img src="../img/<?php echo $reserva['imagen']; ?>" alt="<?php echo $reserva['titulo']; ?>" width="100">
                            </div>
                            <div class="reserva-detalles">
                                <h3><?php echo $reserva['titulo']; ?></h3>
                                <div>
                                    <span class="reserva-codigo"><?php echo $reserva['codigo']; ?></span>
                                    <span class="reserva-estado estado-<?php echo $reserva['estado']; ?>">
                                        <?php echo ucfirst($reserva['estado']); ?>
                                    </span>
                                </div>
                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($reserva['fecha'])); ?></p>
                                <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($reserva['hora'])); ?></p>
                                <p><strong>Sala:</strong> <?php echo $reserva['sala_nombre']; ?></p>
                                <p><strong>Asientos:</strong> <?php echo implode(', ', $asientos); ?></p>
                                <p><strong>Total pagado:</strong> $<?php echo number_format($reserva['total'], 2); ?></p>
                                <p><small>Reserva realizada el: <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])); ?></small></p>
                                
                                <?php if ($reserva['estado'] === 'confirmada' && strtotime($reserva['fecha'] . ' ' . $reserva['hora']) > time()): ?>
                                <a href="cancelar-reserva.php?id=<?php echo $reserva['id']; ?>" 
                                   onclick="return confirm('¿Estás seguro de que deseas cancelar esta reserva?');"
                                   class="btn-cancelar">Cancelar Reserva</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="sin-reservas">
                        <h3>No tienes reservas aún</h3>
                        <p>Explora nuestras películas y reserva tus entradas ahora.</p>
                        <a href="../peliculas.php" class="btn-accion">Ver Películas</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>