<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

check_session();
if (!is_admin()) {
    redirect('dashboard.php');
}

$page_title = 'Gestionar Usuarios';

$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id'] ?? null;

// Manejar acciones
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? 'list';

    switch ($action) {
        case 'create':
            $nombre_completo = $_POST['nombre_completo'] ?? '';
            $correo_institucional = $_POST['correo_institucional'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';
            $id_rol = $_POST['id_rol'] ?? 1;
            $matricula = $_POST['matricula'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $estado = $_POST['estado'] ?? 'activo';

            $result = manage_user('create', null, $nombre_completo, $correo_institucional, $contrasena, $id_rol, $matricula, $telefono, $estado);
            if ($result['success']) {
                $success_message = $result['message'];
            } else {
                $error_message = $result['message'];
            }
            break;

        case 'edit':
            $id_usuario = $_POST['id_usuario'] ?? '';
            $nombre_completo = $_POST['nombre_completo'] ?? '';
            $correo_institucional = $_POST['correo_institucional'] ?? '';
            $matricula = $_POST['matricula'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $estado = $_POST['estado'] ?? 'activo';

            $result = manage_user('edit', $id_usuario, $nombre_completo, $correo_institucional, null, null, $matricula, $telefono, $estado);
            if ($result['success']) {
                $success_message = $result['message'];
            } else {
                $error_message = $result['message'];
            }
            break;

        case 'delete':
            $id_usuario = $_POST['id_usuario'] ?? '';

            $result = manage_user('delete', $id_usuario);
            if ($result['success']) {
                $success_message = $result['message'];
            } else {
                $error_message = $result['message'];
            }
            break;
    }
}

// Obtener datos del usuario si es una acción de edición o visualización
$user_data = null;
if ($action == 'edit' || $action == 'view') {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user_data = $user_result->fetch_assoc();
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

    <h1 class="mb-4"><i class="fas fa-users me-2"></i><?php echo $action == 'create' ? 'Crear Usuario' : ($action == 'edit' ? 'Editar Usuario' : ($action == 'view' ? 'Ver Usuario' : 'Gestionar Usuarios')); ?></h1>

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

    <?php if ($action == 'list'): ?>
        <!-- Lista de Usuarios -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Usuarios</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM usuarios ORDER BY fecha_creacion DESC");
                            $stmt->execute();
                            $users_result = $stmt->get_result();
                            while ($user = $users_result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($user['correo_institucional']); ?></td>
                                    <td><?php echo htmlspecialchars($user['id_rol'] == 1 ? 'Usuario' : ($user['id_rol'] == 2 ? 'Administrador' : 'Guardia')); ?></td>
                                    <td><span class="badge <?php echo $user['estado'] == 'activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo htmlspecialchars($user['estado']); ?></span></td>
                                    <td><?php echo htmlspecialchars($user['fecha_creacion']); ?></td>
                                    <td>
                                        <a href="admin_users.php?action=view&id=<?php echo $user['id_usuario']; ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                        <a href="admin_users.php?action=edit&id=<?php echo $user['id_usuario']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                        <a href="admin_users.php?action=delete&id=<?php echo $user['id_usuario']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <a href="admin_users.php?action=create" class="btn btn-outline-primary"><i class="fas fa-plus me-2"></i>Crear Nuevo Usuario</a>
            </div>
        </div>

    <?php elseif ($action == 'create'): ?>
        <!-- Formulario de Creación -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Crear Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label for="nombre_completo" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required placeholder="Juan Pérez">
                    </div>
                    <div class="mb-3">
                        <label for="correo_institucional" class="form-label">Correo Institucional (@ipn.mx)</label>
                        <input type="email" class="form-control" id="correo_institucional" name="correo_institucional" required placeholder="ejemplo@ipn.mx">
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" required placeholder="Tu contraseña">
                    </div>
                    <div class="mb-3">
                        <label for="id_rol" class="form-label">Rol</label>
                        <select class="form-select" id="id_rol" name="id_rol" required>
                            <option value="1">Usuario</option>
                            <option value="2">Administrador</option>
                            <option value="3">Guardia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="matricula" class="form-label">Matrícula</label>
                        <input type="text" class="form-control" id="matricula" name="matricula" placeholder="DOC001">
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" placeholder="5512345678">
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($action == 'edit' || $action == 'view'): ?>
        <!-- Formulario de Edición/Visualización -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i><?php echo $action == 'edit' ? 'Editar' : 'Ver'; ?> Usuario</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $action == 'edit' ? 'edit' : 'view'; ?>">
                    <input type="hidden" name="id_usuario" value="<?php echo $user_data['id_usuario']; ?>">
                    <div class="mb-3">
                        <label for="nombre_completo" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($user_data['nombre_completo']); ?>" <?php echo $action == 'view' ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="mb-3">
                        <label for="correo_institucional" class="form-label">Correo Institucional (@ipn.mx)</label>
                        <input type="email" class="form-control" id="correo_institucional" name="correo_institucional" value="<?php echo htmlspecialchars($user_data['correo_institucional']); ?>" <?php echo $action == 'view' ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="mb-3">
                        <label for="matricula" class="form-label">Matrícula</label>
                        <input type="text" class="form-control" id="matricula" name="matricula" value="<?php echo htmlspecialchars($user_data['matricula'] ?? ''); ?>" <?php echo $action == 'view' ? 'readonly' : ''; ?>>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($user_data['telefono'] ?? ''); ?>" <?php echo $action == 'view' ? 'readonly' : ''; ?>>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" <?php echo $action == 'view' ? 'disabled' : ''; ?> required>
                            <option value="activo" <?php echo $user_data['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo $user_data['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <?php if ($action == 'edit'): ?>
                            <button type="submit" class="btn btn-secondary">Actualizar Usuario</button>
                        <?php else: ?>
                            <a href="admin_users.php?action=edit&id=<?php echo $user_data['id_usuario']; ?>" class="btn btn-secondary">Editar Usuario</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

    <?php endif; ?>

    <div class="mt-3">
        <a href="admin_dashboard.php" class="btn btn-outline-secondary"><i class