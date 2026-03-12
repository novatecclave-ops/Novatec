<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

check_session();
if (!is_admin()) {
    redirect('dashboard.php');
}

$page_title = 'Gestión Semestral';

// Manejar la limpieza de registros
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clean_semester'])) {
    $result = clean_semester_records();

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

    <h1 class="mb-4"><i class="fas fa-eraser me-2"></i>Gestión Semestral</h1>

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
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-eraser me-2"></i>Limpiar Registros Semestrales</h5>
        </div>
        <div class="card-body">
            <p>Esta acción eliminará todos los registros de vehículos, reservas y QR de acceso de los usuarios, y reiniciará la disponibilidad de los espacios de estacionamiento. Solo debe ejecutarse al inicio de cada semestre académico.</p>
            <p><strong>Advertencia:</strong> Esta acción no se puede deshacer.</p>
            <form method="POST" action="">
                <input type="hidden" name="clean_semester" value="1">
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas limpiar todos los registros semestrales? Esta acción no se puede deshacer.')">Limpiar Registros Semestrales</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3">
        <a href="admin_dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Volver al Panel de Administrador</a>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>