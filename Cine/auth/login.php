<?php
$page_title = "Iniciar Sesión";
require_once '../includes/config.php';
require_once '../includes/db.php';

$error = '';

// Si el usuario ya está logueado, redirigirlo a la página principal
if (isLoggedIn()) {
    header("Location: " . APP_URL);
    exit;
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = clean_input($_POST["email"]);
    $password = $_POST["password"];
    
    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        $error = "Por favor completa todos los campos.";
    } else {
        // Buscar usuario en la base de datos
        $query = "SELECT id, nombre, email, password, es_admin FROM usuarios WHERE email = ?";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            // Verificar si el usuario existe
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nombre, $email, $hashed_password, $es_admin);
                $stmt->fetch();
                
                // Verificar contraseña
                if (password_verify($password, $hashed_password)) {
                    // Contraseña correcta, iniciar sesión
                    session_start();
                    
                    // Guardar datos en la sesión
                    $_SESSION["user_id"] = $id;
                    $_SESSION["user_name"] = $nombre;
                    $_SESSION["user_email"] = $email;
                    $_SESSION["es_admin"] = $es_admin;
                    
                    // Redirigir según el tipo de usuario
                    if ($es_admin) {
                        header("Location: " . APP_URL . "../admin/index.php");
                    } else {
                        header("Location: " . APP_URL);
                    }
                    exit;
                } else {
                    // Contraseña incorrecta
                    $error = "La contraseña proporcionada no es válida.";
                }
            } else {
                // No existe el usuario
                $error = "No existe una cuenta con este correo electrónico.";
            }
            
            $stmt->close();
        } else {
            $error = "Algo salió mal. Por favor intenta de nuevo más tarde.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <div class="form-container">
        <h2 class="form-title">Iniciar Sesión</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Iniciar Sesión</button>
            </div>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            ¿No tienes una cuenta? <a href="<?php echo APP_URL; ?>/auth/register.php">Regístrate</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>