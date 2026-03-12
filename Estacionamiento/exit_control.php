<?php
session_start(); 
require_once'config.php';
require_once'includes/functions.php';
check_session(); 

if(!is_guard()){ 
    redirect('dashboard.php'); 
}

$page_title='Control de Salida';

// Manejar el envío del formulario
if($_SERVER["REQUEST_METHOD"]=="POST"){ 
    $qr_code_or_plate=$_POST['qr_code_or_plate']??''; 
    $guardia_id=$_SESSION['user_id'];

    $result= register_exit($qr_code_or_plate,$guardia_id);

    if($result['success']){ 
        $success_message=$result['message']; 
    } else{ 
        $error_message=$result['message']; 
    } 
} 

// Obtener las últimas 10 salidas registradas de hoy
$stmt_salidas=$conn->prepare("
    SELECT 
        h.fecha_hora,
        u.nombre_completo,
        u.correo_institucional,
        v.placa,
        v.marca,
        v.modelo,
        v.tipo,
        v.color,
        e.codigo_espacio,
        h.resultado
    FROM historial_accesos h
    INNER JOIN usuarios u ON h.id_usuario = u.id_usuario
    INNER JOIN vehiculos v ON h.id_vehiculo = v.id_vehiculo
    LEFT JOIN espacios e ON h.id_espacio = e.id_espacio
    WHERE h.tipo_movimiento = 'salida'
    AND DATE(h.fecha_hora) = CURDATE()
    ORDER BY h.fecha_hora DESC
    LIMIT 10
");
$stmt_salidas->execute();
$salidas_result=$stmt_salidas->get_result();
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
<h1 class="mb-4"><i class="fas fa-door-closed me-2"></i>Control de Salida</h1>

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

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Registrar Salida</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="qr_code_or_plate" class="form-label">Código QR o Placa del Vehículo</label>
                        <input type="text" class="form-control" id="qr_code_or_plate" name="qr_code_or_plate" required placeholder="Escanea el QR o ingresa la placa">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-check me-2"></i>Registrar Salida
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Salidas de Hoy</h5>
            </div>
            <div class="card-body">
                <?php if($salidas_result->num_rows > 0):?> 
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Usuario</th>
                                <th>Placa</th>
                                <th>Vehículo</th>
                                <th>Tipo</th>
                                <th>Espacio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($salida=$salidas_result->fetch_assoc()):?> 
                            <tr>
                                <td><?php echo date('H:i', strtotime($salida['fecha_hora']));?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($salida['nombre_completo']);?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($salida['correo_institucional']);?></small>
                                </td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($salida['placa']);?></span></td>
                                <td>
                                    <?php echo htmlspecialchars($salida['marca'].' '.$salida['modelo']);?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($salida['color']);?></small>
                                </td>
                                <td>
                                    <?php if($salida['tipo']=='auto'):?>
                                        <span class="badge bg-primary"><i class="fas fa-car me-1"></i>Auto</span>
                                    <?php else:?>
                                        <span class="badge bg-warning"><i class="fas fa-motorcycle me-1"></i>Moto</span>
                                    <?php endif;?>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($salida['codigo_espacio']??'N/A');?></span></td>
                                <td>
                                    <?php if($salida['resultado']=='exitoso'):?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Exitoso</span>
                                    <?php else:?>
                                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Fallido</span>
                                    <?php endif;?>
                                </td>
                            </tr>
                            <?php endwhile;?>
                        </tbody>
                    </table>
                </div>
                <?php else:?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No hay salidas registradas hoy.
                </div>
                <?php endif;?>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="dashboard.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver al Panel
    </a>
    <a href="access_control.php" class="btn btn-outline-success">
        <i class="fas fa-door-open me-2"></i>Ir a Control de Entrada
    </a>
</div>

<?php include'includes/footer.php';?>
</body>
</html>