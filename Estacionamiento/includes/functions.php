<?php
// includes/functions.php
// Este archivo contiene SOLO funciones de negocio.
// Las funciones auxiliares (sanitize_input, hash_password, etc.) están definidas en config.php.

/**
 * Registra un nuevo usuario en el sistema.
 */
function register_user($nombre_completo, $correo_institucional, $contrasena) {
    global $conn;

    // Validar correo institucional
    if (!filter_var($correo_institucional, FILTER_VALIDATE_EMAIL) || strpos($correo_institucional, '@ipn.mx') === false) {
        return ['success' => false, 'message' => 'El correo debe ser institucional (@ipn.mx).'];
    }

    // Sanitizar entradas
    $nombre_completo = sanitize_input($nombre_completo);
    $correo_institucional = sanitize_input($correo_institucional);
    $contrasena_hash = hash_password($contrasena);

    try {
        // Insertar usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, correo_institucional, contrasena, id_rol) VALUES (?, ?, ?, ?)");
        $default_role = 1; // Rol por defecto: Usuario
        $stmt->bind_param("sssi", $nombre_completo, $correo_institucional, $contrasena_hash, $default_role);
        $stmt->execute();

        // Generar QR de acceso
        $user_id = $conn->insert_id;
        $qr_code = generate_qr_code($user_id);
        $stmt_qr = $conn->prepare("INSERT INTO qr_acceso (id_usuario, codigo, estado) VALUES (?, ?, ?)");
        $qr_state = 'activo';
        $stmt_qr->bind_param("iss", $user_id, $qr_code, $qr_state);
        $stmt_qr->execute();

        return ['success' => true, 'message' => 'Registro exitoso. Se ha enviado un correo de confirmación.'];
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            return ['success' => false, 'message' => 'El correo institucional ya está registrado.'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar el usuario.'];
        }
    }
}

/**
 * Inicia sesión de un usuario.
 */
function login_user($correo_institucional, $contrasena) {
    global $conn;

    $correo_institucional = sanitize_input($correo_institucional);

    // Buscar usuario activo
    $stmt = $conn->prepare("SELECT id_usuario, nombre_completo, contrasena, id_rol FROM usuarios WHERE correo_institucional = ? AND estado = 'activo'");
    $stmt->bind_param("s", $correo_institucional);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (verify_password($contrasena, $user['contrasena'])) {
            session_start();
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['nombre_completo'];
            $_SESSION['user_email'] = $correo_institucional;

            // CORRECCIÓN CLAVE: Obtener el NOMBRE del rol desde la tabla roles
            $role_stmt = $conn->prepare("SELECT nombre_rol FROM roles WHERE id_rol = ?");
            $role_stmt->bind_param("i", $user['id_rol']);
            $role_stmt->execute();
            $role_result = $role_stmt->get_result();
            $role_row = $role_result->fetch_assoc();
            $_SESSION['user_role'] = $role_row ? $role_row['nombre_rol'] : 'Usuario';

            return ['success' => true, 'message' => 'Inicio de sesión exitoso.'];
        } else {
            return ['success' => false, 'message' => 'Contraseña incorrecta.'];
        }
    } else {
        return ['success' => false, 'message' => 'Correo no registrado o cuenta inactiva.'];
    }
}

/**
 * Recupera la contraseña de un usuario.
 */
function recover_password($correo_institucional) {
    global $conn;

    if (!filter_var($correo_institucional, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Formato de correo inválido.'];
    }

    $correo_institucional = sanitize_input($correo_institucional);

    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo_institucional = ?");
    $stmt->bind_param("s", $correo_institucional);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id_usuario'];

        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt_token = $conn->prepare("INSERT INTO password_reset_tokens (id_usuario, token, expiry) VALUES (?, ?, ?)");
        $stmt_token->bind_param("iss", $user_id, $token, $expiry);
        $stmt_token->execute();

        // Nota: El envío real de correo se omite aquí (simulación)
        return ['success' => true, 'message' => 'Se ha enviado un enlace de recuperación a tu correo.'];
    } else {
        return ['success' => false, 'message' => 'El correo ingresado no está registrado.'];
    }
}

/**
 * Restablece la contraseña usando un token.
 */
function reset_password($token, $new_password, $confirm_password) {
    global $conn;

    if ($new_password !== $confirm_password) {
        return ['success' => false, 'message' => 'Las contraseñas no coinciden.'];
    }

    $token = sanitize_input($token);
    $new_password_hash = hash_password($new_password);

    $stmt = $conn->prepare("SELECT id_usuario FROM password_reset_tokens WHERE token = ? AND expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id_usuario'];

        $stmt_update = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?");
        $stmt_update->bind_param("si", $new_password_hash, $user_id);
        $stmt_update->execute();

        $stmt_invalidate = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        $stmt_invalidate->bind_param("s", $token);
        $stmt_invalidate->execute();

        return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
    } else {
        return ['success' => false, 'message' => 'Token inválido o expirado.'];
    }
}

/**
 * Registra un vehículo para un usuario.
 */
function register_vehicle($id_usuario,$placa,$marca,$modelo,$color,$anio, $tipo){ 
    global$conn;
    
    // Verificar límite de vehículos
    $stmt_count=$conn->prepare("SELECT COUNT(*) as total FROM vehiculos WHERE id_usuario=? AND estado_registro='activo'"); 
    $stmt_count->bind_param("i",$id_usuario); 
    $stmt_count->execute(); 
    $count_result=$stmt_count->get_result(); 
    $count=$count_result->fetch_assoc()['total'];
    
    if($count>= 3){ 
        return['success'=> false,'message'=>'No puedes registrar más de 3 vehículos.']; 
    }
    
    // Sanitizar y validar
    $placa= sanitize_input(strtoupper($placa)); 
    $marca= sanitize_input($marca); 
    $modelo= sanitize_input($modelo); 
    $color= sanitize_input($color); 
    $anio= intval($anio); 
    $tipo= sanitize_input($tipo);
    
    if(!in_array($tipo,['auto','moto'])){ 
        return['success'=> false,'message'=>'Tipo de vehículo no válido. Solo se permiten autos y motos.']; 
    }
    
    $stmt=$conn->prepare("INSERT INTO vehiculos(id_usuario, placa, marca, modelo, color, anio, tipo, estado_registro) VALUES(?,?,?,?,?,?,?,?)"); 
    $estado_registro='activo';
    // CORREGIDO: 8 caracteres para 8 variables
    $stmt->bind_param("issssiis",$id_usuario,$placa,$marca,$modelo,$color,$anio,$tipo,$estado_registro);
    
    if($stmt->execute()){ 
        return['success'=> true,'message'=>'Vehículo registrado exitosamente.']; 
    } else{ 
        return['success'=> false,'message'=>'Error al registrar el vehículo.']; 
    } 
}

/**
 * Solicita una reserva de espacio de estacionamiento.
 */
function request_parking_space($id_usuario,$id_vehiculo,$fecha,$hora_inicio, $hora_fin, $tipo_persona, $discapacidad){
    global$conn;
    
    // Validar que el vehículo pertenezca al usuario
    $stmt_check=$conn->prepare("SELECT id_usuario FROM vehiculos WHERE id_vehiculo=?");
    $stmt_check->bind_param("i",$id_vehiculo);
    $stmt_check->execute();
    $check_result=$stmt_check->get_result();
    
    if($check_result->num_rows!= 1){
        return['success'=> false,'message'=>'Vehículo no encontrado.'];
    }
    
    $vehicle=$check_result->fetch_assoc();
    if($vehicle['id_usuario']!=$id_usuario){
        return['success'=> false,'message'=>'No tienes permiso para este vehículo.'];
    }
    
    // Buscar espacio disponible
    $stmt_avail=$conn->prepare("SELECT e.id_espacio, e.codigo_espacio
        FROM espacios e
        LEFT JOIN reservas r ON e.id_espacio= r.id_espacio
        AND r.fecha=?
        AND r.estado='confirmada'
        AND NOT(r.hora_fin<=? OR r.hora_inicio>=?)
        WHERE e.disponibilidad='disponible' AND e.estado='activo'
        LIMIT 1
    ");
    $stmt_avail->bind_param("sss",$fecha,$hora_inicio,$hora_fin);
    $stmt_avail->execute();
    $avail_result=$stmt_avail->get_result();
    
    if($avail_result->num_rows== 0){
        return['success'=> false,'message'=>'No hay lugares disponibles para ese horario.'];
    }
    
    $space=$avail_result->fetch_assoc();
    $id_espacio=$space['id_espacio'];
    
    // Registrar reserva (CON LAS NUEVAS COLUMNAS)
    $stmt_reserve=$conn->prepare("
        INSERT INTO reservas(id_usuario, id_vehiculo, id_espacio, fecha, hora_inicio, hora_fin, tipo_persona, discapacidad, estado) 
        VALUES(?,?,?,?,?,?,?,?,?)
    ");
    $estado_reserva='confirmada';
    $stmt_reserve->bind_param("iiissssss",$id_usuario,$id_vehiculo,$id_espacio, $fecha,$hora_inicio,$hora_fin,$tipo_persona,$discapacidad,$estado_reserva);
    
    if($stmt_reserve->execute()){
        // Actualizar disponibilidad
        $stmt_update=$conn->prepare("UPDATE espacios SET disponibilidad= 'reservado' WHERE id_espacio=?");
        $stmt_update->bind_param("i",$id_espacio);
        $stmt_update->execute();
        
        return['success'=> true,'message'=>'Reserva realizada exitosamente.'];
    } else{
        return['success'=> false,'message'=>'Error al realizar la reserva.'];
    }
}

/**
 * Registra una entrada de vehículo.
 */
function register_entry($qr_code_or_plate, $guardia_id = null){ 
    global $conn;
    
    $qr_code_or_plate = sanitize_input($qr_code_or_plate);
    
    // Primero verificar si es un QR
    $stmt_qr = $conn->prepare("SELECT id_usuario, estado FROM qr_acceso WHERE codigo = ?");
    $stmt_qr->bind_param("s", $qr_code_or_plate);
    $stmt_qr->execute();
    $qr_result = $stmt_qr->get_result();
    
    if($qr_result->num_rows == 1){
        // Es un QR válido
        $qr_data = $qr_result->fetch_assoc();
        
        if($qr_data['estado'] !== 'activo'){
            return ['success' => false, 'message' => 'Acceso denegado: QR inactivo o revocado.'];
        }
        
        $id_usuario = $qr_data['id_usuario'];
        
        // Verificar que el usuario esté activo
        $stmt_user = $conn->prepare("SELECT id_usuario, nombre_completo FROM usuarios WHERE id_usuario = ? AND estado = 'activo'");
        $stmt_user->bind_param("i", $id_usuario);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        
        if($user_result->num_rows != 1){
            return ['success' => false, 'message' => 'Usuario no activo o no encontrado.'];
        }
        
        $user_data = $user_result->fetch_assoc();
        
        // Buscar un vehículo activo del usuario
        $stmt_veh = $conn->prepare("SELECT id_vehiculo, placa FROM vehiculos WHERE id_usuario = ? AND estado_registro = 'activo' LIMIT 1");
        $stmt_veh->bind_param("i", $id_usuario);
        $stmt_veh->execute();
        $veh_result = $stmt_veh->get_result();
        
        if($veh_result->num_rows == 0){
            return ['success' => false, 'message' => 'El usuario no tiene vehículos registrados.'];
        }
        
        $veh_data = $veh_result->fetch_assoc();
        
        $data = [
            'id_usuario' => $user_data['id_usuario'],
            'nombre_completo' => $user_data['nombre_completo'],
            'id_vehiculo' => $veh_data['id_vehiculo'],
            'placa' => $veh_data['placa']
        ];
        
    } else {
        // No es QR, intentar con placa
        $stmt = $conn->prepare("
            SELECT u.id_usuario, u.nombre_completo, v.id_vehiculo, v.placa
            FROM usuarios u
            INNER JOIN vehiculos v ON u.id_usuario = v.id_usuario
            WHERE v.placa = ? AND u.estado = 'activo' AND v.estado_registro = 'activo'
            LIMIT 1
        ");
        $stmt->bind_param("s", $qr_code_or_plate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows != 1){
            return ['success' => false, 'message' => 'Vehículo no registrado o QR inválido.'];
        }
        
        $data = $result->fetch_assoc();
    }
    
    // Buscar espacio disponible
    $stmt_space = $conn->prepare("SELECT id_espacio FROM espacios WHERE disponibilidad = 'disponible' AND estado = 'activo' LIMIT 1");
    $stmt_space->execute();
    $space_result = $stmt_space->get_result();
    
    if($space_result->num_rows == 0){
        return ['success' => false, 'message' => 'No hay espacios disponibles.'];
    }
    
    $space = $space_result->fetch_assoc();
    $id_espacio = $space['id_espacio'];
    
    // Registrar entrada
    $stmt_hist = $conn->prepare("INSERT INTO historial_accesos(id_usuario, id_vehiculo, id_espacio, tipo_movimiento, resultado) VALUES(?,?,?,'entrada', 'exitoso')");
    $stmt_hist->bind_param("iii", $data['id_usuario'], $data['id_vehiculo'], $id_espacio);
    $stmt_hist->execute();
    
    // Marcar espacio como ocupado
    $stmt_upd = $conn->prepare("UPDATE espacios SET disponibilidad = 'ocupado' WHERE id_espacio = ?");
    $stmt_upd->bind_param("i", $id_espacio);
    $stmt_upd->execute();
    
    return ['success' => true, 'message' => 'Acceso autorizado. Bienvenido, ' . $data['nombre_completo'] . '.'];
}

/**
 * Registra una salida de vehículo.
 */
function register_exit($qr_code_or_plate, $guardia_id = null) {
    global $conn;
    $qr_code_or_plate = sanitize_input($qr_code_or_plate);

    $stmt = $conn->prepare("
        SELECT h.id_historial, h.id_espacio, h.id_usuario, h.id_vehiculo
        FROM historial_accesos h
        INNER JOIN vehiculos v ON h.id_vehiculo = v.id_vehiculo
        LEFT JOIN qr_acceso q ON v.id_usuario = q.id_usuario AND q.estado = 'activo'
        WHERE (q.codigo = ? OR v.placa = ?) AND h.tipo_movimiento = 'entrada' AND h.resultado = 'exitoso'
        ORDER BY h.fecha_hora DESC LIMIT 1
    ");
    $stmt->bind_param("ss", $qr_code_or_plate, $qr_code_or_plate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows != 1) {
        return ['success' => false, 'message' => 'Vehículo sin entrada registrada.'];
    }

    $entry = $result->fetch_assoc();

    // Registrar salida
    $stmt_out = $conn->prepare("INSERT INTO historial_accesos (id_usuario, id_vehiculo, id_espacio, tipo_movimiento, resultado) VALUES (?, ?, ?, 'salida', 'exitoso')");
    $stmt_out->bind_param("iii", $entry['id_usuario'], $entry['id_vehiculo'], $entry['id_espacio']);
    $stmt_out->execute();

    // Liberar espacio
    $stmt_free = $conn->prepare("UPDATE espacios SET disponibilidad = 'disponible' WHERE id_espacio = ?");
    $stmt_free->bind_param("i", $entry['id_espacio']);
    $stmt_free->execute();

    return ['success' => true, 'message' => 'Salida registrada correctamente. Hasta pronto.'];
}

/**
 * Gestiona usuarios (crear, editar, eliminar).
 */
function manage_user($action, $id_usuario = null, $nombre_completo = null, $correo_institucional = null, $contrasena = null, $id_rol = null, $matricula = null, $telefono = null, $estado = null) {
    global $conn;

    switch ($action) {
        case 'create':
            if (!filter_var($correo_institucional, FILTER_VALIDATE_EMAIL) || strpos($correo_institucional, '@ipn.mx') === false) {
                return ['success' => false, 'message' => 'El correo debe ser institucional (@ipn.mx).'];
            }
            $nombre_completo = sanitize_input($nombre_completo);
            $correo_institucional = sanitize_input($correo_institucional);
            $contrasena_hash = hash_password($contrasena);
            $matricula = sanitize_input($matricula);
            $telefono = sanitize_input($telefono);
            $estado = sanitize_input($estado);

            $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, correo_institucional, contrasena, id_rol, matricula, telefono, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiisss", $nombre_completo, $correo_institucional, $contrasena_hash, $id_rol, $matricula, $telefono, $estado);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Usuario creado exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al crear el usuario.'];
            }

        case 'edit':
            $id_usuario = intval($id_usuario);
            $nombre_completo = sanitize_input($nombre_completo);
            $correo_institucional = sanitize_input($correo_institucional);
            $matricula = sanitize_input($matricula);
            $telefono = sanitize_input($telefono);
            $estado = sanitize_input($estado);

            $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo = ?, correo_institucional = ?, matricula = ?, telefono = ?, estado = ? WHERE id_usuario = ?");
            $stmt->bind_param("sssssi", $nombre_completo, $correo_institucional, $matricula, $telefono, $estado, $id_usuario);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Usuario actualizado exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el usuario.'];
            }

        case 'delete':
            // Eliminar dependencias
            $tables = ['vehiculos', 'reservas', 'qr_acceso', 'historial_accesos'];
            foreach ($tables as $table) {
                $stmt_del = $conn->prepare("DELETE FROM $table WHERE id_usuario = ?");
                $stmt_del->bind_param("i", $id_usuario);
                $stmt_del->execute();
            }

            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Usuario eliminado exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar el usuario.'];
            }

        default:
            return ['success' => false, 'message' => 'Acción no válida.'];
    }
}

/**
 * Limpia registros semestrales.
 */
function clean_semester_records() {
    global $conn;

    $tables = ['reservas', 'vehiculos', 'qr_acceso'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("DELETE FROM $table");
        $stmt->execute();
    }

    $stmt = $conn->prepare("UPDATE espacios SET disponibilidad = 'disponible'");
    $stmt->execute();

    return ['success' => true, 'message' => 'Registros semestrales limpiados exitosamente.'];
}


/**
 * Elimina una reserva de estacionamiento.
 */
function cancel_reservation($id_usuario, $id_reserva){
    global $conn;
    
    // Verificar que la reserva pertenezca al usuario
    $stmt_check = $conn->prepare("SELECT id_usuario, id_espacio, estado FROM reservas WHERE id_reserva = ?");
    $stmt_check->bind_param("i", $id_reserva);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    if($check_result->num_rows != 1){
        return ['success' => false, 'message' => 'Reserva no encontrada.'];
    }
    
    $reserva = $check_result->fetch_assoc();
    
    if($reserva['id_usuario'] != $id_usuario){
        return ['success' => false, 'message' => 'No tienes permiso para cancelar esta reserva.'];
    }
    
    if($reserva['estado'] == 'cancelada'){
        return ['success' => false, 'message' => 'Esta reserva ya está cancelada.'];
    }
    
    // Eliminar la reserva
    $stmt_delete = $conn->prepare("DELETE FROM reservas WHERE id_reserva = ?");
    $stmt_delete->bind_param("i", $id_reserva);
    
    if($stmt_delete->execute()){
        // Liberar el espacio
        $stmt_update = $conn->prepare("UPDATE espacios SET disponibilidad = 'disponible' WHERE id_espacio = ?");
        $stmt_update->bind_param("i", $reserva['id_espacio']);
        $stmt_update->execute();
        
        return ['success' => true, 'message' => 'Reserva cancelada exitosamente.'];
    } else {
        return ['success' => false, 'message' => 'Error al cancelar la reserva.'];
    }
}

/**
 * Elimina un vehículo del usuario.
 */
function delete_vehicle($id_usuario, $id_vehiculo){
    global $conn;
    
    // Verificar que el vehículo pertenezca al usuario
    $stmt_check = $conn->prepare("SELECT id_usuario, placa FROM vehiculos WHERE id_vehiculo = ? AND estado_registro = 'activo'");
    $stmt_check->bind_param("i", $id_vehiculo);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    if($check_result->num_rows != 1){
        return ['success' => false, 'message' => 'Vehículo no encontrado.'];
    }
    
    $vehiculo = $check_result->fetch_assoc();
    
    if($vehiculo['id_usuario'] != $id_usuario){
        return ['success' => false, 'message' => 'No tienes permiso para eliminar este vehículo.'];
    }
    
    // Verificar que no tenga reservas activas
    $stmt_reservas = $conn->prepare("SELECT COUNT(*) as total FROM reservas WHERE id_vehiculo = ? AND estado = 'confirmada' AND fecha >= CURDATE()");
    $stmt_reservas->bind_param("i", $id_vehiculo);
    $stmt_reservas->execute();
    $reservas_result = $stmt_reservas->get_result();
    $reservas_count = $reservas_result->fetch_assoc()['total'];
    
    if($reservas_count > 0){
        return ['success' => false, 'message' => 'No puedes eliminar este vehículo porque tiene reservas activas. Cancela las reservas primero.'];
    }
    
    // Eliminar el vehículo (cambiar estado a inactivo)
    $stmt_delete = $conn->prepare("UPDATE vehiculos SET estado_registro = 'inactivo' WHERE id_vehiculo = ?");
    $stmt_delete->bind_param("i", $id_vehiculo);
    
    if($stmt_delete->execute()){
        return ['success' => true, 'message' => 'Vehículo eliminado exitosamente.'];
    } else {
        return ['success' => false, 'message' => 'Error al eliminar el vehículo.'];
    }
}
?>