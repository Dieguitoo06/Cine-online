<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirigido=true');
    exit;
}

// Incluir configuración y conexión a la base de datos
require_once '../includes/config.php';
require_once '../includes/db.php';

// Obtener ID de la función
if (!isset($_GET['funcion_id'])) {
    header('Location: ../peliculas.php');
    exit;
}

$funcion_id = $_GET['funcion_id'];

// Obtener información de la función
$query = "SELECT f.*, p.titulo, p.imagen, p.duracion, s.nombre as sala_nombre, s.capacidad 
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

// Obtener asientos ya reservados
$query = "SELECT a.fila, a.numero 
          FROM asientos a 
          JOIN detalle_reservas dr ON a.id = dr.asiento_id 
          JOIN reservas r ON dr.reserva_id = r.id 
          WHERE r.funcion_id = ? AND r.estado != 'cancelada'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $funcion_id);
$stmt->execute();
$resultado = $stmt->get_result();

$asientos_reservados = [];
while ($fila = $resultado->fetch_assoc()) {
    // Formato: "A1", "B5", etc.
    $asientos_reservados[] = $fila['fila'] . $fila['numero'];
}

// Convertir a formato JSON para usar en JavaScript
$asientos_reservados_json = json_encode($asientos_reservados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Asientos - <?php echo $funcion['titulo']; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="contenedor">
        <h1>Seleccionar Asientos</h1>
        
        <div class="info-pelicula">
            <img src="../img/<?php echo $funcion['imagen']; ?>" alt="<?php echo $funcion['titulo']; ?>" width="150">
            <div>
                <h2><?php echo $funcion['titulo']; ?></h2>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($funcion['fecha'])); ?></p>
                <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($funcion['hora'])); ?></p>
                <p><strong>Sala:</strong> <?php echo $funcion['sala_nombre']; ?></p>
                <p><strong>Precio:</strong> $<?php echo number_format($funcion['precio'], 2); ?></p>
            </div>
        </div>
        
        <div class="contenedor-sala">
            <div class="pantalla">PANTALLA</div>
            
            <div class="filas-asientos" id="sala-asientos">
                <!-- Los asientos se generarán dinámicamente con JavaScript -->
            </div>
            
            <div class="leyenda">
                <div class="leyenda-item">
                    <div class="leyenda-color leyenda-disponible"></div>
                    <span>Disponible</span>
                </div>
                <div class="leyenda-item">
                    <div class="leyenda-color leyenda-seleccionado"></div>
                    <span>Seleccionado</span>
                </div>
                <div class="leyenda-item">
                    <div class="leyenda-color leyenda-reservado"></div>
                    <span>Reservado</span>
                </div>
            </div>
            
            <div class="info-resumen">
                <h3>Resumen de tu reserva</h3>
                <p><strong>Asientos seleccionados:</strong> <span id="asientos-seleccionados">Ninguno</span></p>
                <p><strong>Total a pagar:</strong> $<span id="total-pagar">0.00</span></p>
                
                <form action="confirmar.php" method="post" id="form-reserva">
                    <input type="hidden" name="funcion_id" value="<?php echo $funcion_id; ?>">
                    <input type="hidden" name="asientos" id="input-asientos" value="">
                    <input type="hidden" name="total" id="input-total" value="">
                    <button type="submit" class="btn-confirmar" id="btn-confirmar" disabled>Confirmar Reserva</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Datos de la función y asientos reservados
        const funcionId = <?php echo $funcion_id; ?>;
        const capacidadSala = <?php echo $funcion['capacidad']; ?>;
        const precioAsiento = <?php echo $funcion['precio']; ?>;
        const asientosReservados = <?php echo $asientos_reservados_json; ?>;
        
        // Pasamos los datos al archivo JavaScript externo
        document.addEventListener('DOMContentLoaded', function() {
            inicializarSeleccionAsientos(capacidadSala, asientosReservados, precioAsiento);
        });
    </script>
    <script src="../js/reservas.js"></script>
</body>
</html>