<?php
$page_title = "Registro";
require_once '../includes/config.php';
require_once '../includes/db.php';

$error = '';
$success = '';

// Si el usuario ya está logueado, redirigirlo a la página principal
if (isLoggedIn()) {
    header("Location: " . APP_URL);
    exit;
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = clean_input($_POST["nombre"]);
    $email = clean_input($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Por favor completa todos los campos.";
    }
    // Validar que las contraseñas coincidan
    elseif ($password != $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    }
    // Validar que la contraseña tenga al menos 6 caracteres
    elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    }
    // Validar formato de email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor ingresa un correo electrónico válido.";
    }
    else {
        // Verificar si el email ya está registrado
        $check_query = "SELECT id FROM usuarios WHERE email = ?";
        
        if ($stmt = $conn->prepare($check_query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "Este correo electrónico ya está registrado.";
            } else {
                $stmt->close();
                
                // Hash de la contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
                $insert_query = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
                
                if ($stmt = $conn->prepare($insert_query)) {
                    $stmt->bind_param("sss", $nombre, $email, $hashed_password);
                    
                    if ($stmt->execute()) {
                        $success = "¡Registro exitoso! Ahora puedes <a href='" . APP_URL . "../auth/login.php'>iniciar sesión</a>.";
                        // Limpiar los valores del formulario
                        $nombre = $email = '';
                    } else {
                        $error = "Algo salió mal. Por favor intenta de nuevo más tarde.";
                    }
                    
                    $stmt->close();
                } else {
                    $error = "Algo salió mal. Por favor intenta de nuevo más tarde.";
                }
            }
        } else {
            $error = "Algo salió mal. Por favor intenta de nuevo más tarde.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <div class="form-container">
        <h2 class="form-title">Registro de Usuario</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (empty($success)): // Mostrar el formulario solo si no hay mensaje de éxito ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <small class="form-text">La contraseña debe tener al menos 6 caracteres.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Registrarse</button>
                </div>
            </form>
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 20px;">
            ¿Ya tienes una cuenta? <a href="<?php echo APP_URL; ?>/auth/login.php">Iniciar sesión</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>