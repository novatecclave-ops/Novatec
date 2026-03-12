<?php
session_start(); 
require_once'config.php';
require_once'includes/functions.php';
check_session(); 
$page_title='Solicitar Lugar de Estacionamiento';
$user_id=$_SESSION['user_id'];

// Obtener vehículos del usuario
$stmt=$conn->prepare("SELECT * FROM vehiculos WHERE id_usuario=? AND estado_registro='activo'"); 
$stmt->bind_param("i",$user_id); 
$stmt->execute(); 
$vehicles_result=$stmt->get_result();

// Manejar el envío del formulario
if($_SERVER["REQUEST_METHOD"]=="POST"){ 
    $id_vehiculo=$_POST['id_vehiculo']??'';
    $fecha=$_POST['fecha']??''; 
    $hora_inicio=$_POST['hora_inicio']??''; 
    $hora_fin=$_POST['hora_fin']??'';
    // NUEVOS CAMPOS - Tipo de persona y discapacidad
    $tipo_persona=$_POST['tipo_persona']??'';
    $discapacidad=$_POST['discapacidad']??'no';
    
    $result= request_parking_space($user_id,$id_vehiculo,$fecha,$hora_inicio, $hora_fin, $tipo_persona, $discapacidad);

    if($result['success']){ 
        $success_message=$result['message']; 
    } else{ 
        $error_message=$result['message']; 
    } 
} 
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
<h1 class="mb-4"><i class="fas fa-parking me-2"></i>Solicitar Lugar de Estacionamiento</h1>
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
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Formulario de Solicitud</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="id_vehiculo" class="form-label">Vehículo</label>
                <select class="form-select" id="id_vehiculo" name="id_vehiculo" required>
                    <option value="">Selecciona un vehículo...</option>
                    <?php while($vehicle=$vehicles_result->fetch_assoc()):?> 
                        <option value="<?php echo$vehicle['id_vehiculo'];?>">
                            <?php echo htmlspecialchars($vehicle['marca'].' '.$vehicle['modelo'].' ('.$vehicle['placa'].')');?>
                        </option> 
                    <?php endwhile;?>
                </select>
            </div>
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required min="<?php echo date('Y-m-d');?>"> 
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="hora_fin" class="form-label">Hora de Fin</label>
                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" required>
                    </div>
                </div>
            </div>
            
            <!-- NUEVOS CAMPOS AGREGADOS -->
            <div class="mb-3">
                <label for="tipo_persona" class="form-label">Tipo de Persona</label>
                <select class="form-select" id="tipo_persona" name="tipo_persona" required>
                    <option value="">Seleccione una opción...</option>
                    <option value="directivo">Directivo</option>
                    <option value="administrador">Administrador</option>
                    <option value="maestro">Maestro</option>
                    <option value="invitado">Invitado</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">¿Persona con discapacidad?</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discapacidad" id="discapacidad_no" value="no" checked>
                    <label class="form-check-label" for="discapacidad_no">
                        No
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="discapacidad" id="discapacidad_si" value="si">
                    <label class="form-check-label" for="discapacidad_si">
                        Sí
                    </label>
                </div>
            </div>
            <!-- FIN DE NUEVOS CAMPOS -->

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Solicitar Lugar</button>
            </div>
        </form>
    </div>
</div>
<div class="mt-3">
    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Volver al Panel</a>
</div>
<?php include'includes/footer.php';?>
</body>
</html>