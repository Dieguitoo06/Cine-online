<?php
$page_title = "Películas en Cartelera";
require_once '../includes/config.php';
require_once '../includes/db.php';

// Obtener todas las películas activas
$query = "SELECT * FROM peliculas WHERE estado = 'activa'";
$result = $conn->query($query);
$peliculas = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $peliculas[] = $row;
    }
}

// Si se proporciona un ID de película, mostrar detalle de esa película y sus funciones
$pelicula_seleccionada = null;
$funciones = [];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $pelicula_id = (int)$_GET['id'];
    
    // Obtener información de la película
    $query = "SELECT * FROM peliculas WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $pelicula_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $pelicula_seleccionada = $result->fetch_assoc();
            
            // Obtener funciones para esta película para hoy y mañana
            $query_funciones = "
                SELECT f.id, f.fecha, f.hora, f.precio, s.nombre as sala 
                FROM funciones f 
                JOIN salas s ON f.sala_id = s.id 
                WHERE f.pelicula_id = ? 
                AND f.fecha >= CURDATE() 
                AND f.fecha <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
                ORDER BY f.fecha, f.hora";
            
            if ($stmt_funciones = $conn->prepare($query_funciones)) {
                $stmt_funciones->bind_param("i", $pelicula_id);
                $stmt_funciones->execute();
                $result_funciones = $stmt_funciones->get_result();
                
                if ($result_funciones->num_rows > 0) {
                    while ($row = $result_funciones->fetch_assoc()) {
                        // Agrupar por fecha
                        $fecha = date("Y-m-d", strtotime($row['fecha']));
                        if (!isset($funciones[$fecha])) {
                            $funciones[$fecha] = [];
                        }
                        $funciones[$fecha][] = $row;
                    }
                }
                
                $stmt_funciones->close();
            }
        }
        
        $stmt->close();
    }
}

require_once '../includes/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <?php if ($pelicula_seleccionada): ?>
        <!-- Detalle de la película y sus funciones -->
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; margin-bottom: 40px;">
            <div>
                <?php if (!empty($pelicula_seleccionada['imagen']) && file_exists('../img/' . $pelicula_seleccionada['imagen'])): ?>
                    <img src="<?php echo APP_URL; ?>/img/<?php echo $pelicula_seleccionada['imagen']; ?>" alt="<?php echo $pelicula_seleccionada['titulo']; ?>" style="width: 100%; border-radius: 8px;">
                <?php else: ?>
                    <img src="<?php echo APP_URL; ?>/img/default-movie.jpg" alt="<?php echo $pelicula_seleccionada['titulo']; ?>" style="width: 100%; border-radius: 8px;">
                <?php endif; ?>
            </div>
            
            <div>
                <h1 style="margin-bottom: 15px;"><?php echo $pelicula_seleccionada['titulo']; ?></h1>
                
                <div style="margin-bottom: 20px; color: #777;">
                    <span style="margin-right: 15px;"><strong>Género:</strong> <?php echo $pelicula_seleccionada['genero']; ?></span>
                    <span style="margin-right: 15px;"><strong>Duración:</strong> <?php echo $pelicula_seleccionada['duracion']; ?> min</span>
                    <span><strong>Clasificación:</strong> <?php echo $pelicula_seleccionada['clasificacion']; ?></span>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <h3 style="margin-bottom: 10px;">Sinopsis</h3>
                    <p><?php echo $pelicula_seleccionada['descripcion']; ?></p>
                </div>
                
                <div>
                    <h3 style="margin-bottom: 20px;">Funciones Disponibles</h3>
                    
                    <?php if (count($funciones) > 0): ?>
                        <?php foreach ($funciones as $fecha => $funciones_fecha): ?>
                            <div style="margin-bottom: 25px;">
                                <h4 style="margin-bottom: 10px;"><?php echo date("d/m/Y", strtotime($fecha)); ?> <?php echo (date("Y-m-d") == $fecha) ? '(Hoy)' : ((date("Y-m-d", strtotime("+1 day")) == $fecha) ? '(Mañana)' : ''); ?></h4>
                                
                                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                    <?php foreach ($funciones_fecha as $funcion): ?>
                                        <a href="<?php echo isLoggedIn() ? APP_URL . '/reservas/seleccion_asientos.php?funcion_id=' . $funcion['id'] : APP_URL . '/auth/login.php'; ?>" class="btn <?php echo isLoggedIn() ? 'btn-primary' : 'btn-secondary'; ?>" style="margin-bottom: 10px;">
                                            <?php echo date("H:i", strtotime($funcion['hora'])); ?> - <?php echo $funcion['sala']; ?> - $<?php echo number_format($funcion['precio'], 2); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (!isLoggedIn()): ?>
                            <div class="error-msg">
                                Debes <a href="<?php echo APP_URL; ?>/auth/login.php">iniciar sesión</a> para poder reservar entradas.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No hay funciones disponibles para esta película en este momento.</p>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 30px;">
                    <a href="<?php echo APP_URL; ?>/reservas" class="btn btn-secondary">Volver a la cartelera</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Listado de películas -->
        <h1 class="section-title">Películas en Cartelera</h1>
        
        <?php if (count($peliculas) > 0): ?>
            <div class="movies-grid">
                <?php foreach ($peliculas as $pelicula): ?>
                    <div class="movie-card">
                        <?php if (!empty($pelicula['imagen']) && file_exists('../img/' . $pelicula['imagen'])): ?>
                            <img src="<?php echo APP_URL; ?>/img/<?php echo $pelicula['imagen']; ?>" alt="<?php echo $pelicula['titulo']; ?>" class="movie-poster">
                        <?php else: ?>
                            <img src="<?php echo APP_URL; ?>/img/default-movie.jpg" alt="<?php echo $pelicula['titulo']; ?>" class="movie-poster">
                        <?php endif; ?>
                        
                        <div class="movie-info">
                            <h3 class="movie-title"><?php echo $pelicula['titulo']; ?></h3>
                            <div class="movie-meta">
                                <span><?php echo $pelicula['genero']; ?></span>
                                <span><?php echo $pelicula['duracion']; ?> min</span>
                            </div>
                            <a href="<?php echo APP_URL; ?>/reservas/index.php?id=<?php echo $pelicula['id']; ?>" class="btn btn-primary">Ver funciones</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No hay películas disponibles en este momento.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>