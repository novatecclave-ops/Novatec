<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

check_session();
if (!is_admin() && !is_guard()) {
    redirect('dashboard.php');
}

$page_title = 'Registrar Infracción';

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $placa = $_POST['placa'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    $result = register_violation($placa, $tipo, $descripcion);

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

    <h1 class="mb-4"><i class="fas fa-exclamation-triangle me-2"></i>Registrar Infracción</h1>

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
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Formulario de Registro</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="placa" class="form-label">Placa del Vehículo</label>
                    <input type="text" class="form-control" id="placa" name="placa" required placeholder="ABC123">
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo de Infracción</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="">Selecciona un tipo...</option>
                        <option value="estacionamiento incorrecto">Estacionamiento Incorrecto</option>
                        <option value="exceso de tiempo">Exceso de Tiempo</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required placeholder="Describe la infracción..."></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-warning">Registrar Infracción</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3">
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Volver al Panel</a>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>