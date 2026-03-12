<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

check_session();
$page_title = 'Registrar Vehículo';

$user_id = $_SESSION['user_id'];

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $placa = $_POST['placa'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $color = $_POST['color'] ?? '';
    $anio = $_POST['anio'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    $result = register_vehicle($user_id, $placa, $marca, $modelo, $color, $anio, $tipo);

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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <h1 class="mb-4"><i class="fas fa-car me-2"></i>Registrar Vehículo</h1>

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

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Formulario de Registro</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="placa" class="form-label">Placa</label>
                    <input type="text" class="form-control" id="placa" name="placa" required placeholder="ABC123">
                </div>
                <div class="mb-3">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" class="form-control" id="marca" name="marca" required placeholder="Toyota">
                </div>
                <div class="mb-3">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input type="text" class="form-control" id="modelo" name="modelo" required placeholder="Corolla">
                </div>
                <div class="mb-3">
                    <label for="color" class="form-label">Color</label>
                    <input type="text" class="form-control" id="color" name="color" required placeholder="Blanco">
                </div>
                <div class="mb-3">
                    <label for="anio" class="form-label">Año</label>
                    <input type="number" class="form-control" id="anio" name="anio" required min="1900" max="<?php echo date('Y'); ?>" placeholder="<?php echo date('Y'); ?>">
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo de Vehículo</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="">Selecciona un tipo...</option>
                        <option value="auto">Auto</option>
                        <option value="moto">Moto</option>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success">Registrar Vehículo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3">
        <a href="profile.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Volver a Mi Perfil</a>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>