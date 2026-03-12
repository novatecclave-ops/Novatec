<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'Recuperar Contraseña';

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo_institucional = $_POST['correo_institucional'] ?? '';

    $result = recover_password($correo_institucional);

    if ($result['success']) {
        $success_message = $result['message'];
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
        .recover-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .recover-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .recover-header h2 {
            color: #333;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
        }
        .btn-recover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-recover:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="recover-container">
        <div class="recover-header">
            <h2><i class="fas fa-key me-2"></i>Recuperar Contraseña</h2>
            <p>Ingresa tu correo institucional para recibir un enlace de recuperación</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="correo_institucional" class="form-label">Correo Institucional (@ipn.mx)</label>
                <input type="email" class="form-control" id="correo_institucional" name="correo_institucional" required placeholder="ejemplo@ipn.mx">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-recover">Enviar Enlace de Recuperación</button>
            </div>
        </form>

        <div class="login-link">
            <p><a href="login.php">Volver a Iniciar Sesión</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>