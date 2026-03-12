<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'Iniciar Sesión';

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo_institucional = $_POST['correo_institucional'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    $result = login_user($correo_institucional, $contrasena);

    if ($result['success']) {
        // Redirigir al panel correspondiente
        if (is_admin()) {
            redirect('admin_dashboard.php');
        } elseif (is_guard()) {
            redirect('access_control.php');
        } else {
            redirect('dashboard.php');
        }
    } else {
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #333;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2><i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión</h2>
            <p>Accede a tu cuenta del CECyT 9</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="correo_institucional" class="form-label">Correo Institucional (@ipn.mx)</label>
                <input type="email" class="form-control" id="correo_institucional" name="correo_institucional" required placeholder="ejemplo@ipn.mx">
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required placeholder="Tu contraseña">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-login">Iniciar Sesión</button>
            </div>
        </form>

        <div class="forgot-password">
            <a href="recover_password.php">¿Olvidaste tu contraseña?</a>
        </div>

        <div class="register-link">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>