<?php
session_start();

// Incluir configuración y funciones
require_once 'config.php';
require_once 'includes/functions.php';

// Verificar sesión y rol de administrador
check_session();
if(!is_admin()){
    if(is_guard()){
        redirect('access_control.php');
    } else{
        redirect('dashboard.php');
    }
}

$page_title = 'Panel de Administrador';
$modulo = $_GET['modulo'] ?? 'inicio';

// Verificar que la conexión existe
if(!isset($conn) || $conn->connect_error){
    die("Error de conexión a la base de datos");
}

// Obtener estadísticas generales
$stmt_users = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
$stmt_users->execute();
$users_result = $stmt_users->get_result();
$users_count = $users_result->fetch_assoc()['total'];

$stmt_vehicles = $conn->prepare("SELECT COUNT(*) as total FROM vehiculos");
$stmt_vehicles->execute();
$vehicles_result = $stmt_vehicles->get_result();
$vehicles_count = $vehicles_result->fetch_assoc()['total'];

$stmt_spaces = $conn->prepare("SELECT COUNT(*) as total FROM espacios WHERE estado='activo'");
$stmt_spaces->execute();
$spaces_result = $stmt_spaces->get_result();
$spaces_count = $spaces_result->fetch_assoc()['total'];

$stmt_available_spaces=$conn->prepare("SELECT COUNT(*) as total FROM espacios e WHERE e.estado='activo' AND NOT EXISTS (SELECT 1 FROM reservas r WHERE r.id_espacio = e.id_espacio AND r.estado='confirmada' AND r.fecha >= CURDATE())");

$stmt_available_spaces->execute();
$available_spaces_result = $stmt_available_spaces->get_result();
$available_spaces_count = $available_spaces_result->fetch_assoc()['total'];

$stmt_reservas = $conn->prepare("SELECT COUNT(*) as total FROM reservas WHERE estado='confirmada'");
$stmt_reservas->execute();
$reservas_result = $stmt_reservas->get_result();
$reservas_count = $reservas_result->fetch_assoc()['total'];

$stmt_infracciones = $conn->prepare("SELECT COUNT(*) as total FROM infracciones");
$stmt_infracciones->execute();
$infracciones_result = $stmt_infracciones->get_result();
$infracciones_count = $infracciones_result->fetch_assoc()['total'];
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
    <!-- Para el calendario (usaremos FullCalendar) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Panel de Administrador</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link <?php echo $modulo === 'inicio' ? 'active' : ''; ?>" href="?modulo=inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $modulo === 'vehiculos' ? 'active' : ''; ?>" href="?modulo=vehiculos">Vehículos</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $modulo === 'registros' ? 'active' : ''; ?>" href="?modulo=registros">Control de Registros</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $modulo === 'reservas' ? 'active' : ''; ?>" href="?modulo=reservas">Reservas de Espacio</a></li>
                </ul>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 py-4">

            <h1 class="mb-4"><i class="fas fa-shield-alt me-2"></i>Panel de Administrador</h1>

            <?php if ($modulo === 'inicio'): ?>
                <!-- Tarjetas de Resumen -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-users me-2"></i>Usuarios Totales</h5>
                                <p class="card-text"><?php echo $users_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-car me-2"></i>Vehículos Registrados</h5>
                                <p class="card-text"><?php echo $vehicles_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-parking me-2"></i>Espacios Totales</h5>
                                <p class="card-text"><?php echo $spaces_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-door-open me-2"></i>Espacios Disponibles</h5>
                                <p class="card-text"><?php echo $available_spaces_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-calendar-check me-2"></i>Reservas Activas</h5>
                                <p class="card-text"><?php echo $reservas_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Infracciones</h5>
                                <p class="card-text"><?php echo $infracciones_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Entradas/Salidas Recientes -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Entradas y Salidas Recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Vehículo (Placa)</th>
                                        <th>Tipo Movimiento</th>
                                        <th>Fecha/Hora</th>
                                        <th>Espacio</th>
                                        <th>Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Consulta para obtener los últimos 10 movimientos
                                    $stmt_historial = $conn->prepare("
                                        SELECT h.*, u.nombre_completo, v.placa, e.codigo_espacio
                                        FROM historial_accesos h
                                        INNER JOIN usuarios u ON h.id_usuario = u.id_usuario
                                        INNER JOIN vehiculos v ON h.id_vehiculo = v.id_vehiculo
                                        LEFT JOIN espacios e ON h.id_espacio = e.id_espacio
                                        ORDER BY h.fecha_hora DESC
                                        LIMIT 10
                                    ");
                                    $stmt_historial->execute();
                                    $historial_result = $stmt_historial->get_result();
                                    while ($registro = $historial_result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($registro['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($registro['placa']); ?></td>
                                        <td>
                                            <?php if ($registro['tipo_movimiento'] == 'entrada'): ?>
                                                <span class="badge bg-primary">Entrada</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Salida</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($registro['fecha_hora']); ?></td>
                                        <td><?php echo htmlspecialchars($registro['codigo_espacio'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if ($registro['resultado'] == 'exitoso'): ?>
                                                <span class="badge bg-success">Exitoso</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Fallido</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="?modulo=registros" class="btn btn-outline-primary mt-3">Ver Todos los Registros</a>
                    </div>
                </div>

            <?php elseif ($modulo === 'vehiculos'): ?>
                <!-- Módulo: Vehículos Registrados -->
                <h2><i class="fas fa-car me-2"></i>Vehículos Registrados</h2>
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Lista de Vehículos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Propietario</th>
                                        <th>Placa</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Año</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt_vehiculos = $conn->prepare("
                                        SELECT v.*, u.nombre_completo AS propietario
                                        FROM vehiculos v
                                        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                                        ORDER BY v.fecha_registro DESC
                                    ");
                                    $stmt_vehiculos->execute();
                                    $vehiculos_result = $stmt_vehiculos->get_result();
                                    while ($vehiculo = $vehiculos_result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $vehiculo['id_vehiculo']; ?></td>
                                        <td><?php echo htmlspecialchars($vehiculo['propietario']); ?></td>
                                        <td><?php echo htmlspecialchars($vehiculo['placa']); ?></td>
                                        <td><?php echo htmlspecialchars($vehiculo['marca']); ?></td>
                                        <td><?php echo htmlspecialchars($vehiculo['modelo']); ?></td>
                                        <td><?php echo htmlspecialchars($vehiculo['anio']); ?></td>
                                        <td><?php echo htmlspecialchars($vehiculo['tipo']); ?></td>
                                        <td><span class="badge <?php echo $vehiculo['estado_registro'] == 'activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo htmlspecialchars($vehiculo['estado_registro']); ?></span></td>
                                        <td><?php echo htmlspecialchars($vehiculo['fecha_registro']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($modulo === 'registros'): ?>
                <!-- Módulo: Control de Registros -->
                <h2><i class="fas fa-book me-2"></i>Control de Registros de Usuarios</h2>
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Registros de Acceso</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Fecha Creación</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt_usuarios = $conn->prepare("
                                        SELECT u.*, r.nombre_rol
                                        FROM usuarios u
                                        INNER JOIN roles r ON u.id_rol = r.id_rol
                                        ORDER BY u.fecha_creacion DESC
                                    ");
                                    $stmt_usuarios->execute();
                                    $usuarios_result = $stmt_usuarios->get_result();
                                    while ($usuario = $usuarios_result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['correo_institucional']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre_rol']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['fecha_creacion']); ?></td>
                                        <td><span class="badge <?php echo $usuario['estado'] == 'activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo htmlspecialchars($usuario['estado']); ?></span></td>
                                        <td>
                                            <a href="admin_users.php?action=view&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                            <a href="admin_users.php?action=edit&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                            <a href="admin_users.php?action=delete&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">Eliminar</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($modulo === 'reservas'): ?>
                <!-- Módulo: Reservas de Espacio -->
                <h2><i class="fas fa-calendar-check me-2"></i>Reservas de Espacio</h2>

                <!-- Calendario -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Calendario de Reservas</h5>
                    </div>
                    <div class="card-body">
                        <div id="calendar" style="height: 600px;"></div>
                    </div>
                </div>

                <!-- Mapa del Estacionamiento -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Mapa del Estacionamiento</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Legenda:</strong> <span style="background: green; padding: 5px; border-radius: 5px;">Ocupado</span> | <span style="background: lightgray; padding: 5px; border-radius: 5px;">Disponible</span></p>
                        <div class="parking-map" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                            <img src="" alt="">
                            <?php
                            // Obtener todos los espacios
                            $stmt_espacios = $conn->prepare("SELECT * FROM espacios WHERE estado = 'activo' ORDER BY codigo_espacio ASC");
                            $stmt_espacios->execute();
                            $espacios_result = $stmt_espacios->get_result();

                            while ($espacio = $espacios_result->fetch_assoc()):
                                $color = $espacio['disponibilidad'] == 'ocupado' ? 'green' : 'lightgray';
                                $tooltip = "Espacio: {$espacio['codigo_espacio']} - Tipo: {$espacio['tipo']} - Estado: {$espacio['disponibilidad']}";
                            ?>
                                <div class="parking-spot" style="width: 80px; height: 80px; background: <?php echo $color; ?>; border: 2px solid #333; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer;" title="<?php echo $tooltip; ?>">
                                    <?php echo htmlspecialchars($espacio['codigo_espacio']); ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Script para el calendario -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var calendarEl = document.getElementById('calendar');
                        var calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            events: [
                                <?php
                                // Obtener reservas confirmadas para el calendario
                                $stmt_reservas_cal = $conn->prepare("
                                    SELECT r.*, u.nombre_completo, v.placa, e.codigo_espacio
                                    FROM reservas r
                                    INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                                    INNER JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
                                    INNER JOIN espacios e ON r.id_espacio = e.id_espacio
                                    WHERE r.estado = 'confirmada'
                                ");
                                $stmt_reservas_cal->execute();
                                $reservas_cal_result = $stmt_reservas_cal->get_result();
                                $events = [];
                                while ($reserva = $reservas_cal_result->fetch_assoc()) {
                                    $events[] = [
                                        'title' => "Reserva: {$reserva['placa']} ({$reserva['nombre_completo']}) - Espacio: {$reserva['codigo_espacio']}",
                                        'start' => "{$reserva['fecha']}T{$reserva['hora_inicio']}",
                                        'end' => "{$reserva['fecha']}T{$reserva['hora_fin']}",
                                        'description' => "Reservado por: {$reserva['nombre_completo']}\nPlaca: {$reserva['placa']}\nEspacio: {$reserva['codigo_espacio']}\nHora: {$reserva['hora_inicio']} - {$reserva['hora_fin']}",
                                        'backgroundColor' => '#28a745',
                                        'borderColor' => '#28a745',
                                        'textColor' => '#fff'
                                    ];
                                }
                                echo json_encode($events);
                                ?>
                            ],
                            eventClick: function(info) {
                                alert(info.event.extendedProps.description);
                            },
                            locale: 'es'
                        });
                        calendar.render();
                    });
                </script>

            <?php else: ?>
                <div class="alert alert-warning">Módulo no encontrado.</div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="admin_dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Volver al Panel Principal</a>
            </div>

        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>