<footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>CineOnline</h3>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>">Inicio</a></li>
                        <li><a href="<?php echo APP_URL; ?>../reservas">Películas</a></li>
                        <li><a href="#">Sobre Nosotros</a></li>
                        <li><a href="#">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Ayuda</h3>
                    <ul>
                        <li><a href="#">Preguntas Frecuentes</a></li>
                        <li><a href="#">Términos y Condiciones</a></li>
                        <li><a href="#">Política de Privacidad</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Síguenos</h3>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Instagram</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    <script src="<?php echo APP_URL; ?>../js/main.js"></script>
    <?php if(isset($extra_scripts)): ?>
        <?php foreach($extra_scripts as $script): ?>
            <script src="<?php echo APP_URL . $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html> 