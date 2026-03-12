<?php
// config.php

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Cambia esto por tu usuario de MySQL
define('DB_PASS', '');     // Cambia esto por tu contraseña de MySQL
define('DB_NAME', 'estacionamiento_cecyt9');

// Conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para sanitizar entradas
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para generar tokens seguros
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// Función para hashear contraseñas
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Función para verificar contraseñas
function verify_password($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}

// Función para mostrar mensajes de alerta
function show_alert($message, $type = 'info') {
    echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
            $message
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
}

// Función para verificar sesión
function check_session() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// Función para verificar rol de administrador
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Administrador';
}

// Función para verificar rol de guardia
function is_guard() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Guardia';
}

// Función para generar un código QR (simulación)
function generate_qr_code($user_id) {
    // En un sistema real, usarías una librería como phpqrcode
    // Aquí generamos un código único simple para demostración
    return "QR-" . strtoupper(substr(md5($user_id . time()), 0, 8));
}

?>