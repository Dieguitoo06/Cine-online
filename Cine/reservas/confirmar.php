<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?redirigido=true');
    exit;
}

// Verificar si se recibieron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['funcion_id']) || !isset($_POST['asientos'])) {
    header('Location: ../peliculas.php');
    exit;
}

// Incluir configuración y conexión a la base de datos
require_once '../includes/config.php';
require_once '../includes/db.php';

$usuario_id = $_SESSION['usuario_id'];
$funcion_id = $_POST['funcion_id'];
$asientos = explode(',', $_POST['asientos']);
$total = $_POST['total'];

// Verificar que la función exista
$query = "SELECT f.*, p.titulo, p.imagen, s.nombre as sala_nombre 
          FROM funciones f 
          JOIN peliculas p ON f.pelicula_id = p.id 
          JOIN salas s ON f.sala_id = s.id 
          WHERE f.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $funcion_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header('Location: ../peliculas.php');
    exit;
}

$funcion = $resultado->fetch_assoc();

// Verificar que los asientos no estén ya reservados (doble verificación)
$asientos_reservados = [];
foreach ($asientos as $asiento) {
    $query = "SELECT id FROM reservas WHERE funcion_id = ? AND asiento = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $funcion_id, $asiento);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $asientos_reservados[] = $asiento;
    }
}

// Si hay asientos ya reservados, redirigir con error
if (count($asientos_reservados) > 0) {
    header('Location: seleccion_asientos.php?funcion_id=' . $funcion_id . '&error=asientos_reservados');
    exit;
}

// Generar código de reserva único
$codigo_reserva = 'R' . time() . rand(1000, 9999);

// Procesar la reserva si no hay errores
$reserva_exitosa = true;
$conn->begin_transaction();

try {
    // Insertar la reserva principal
    $query = "INSERT INTO reservas (usuario_id, funcion_id, codigo, fecha_reserva, estado, total) 
              VALUES (?, ?, ?, NOW(), 'confirmada', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issd", $usuario_id, $funcion_id, $codigo_reserva, $total);
    $stmt->execute();
    $reserva_id = $conn->insert_id;
    
    // Insertar cada asiento reservado
    foreach ($asientos as $asiento) {
        $query = "INSERT INTO detalle_reservas (reserva_id, asiento) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $reserva_id, $asiento);
        $stmt->execute();
    }
    
    // Confirmar la transacción
    $conn->commit();
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollback();
    $reserva_exitosa = false;
}

// Verificar si la transacción fue exitosa
if (!$reserva_exitosa) {
    header('Location: seleccion_asientos.php?funcion_id=' . $funcion_id . '&error=proceso');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Confirmada - CINE-ONLINE</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .confirmacion {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .confirmacion h2 {
            color: #27ae60;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .detalles-reserva {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .detalles-reserva .imagen {
            flex: 0 0 200px;
            margin-right: 20px;
        }
        
        .detalles-reserva .info {
            flex: 1;
        }
        
        .codigo-reserva {
            background-color: #f1c40f;
            padding: 15px;
            border-radius: 5px;
            font-size: 18px;
            text-align: center;
            margin: 20px 0;
        }
        
        .btn-accion {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        
        .btn-accion:hover {
            background-color: #2980b9;
        }
        
        .btn-imprimir {
            background-color: #9b59b6;
        }
        
        .btn-imprimir:hover {
            background-color: #8e44ad;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="contenedor">
        <div class="confirmacion">
            <h2>¡Reserva Confirmada!</h2>
            
            <div class="codigo-reserva">
                <strong>Código de Reserva:</strong> <?php echo $codigo_reserva; ?>
            </div>
            
            <div class="detalles-reserva">
                <div class="imagen">
                    <img src="../img/<?php echo $funcion['imagen']; ?>" alt="<?php echo $funcion['titulo']; ?>" width="180">
                </div>
                <div class="info">
                    <h3><?php echo $funcion['titulo']; ?></h3>
                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($funcion['fecha'])); ?></p>
                    <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($funcion['hora'])); ?></p>
                    <p><strong>Sala:</strong> <?php echo $funcion['sala_nombre']; ?></p>
                    <p><strong>Asientos:</strong> <?php echo implode(', ', $asientos); ?></p>
                    <p><strong>Total pagado:</strong> $<?php echo number_format($total, 2); ?></p>
                </div>
            </div>
            
            <p>Guarda este código de reserva. Deberás presentarlo en la taquilla del cine para recoger tus entradas.</p>
            
            <div class="acciones">
                <a href="javascript:window.print()" class="btn-accion btn-imprimir">Imprimir Reserva</a>
                <a href="../perfil/index.php" class="btn-accion">Ver Mis Reservas</a>
                <a href="../peliculas.php" class="btn-accion">Explorar Películas</a>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>