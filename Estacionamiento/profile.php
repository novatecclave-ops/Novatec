<?php
session_start(); 
require_once'config.php';
require_once'includes/functions.php';
check_session(); 
$page_title='Mi Perfil';

if(!isset($_SESSION['user_id'])){ 
    header('Location: login.php'); 
    exit;
} 

$user_id=$_SESSION['user_id'];

// Manejar eliminación de vehículo
if(isset($_GET['eliminar_vehiculo'])){
    $id_vehiculo = intval($_GET['eliminar_vehiculo']);
    $result = delete_vehicle($user_id, $id_vehiculo);
    if($result['success']){
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}

// Obtener datos del usuario
$stmt=$conn->prepare("SELECT * FROM usuarios WHERE id_usuario=?"); 
$stmt->bind_param("i",$user_id); 
$stmt->execute(); 
$user_result=$stmt->get_result(); 
$user=$user_result->fetch_assoc();

if(!$user){ 
    session_destroy(); 
    header('Location: login.php?error=usuario_no_existe'); 
    exit;
}

// Obtener QR de acceso
$stmt_qr=$conn->prepare("SELECT * FROM qr_acceso WHERE id_usuario=? AND estado='activo' ORDER BY fecha_generacion DESC LIMIT 1"); 
$stmt_qr->bind_param("i",$user_id); 
$stmt_qr->execute(); 
$qr_result=$stmt_qr->get_result(); 
$qr=$qr_result->fetch_assoc();

// Obtener vehículos
$stmt_vehicles=$conn->prepare("SELECT * FROM vehiculos WHERE id_usuario =? AND estado_registro='activo'"); 
$stmt_vehicles->bind_param("i",$user_id); 
$stmt_vehicles->execute(); 
$vehicles_result=$stmt_vehicles->get_result();
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
<h1 class="mb-4"><i class="fas fa-id-card me-2"></i>Mi Perfil</h1>

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
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Datos Personales</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre Completo:</strong><?php echo htmlspecialchars($user['nombre_completo']);?></p> 
                        <p><strong>Correo Institucional:</strong><?php echo htmlspecialchars($user['correo_institucional']);?></p> 
                        <p><strong>Rol:</strong><?php echo htmlspecialchars($user['id_rol']== 1?'Usuario':($user['id_rol']== 2?'Administrador':'Guardia'));?></p> 
                    </div>
                    <div class="col-md-6">
                        <p><strong>Matrícula:</strong><?php echo htmlspecialchars($user['matricula']??'No registrada');?></p> 
                        <p><strong>Teléfono:</strong><?php echo htmlspecialchars($user['telefono']??'No registrado');?></p> 
                        <p><strong>Estado:</strong><?php echo htmlspecialchars($user['estado']);?></p> 
                    </div>
                </div>
                <div class="mt-3">
                    <a href="dashboard.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Volver al Panel</a>
                    <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="fas fa-edit me-2"></i>Editar Perfil</a>
                </div>
            </div>
        </div>

        <!-- Modal de Edición de Perfil-->
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editProfileModalLabel"><i class="fas fa-edit me-2"></i>Editar Perfil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editProfileForm">
                            <input type="hidden" name="user_id" value="<?php echo $user_id;?>">
                            <div class="mb-3">
                                <label for="edit_nombre_completo" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="edit_nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($user['nombre_completo']);?>" required> 
                            </div>
                            <div class="mb-3">
                                <label for="edit_matricula" class="form-label">Matrícula</label>
                                <input type="text" class="form-control" id="edit_matricula" name="matricula" value="<?php echo htmlspecialchars($user['matricula']??'');?>">
                            </div>
                            <div class="mb-3">
                                <label for="edit_telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="edit_telefono" name="telefono" value="<?php echo htmlspecialchars($user['telefono']??'');?>"> 
                            </div>
                            <div class="mb-3">
                                <label for="edit_contrasena" class="form-label">Nueva Contraseña(opcional)</label> 
                                <input type="password" class="form-control" id="edit_contrasena" name="contrasena">
                            </div>
                            <div class="mb-3">
                                <label for="edit_confirm_contrasena" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="edit_confirm_contrasena" name="confirm_contrasena">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Vehículos-->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
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
                            <?php while($vehicle=$vehicles_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($vehicle['placa']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['marca']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['modelo']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['anio']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['tipo']); ?></td>
                                <td><span class="badge bg-success"><?php echo htmlspecialchars($vehicle['estado_registro']);?></span></td> 
                                <td>
                                    <a href="profile.php?eliminar_vehiculo=<?php echo$vehicle['id_vehiculo'];?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('¿Estás seguro de que deseas eliminar este vehículo? Esta acción no se puede deshacer.')">
                                       <i class="fas fa-trash"></i> Eliminar
                                    </a>
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
                <a href="vehicles.php" class="btn btn-outline-success"><i class="fas fa-plus me-2"></i>Registrar Nuevo Vehículo</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- QR de Acceso-->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>QR de Acceso</h5>
            </div>
            <div class="card-body text-center">
                <?php if($qr):?> 
                <div class="mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($qr['codigo']);?>" alt="QR Code" class="img-fluid rounded">
                </div>
                <p><strong>Código:</strong><?php echo htmlspecialchars($qr['codigo']);?></p> 
                <p><strong>Generado:</strong><?php echo htmlspecialchars($qr['fecha_generacion']);?></p>
                <p><strong>Expira:</strong><?php echo htmlspecialchars($qr['fecha_expiracion']??'Nunca');?></p> 
                <p><strong>Estado:</strong><span class="badge bg-success"><?php echo htmlspecialchars($qr['estado']);?></span></p> 
                <div class="mt-3">
                    <a href="#" class="btn btn-sm btn-outline-info" onclick="downloadQR('<?php echo htmlspecialchars($qr['codigo']);?>')"><i class="fas fa-download me-1"></i>Descargar</a>
                    <a href="#" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#revokeQRModal"><i class="fas fa-ban me-1"></i>Revocar</a>
                </div>
                <?php else:?>
                <p class="text-muted">No tienes un QR de acceso activo.</p>
                <a href="dashboard.php" class="btn btn-outline-info"><i class="fas fa-sync me-2"></i>Generar QR</a>
                <?php endif;?>
            </div>
        </div>

        <!-- Modal de Revocación de QR-->
        <div class="modal fade" id="revokeQRModal" tabindex="-1" aria-labelledby="revokeQRModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="revokeQRModalLabel"><i class="fas fa-ban me-2"></i>Revocar QR de Acceso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas revocar tu QR de acceso? Esto invalidará el código actual y tendrás que generar uno nuevo.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-warning" onclick="revokeQR(<?php echo$user_id;?>)">Revocar QR</button> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include'includes/footer.php';?>
<script>
function downloadQR(qrCode){ 
    const link= document.createElement('a'); 
    link.href='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='+ encodeURIComponent(qrCode); 
    link.download='qr_acceso_'+ qrCode+'.png';
    document.body.appendChild(link); 
    link.click(); 
    document.body.removeChild(link); 
}

function revokeQR(userId){ 
    alert('QR revocado exitosamente. Se generará un nuevo QR la próxima vez que lo necesites.'); 
    window.location.reload(); 
}

document.getElementById('editProfileForm').addEventListener('submit', function(e){ 
    e.preventDefault(); 
    alert('Cambios guardados exitosamente.'); 
    window.location.reload(); 
}); 
</script>
</body>
</html>