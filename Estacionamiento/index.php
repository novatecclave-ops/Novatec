<?php
session_start();
// Si el usuario ya está logueado, redirigir al panel correspondiente
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'Administrador') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['user_role'] === 'Guardia') {
        header("Location: access_control.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estacionamiento - CECyT 9</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: white;
        }
        .hero-section {
            text-align: center;
            max-width: 800px;
        }
        .hero-section h1 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .btn-action {
            background: white;
            color: #667eea;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 10px;
        }
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        .logo {
            font-size: 3.5rem;
            margin-bottom: 20px;
        }
        .features {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 40px;
        }
        .feature-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            width: 200px;
        }
        .feature-card i {
            font-size: 2rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="logo">
            <i class="fas fa-parking"></i>
        </div>
        <h1>Gestión de Estacionamiento CECyT 9</h1>
        <p>Sistema automatizado para la administración eficiente del estacionamiento institucional del CECyT 9 "Juan de Dios Bátiz".</p>
        
        <div>
            <a href="login.php" class="btn-action"><i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión</a>
            <a href="register.php" class="btn-action"><i class="fas fa-user-plus me-2"></i>Registrarse</a>
        </div>

        <div class="features">
            <div class="feature-card">
                <i class="fas fa-qrcode"></i>
                <p>Acceso con QR</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-car"></i>
                <p>Hasta 3 vehículos</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-calendar-check"></i>
                <p>Reserva automática</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>