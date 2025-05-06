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

// Procesar formulario para agregar o editar horario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $pelicula_id = $_POST['pelicula_id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $sala = $_POST['sala'];
    $precio = $_POST['precio'];
    $capacidad = $_POST['capacidad'];
    $formato = $_POST['formato'];
    
    // Validar datos
    if (empty($pelicula_id) || empty($fecha) || empty($hora) || empty($sala) || empty($precio) || empty($capacidad)) {
        $error = "Por favor complete todos los campos obligatorios.";
    } else {
        try {
            // Combinar fecha y hora para almacenar como datetime
            $fecha_hora = $fecha . ' ' . $hora;
            
            if ($id) {
                // Actualizar horario existente
                $stmt = $conn->prepare("UPDATE horarios SET 
                                        pelicula_id = :pelicula_id, 
                                        fecha_hora = :fecha_hora, 
                                        sala = :sala, 
                                        precio = :precio, 
                                        capacidad = :capacidad, 
                                        formato = :formato 
                                        WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $mensaje = "Horario actualizado con éxito.";
            } else {
                // Insertar nuevo horario
                $stmt = $conn->prepare("INSERT INTO horarios (pelicula_id, fecha_hora, sala, precio, capacidad, formato) 
                                        VALUES (:pelicula_id, :fecha_hora, :sala, :precio, :capacidad, :formato)");
                $mensaje = "Horario agregado con éxito.";
            }
            
            $stmt->bindParam(':pelicula_id', $pelicula_id, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_hora', $fecha_hora);
            $stmt->bindParam(':sala', $sala);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':capacidad', $capacidad, PDO::PARAM_INT);
            $stmt->bindParam(':formato', $formato);
            
            $stmt->execute();
            
            // Redireccionar para evitar reenvío del formulario
            header("Location: horarios.php?pelicula=" . $pelicula_id . "&mensaje=" . urlencode($mensaje));
            exit();
            
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// Eliminar horario
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    try {
        // Primero verificar si hay reservas asociadas
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM reservas WHERE horario_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $reservas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($reservas > 0) {
            $error = "No se puede eliminar el horario porque tiene reservas asociadas.";
        } else {
            // También obtener la película_id para redirigir después
            $stmt = $conn->prepare("SELECT pelicula_id FROM horarios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $pelicula_id = $stmt->fetch(PDO::FETCH_ASSOC)['pelicula_id'];
            
            $stmt = $conn->prepare("DELETE FROM horarios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $mensaje = "Horario eliminado con éxito.";
            
            header("Location: horarios.php?pelicula=" . $pelicula_id . "&mensaje=" . urlencode($mensaje));
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Obtener horario para editar
$horario = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM horarios WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $horario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($horario) {
            // Separar fecha y hora
            $fecha_hora = new DateTime($horario['fecha_hora']);
            $horario['fecha'] = $fecha_hora->format('Y-m-d');
            $horario['hora'] = $fecha_hora->format('H:i');
        } else {
            $error = "Horario no encontrado.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Obtener la película
$pelicula = null;
$pelicula_id = isset($_GET['pelicula']) ? $_GET['pelicula'] : (isset($_POST['pelicula_id']) ? $_POST['pelicula_id'] : null);

if ($pelicula_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM peliculas WHERE id = :id");
        $stmt->bindParam(':id', $pelicula_id, PDO::PARAM_INT);
        $stmt->execute();
        $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pelicula) {
            $error = "Película no encontrada.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Obtener lista de películas para el selector
try {
    $stmt = $conn->prepare("SELECT id, titulo FROM peliculas ORDER BY titulo");
    $stmt->execute();
    $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error en la base de datos: " . $e->getMessage();
    $peliculas = [];
}

// Obtener lista de horarios
try {
    $sql = "SELECT h.*, p.titulo 
            FROM horarios h 
            JOIN peliculas p ON h.pelicula_id = p.id";
    
    // Si hay una película seleccionada, filtrar por ella
    if ($pelicula_id) {
        $sql .= " WHERE h.pelicula_id = :pelicula_id";
        $stmt = $conn->prepare($sql . " ORDER BY h.fecha_hora");
        $stmt->bindParam(':pelicula_id', $pelicula_id, PDO::PARAM_INT);
    } else {
        $stmt = $conn->prepare($sql . " ORDER BY h.fecha_hora");
    }
    
    $stmt->execute();
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error en la base de datos: " . $e->getMessage();
    $horarios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Horarios - CINE-ONLINE 2</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <h1>Gestión de Horarios</h1>
        <a href="index.php" class="btn btn-secondary">Volver al Panel</a>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
        <?php endif; ?>
        
        <?php if ($pelicula): ?>
            <div class="pelicula-info">
                <h2>Horarios para: <?php echo htmlspecialchars($pelicula['titulo']); ?></h2>
                <a href="peliculas.php" class="btn btn-small">Ver todas las películas</a>
            </div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2><?php echo $horario ? 'Editar Horario' : 'Agregar Nuevo Horario'; ?></h2>
            
            <form action="horarios.php" method="post" class="admin-form">
                <?php if ($horario): ?>
                    <input type="hidden" name="id" value="<?php echo $horario['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="pelicula_id">Película*:</label>
                    <select id="pelicula_id" name="pelicula_id" required>
                        <option value="">Seleccione una película</option>
                        <?php foreach ($peliculas as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo (($horario && $horario['pelicula_id'] == $p['id']) || ($pelicula_id == $p['id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['titulo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha">Fecha*:</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo $horario ? $horario['fecha'] : date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="hora">Hora*:</label>
                    <input type="time" id="hora" name="hora" value="<?php echo $horario ? $horario['hora'] : '19:00'; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="sala">Sala*:</label>
                    <input type="text" id="sala" name="sala" value="<?php echo $horario ? htmlspecialchars($horario['sala']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="precio">Precio*:</label>
                    <input type="number" id="precio" name="precio" step="0.01" value="<?php echo $horario ? $horario['precio'] : '8.50'; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="capacidad">Capacidad (asientos)*:</label>
                    <input type="number" id="capacidad" name="capacidad" value="<?php echo $horario ? $horario['capacidad'] : '50'; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="formato">Formato:</label>
                    <select id="formato" name="formato">
                        <option value="2D" <?php echo ($horario && $horario['formato'] == '2D') ? 'selected' : ''; ?>>2D</option>
                        <option value="3D" <?php echo ($horario && $horario['formato'] == '3D') ? 'selected' : ''; ?>>3D</option>
                        <option value="IMAX" <?php echo ($horario && $horario['formato'] == 'IMAX') ? 'selected' : ''; ?>>IMAX</option>
                        <option value="4DX" <?php echo ($horario && $horario['formato'] == '4DX') ? 'selected' : ''; ?>>4DX</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $horario ? 'Actualizar' : 'Agregar'; ?> Horario</button>
                    <?php if ($horario): ?>
                        <a href="horarios.php<?php echo $pelicula_id ? '?pelicula=' . $pelicula_id : ''; ?>" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Listado de Horarios</h2>
            
            <?php if (!$pelicula_id): ?>
                <div class="filtro-container">
                    <form action="horarios.php" method="get" class="filtro-form">
                        <label for="pelicula_filtro">Filtrar por película:</label>
                        <select id="pelicula_filtro" name="pelicula" onchange="this.form.submit()">
                            <option value="">Todas las películas</option>
                            <?php foreach ($peliculas as $p): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo htmlspecialchars($p['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            <?php endif; ?>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Película</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Sala</th>
                        <th>Formato</th>
                        <th>Precio</th>
                        <th>Capacidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($horarios)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay horarios registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($horarios as $h): ?>
                            <tr>
                                <td><?php echo $h['id']; ?></td>
                                <td><?php echo htmlspecialchars($h['titulo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($h['fecha_hora'])); ?></td>
                                <td><?php echo date('H:i', strtotime($h['fecha_hora'])); ?></td>
                                <td><?php echo htmlspecialchars($h['sala']); ?></td>
                                <td><?php echo htmlspecialchars($h['formato']); ?></td>
                                <td><?php echo number_format($h['precio'], 2); ?>€</td>
                                <td><?php echo $h['capacidad']; ?></td>
                                <td>
                                    <a href="horarios.php?editar=<?php echo $h['id']; ?><?php echo $pelicula_id ? '&pelicula=' . $pelicula_id : ''; ?>" class="btn btn-small">Editar</a>
                                    <a href="horarios.php?eliminar=<?php echo $h['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('¿Está seguro de eliminar este horario?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>