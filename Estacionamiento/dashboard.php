<?php
session_start(); 
require_once'config.php';
require_once'includes/functions.php';
check_session(); 
$page_title='Panel de Control';

// Obtener información del usuario
$user_id=$_SESSION['user_id']; 
$user_name=$_SESSION['user_name']; 
$user_email=$_SESSION['user_email']; 
$user_role=$_SESSION['user_role'];

// Manejar cancelación de reserva
if(isset($_GET['cancelar_reserva'])){
    $id_reserva = intval($_GET['cancelar_reserva']);
    $result = cancel_reservation($user_id, $id_reserva);
    if($result['success']){
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}

// Obtener vehículos del usuario
$stmt=$conn->prepare("SELECT * FROM vehiculos WHERE id_usuario=? AND estado_registro='activo'"); 
$stmt->bind_param("i",$user_id); 
$stmt->execute(); 
$vehicles_result=$stmt->get_result();

// Obtener reservas activas del usuario
$stmt_reservas=$conn->prepare("
    SELECT r.*, e.codigo_espacio, e.tipo as tipo_espacio
    FROM reservas r 
    INNER JOIN espacios e ON r.id_espacio= e.id_espacio 
    WHERE r.id_usuario=? 
    AND r.estado='confirmada' 
    AND r.fecha>= CURDATE() 
    ORDER BY r.fecha, r.hora_inicio
");
$stmt_reservas->bind_param("i",$user_id); 
$stmt_reservas->execute(); 
$reservas_result=$stmt_reservas->get_result();

// Obtener historial de accesos recientes
$stmt_historial=$conn->prepare("
    SELECT h.*, v.placa, e.codigo_espacio 
    FROM historial_accesos h 
    INNER JOIN vehiculos v ON h.id_vehiculo= v.id_vehiculo 
    LEFT JOIN espacios e ON h.id_espacio= e.id_espacio 
    WHERE h.id_usuario=?
    ORDER BY h.fecha_hora DESC 
    LIMIT 5
"); 
$stmt_historial->bind_param("i",$user_id); 
$stmt_historial->execute(); 
$historial_result=$stmt_historial->get_result(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo$page_title;?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include'includes/header.php';?>
<h1 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Panel de Control</h1>

<?php if(isset($error_message)):?> 
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo$error_message;?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif;?>

<?php if(isset($success_message)):?> 
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo$success_message;?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif;?>

<!-- Tarjetas de Resumen-->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-car me-2"></i>Vehículos Registrados</h5>
                <p class="card-text"><?php echo$vehicles_result->num_rows;?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-calendar-check me-2"></i>Reservas Activas</h5>
                <p class="card-text"><?php echo$reservas_result->num_rows;?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-history me-2"></i>Accesos Recientes</h5>
                <p class="card-text"><?php echo$historial_result->num_rows;?></p>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Vehículos-->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-car me-2"></i>Mis Vehículos</h5>
    </div>
    <div class="card-body">
        <?php if($vehicles_result->num_rows> 0):?> 
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Año</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($vehicle=$vehicles_result->fetch_assoc()):?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle['placa']);?></td> 
                        <td><?php echo htmlspecialchars($vehicle['marca']);?></td>
                        <td><?php echo htmlspecialchars($vehicle['modelo']);?></td>
                        <td><?php echo htmlspecialchars($vehicle['anio']);?></td> 
                        <td><?php echo htmlspecialchars($vehicle['tipo']);?></td> 
                        <td><span class="badge bg-success"><?php echo htmlspecialchars($vehicle['estado_registro']);?></span></td> 
                        <td>
                            <a href="profile.php" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
                        </td>
                    </tr>
                    <?php endwhile;?>
                </tbody>
            </table>
        </div>
        <?php else:?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No has registrado ningún vehículo aún.<a href="vehicles.php">Registra tu primer vehículo</a>.
        </div>
        <?php endif;?>
    </div>
</div>

<!-- Sección de Reservas-->
<div class="card mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Mis Reservas</h5>
        <a href="request_parking.php" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-1"></i>Nueva Reserva
        </a>
    </div>
    <div class="card-body">
        <?php if($reservas_result->num_rows> 0):?> 
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Espacio</th>
                        <th>Tipo Persona</th>
                        <th>Discapacidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($reserva=$reservas_result->fetch_assoc()):?> 
                    <tr>
                        <td><?php echo htmlspecialchars($reserva['fecha']);?></td>
                        <td><?php echo htmlspecialchars($reserva['hora_inicio']).' - '.htmlspecialchars($reserva['hora_fin']);?></td> 
                        <td><?php echo htmlspecialchars($reserva['codigo_espacio']);?></td> 
                        <td><?php echo htmlspecialchars($reserva['tipo_persona'] ?? 'N/A');?></td>
                        <td>
                            <?php 
                            $discap = $reserva['discapacidad'] ?? 'no';
                            if($discap == 'si'):?>
                                <span class="badge bg-warning">Sí</span>
                            <?php else:?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif;?>
                        </td>
                        <td><span class="badge bg-success"><?php echo htmlspecialchars($reserva['estado']);?></span></td>
                        <td>
                            <a href="request_parking.php" class="btn btn-sm btn-outline-success">Modificar</a>
                            <a href="dashboard.php?cancelar_reserva=<?php echo$reserva['id_reserva'];?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('¿Estás seguro de que deseas cancelar esta reserva?')">
                               <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile;?>
                </tbody>
            </table>
        </div>
        <?php else:?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No tienes reservas activas. 
            <a href="request_parking.php" class="alert-link">Solicita un espacio</a>.
        </div>
        <?php endif;?>
    </div>
</div>

<!-- Sección de Historial de Accesos-->
<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Accesos Recientes</h5>
    </div>
    <div class="card-body">
        <?php if($historial_result->num_rows> 0):?> 
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Vehículo</th>
                        <th>Espacio</th>
                        <th>Movimiento</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($historial=$historial_result->fetch_assoc()):?> 
                    <tr>
                        <td><?php echo htmlspecialchars($historial['fecha_hora']);?></td>
                        <td><?php echo htmlspecialchars($historial['placa']);?></td>
                        <td><?php echo htmlspecialchars($historial['codigo_espacio']??'N/A');?></td> 
                        <td>
                            <?php if($historial['tipo_movimiento']=='entrada'):?> 
                            <span class="badge bg-primary">Entrada</span>
                            <?php else:?>
                            <span class="badge bg-secondary">Salida</span>
                            <?php endif;?>
                        </td>
                        <td>
                            <?php if($historial['resultado']=='exitoso'):?> 
                            <span class="badge bg-success">Exitoso</span>
                            <?php else:?>
                            <span class="badge bg-danger">Fallido</span>
                            <?php endif;?>
                        </td>
                    </tr>
                    <?php endwhile;?>
                </tbody>
            </table>
        </div>
        <?php else:?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No hay historial de accesos recientes.
        </div>
        <?php endif;?>
    </div>
</div>

<?php include'includes/footer.php';?>
</body>
</html>