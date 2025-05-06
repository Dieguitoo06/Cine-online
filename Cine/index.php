<?php
$page_title = "Inicio";
require_once 'includes/header.php';

// Obtener películas en cartelera
$query = "SELECT * FROM peliculas WHERE estado = 'activa' LIMIT 8";
$result = $conn->query($query);
$peliculas = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $peliculas[] = $row;
    }
}
?>

<div class="hero">
    <div class="hero-content">
        <h1>Bienvenido a CineOnline</h1>
        <p>Reserva tus entradas y disfruta de las mejores películas en cartelera</p>
        <a href="<?php echo APP_URL; ?>../reservas" class="btn btn-primary">Ver películas</a>
    </div>
</div>

<section class="section">
    <div class="container">
        <h2 class="section-title">Películas en Cartelera</h2>
        
        <?php if (count($peliculas) > 0): ?>
            <div class="movies-grid">
                <?php foreach ($peliculas as $pelicula): ?>
                    <div class="movie-card">
                        <?php if (!empty($pelicula['imagen']) && file_exists('img/' . $pelicula['imagen'])): ?>
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
    </div>
</section>

<section class="section" style="background-color: #f9f9f9;">
    <div class="container">
        <h2 class="section-title">¿Por qué elegirnos?</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; text-align: center;">
            <div>
                <h3 style="margin-bottom: 15px;">Reserva fácil</h3>
                <p>Reserva tus entradas en línea en pocos minutos y asegura los mejores asientos.</p>
            </div>
            <div>
                <h3 style="margin-bottom: 15px;">Las mejores películas</h3>
                <p>Disfruta de los últimos estrenos y las películas más populares del momento.</p>
            </div>
            <div>
                <h3 style="margin-bottom: 15px;">Precios accesibles</h3>
                <p>Ofrecemos los mejores precios y promociones para que puedas disfrutar más por menos.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>