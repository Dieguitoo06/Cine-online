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

// Procesar formulario para agregar o editar película
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $titulo = $_POST['titulo'];
    $sinopsis = $_POST['sinopsis'];
    $duracion = $_POST['duracion'];
    $genero = $_POST['genero'];
    $director = $_POST['director'];
    $fecha_estreno = $_POST['fecha_estreno'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validar datos
    if (empty($titulo) || empty($sinopsis) || empty($duracion) || empty($genero)) {
        $error = "Por favor complete todos los campos obligatorios.";
    } else {
        // Procesar la imagen si se ha subido una nueva
        $imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagen_temporal = $_FILES['imagen']['tmp_name'];
            $imagen_nombre = time() . '_' . $_FILES['imagen']['name'];
            $ruta_destino = '../img/peliculas/' . $imagen_nombre;
            
            // Verificar que es una imagen válida
            $check = getimagesize($imagen_temporal);
            if ($check !== false) {
                // Mover la imagen al directorio de destino
                if (move_uploaded_file($imagen_temporal, $ruta_destino)) {
                    $imagen = $imagen_nombre;
                } else {
                    $error = "Error al subir la imagen.";
                }
            } else {
                $error = "El archivo no es una imagen válida.";
            }
        }
        
        if (!isset($error)) {
            try {
                if ($id) {
                    // Actualizar película existente
                    $sql = "UPDATE peliculas SET 
                            titulo = :titulo, 
                            sinopsis = :sinopsis, 
                            duracion = :duracion, 
                            genero = :genero, 
                            director = :director, 
                            fecha_estreno = :fecha_estreno, 
                            activo = :activo";
                    
                    // Solo actualizar la imagen si se ha subido una nueva
                    if ($imagen) {
                        $sql .= ", imagen = :imagen";
                    }
                    
                    $sql .= " WHERE id = :id";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    
                    if ($imagen) {
                        $stmt->bindParam(':imagen', $imagen);
                    }
                    
                    $mensaje = "Película actualizada con éxito.";
                } else {
                    // Insertar nueva película
                    $sql = "INSERT INTO peliculas (titulo, sinopsis, duracion, genero, director, fecha_estreno, imagen, activo) 
                            VALUES (:titulo, :sinopsis, :duracion, :genero, :director, :fecha_estreno, :imagen, :activo)";
                    
                    $stmt = $conn->prepare($sql);
                    
                    // Si no se subió imagen, usar una por defecto
                    if (!$imagen) {
                        $imagen = 'default.jpg';
                    }
                    
                    $stmt->bindParam(':imagen', $imagen);
                    $mensaje = "Película agregada con éxito.";
                }
                
                // Parámetros comunes para ambas operaciones
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':sinopsis', $sinopsis);
                $stmt->bindParam(':duracion', $duracion);
                $stmt->bindParam(':genero', $genero);
                $stmt->bindParam(':director', $director);
                $stmt->bindParam(':fecha_estreno', $fecha_estreno);
                $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
                
                $stmt->execute();
                
                // Redireccionar para evitar reenvío del formulario
                header("Location: peliculas.php?mensaje=" . urlencode($mensaje));
                exit();
                
            } catch (PDOException $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
}

// Eliminar película
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    try {
        // Primero verificar si hay reservas asociadas
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM reservas WHERE pelicula_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $reservas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($reservas > 0) {
            $error = "No se puede eliminar la película porque tiene reservas asociadas.";
        } else {
            $stmt = $conn->prepare("DELETE FROM peliculas WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $mensaje = "Película eliminada con éxito.";
            
            header("Location: peliculas.php?mensaje=" . urlencode($mensaje));
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Obtener película para editar
$pelicula = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM peliculas WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pelicula) {
            $error = "Película no encontrada.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Obtener lista de películas
try {
    $stmt = $conn->prepare("SELECT * FROM peliculas ORDER BY fecha_estreno DESC");
    $stmt->execute();
    $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error en la base de datos: " . $e->getMessage();
    $peliculas = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Películas - CINE-ONLINE 2</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <h1>Gestión de Películas</h1>
        <a href="index.php" class="btn btn-secondary">Volver al Panel</a>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2><?php echo $pelicula ? 'Editar Película' : 'Agregar Nueva Película'; ?></h2>
            
            <form action="peliculas.php" method="post" enctype="multipart/form-data" class="admin-form">
                <?php if ($pelicula): ?>
                    <input type="hidden" name="id" value="<?php echo $pelicula['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="titulo">Título*:</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo $pelicula ? htmlspecialchars($pelicula['titulo']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="sinopsis">Sinopsis*:</label>
                    <textarea id="sinopsis" name="sinopsis" rows="4" required><?php echo $pelicula ? htmlspecialchars($pelicula['sinopsis']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="duracion">Duración (minutos)*:</label>
                    <input type="number" id="duracion" name="duracion" value="<?php echo $pelicula ? $pelicula['duracion'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="genero">Género*:</label>
                    <input type="text" id="genero" name="genero" value="<?php echo $pelicula ? htmlspecialchars($pelicula['genero']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="director">Director:</label>
                    <input type="text" id="director" name="director" value="<?php echo $pelicula ? htmlspecialchars($pelicula['director']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="fecha_estreno">Fecha de Estreno:</label>
                    <input type="date" id="fecha_estreno" name="fecha_estreno" value="<?php echo $pelicula ? $pelicula['fecha_estreno'] : date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="imagen">Imagen:</label>
                    <?php if ($pelicula && $pelicula['imagen']): ?>
                        <div class="imagen-actual">
                            <img src="../img/peliculas/<?php echo htmlspecialchars($pelicula['imagen']); ?>" alt="<?php echo htmlspecialchars($pelicula['titulo']); ?>" width="100">
                            <p>Imagen actual</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <small>Deje en blanco para mantener la imagen actual.</small>
                </div>
                
                <div class="form-group">
                    <label for="activo">
                        <input type="checkbox" id="activo" name="activo" <?php echo ($pelicula && $pelicula['activo']) ? 'checked' : ''; ?>>
                        Película Activa
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $pelicula ? 'Actualizar' : 'Agregar'; ?> Película</button>
                    <?php if ($pelicula): ?>
                        <a href="peliculas.php" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Listado de Películas</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Título</th>
                        <th>Género</th>
                        <th>Duración</th>
                        <th>Estreno</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($peliculas)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay películas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($peliculas as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td>
                                    <?php if ($p['imagen']): ?>
                                        <img src="../img/peliculas/<?php echo htmlspecialchars($p['imagen']); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>" width="50">
                                    <?php else: ?>
                                        <img src="../img/peliculas/default.jpg" alt="Sin imagen" width="50">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($p['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($p['genero']); ?></td>
                                <td><?php echo $p['duracion']; ?> min</td>
                                <td><?php echo date('d/m/Y', strtotime($p['fecha_estreno'])); ?></td>
                                <td>
                                    <span class="estado-<?php echo $p['activo'] ? 'activo' : 'inactivo'; ?>">
                                        <?php echo $p['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="peliculas.php?editar=<?php echo $p['id']; ?>" class="btn btn-small">Editar</a>
                                    <a href="peliculas.php?eliminar=<?php echo $p['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('¿Está seguro de eliminar esta película?')">Eliminar</a>
                                    <a href="horarios.php?pelicula=<?php echo $p['id']; ?>" class="btn btn-small btn-secondary">Horarios</a>
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