<?php

// Cargar variables de entorno desde .env
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv($key . '=' . $value);
            }
        }
    }
}

// Configuración de base de datos
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'app40ad');

// Configuración JWT
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'MySeCrtKeywow4325');
define('INTERNAL_API_KEY', getenv('INTERNAL_API_KEY') ?: '');

// Configuración Email
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_FROM', getenv('MAIL_FROM') ?: '40figuritas@gmail.com');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: 'bifdmreabsboaruf');

// Crear conexión a MySQL
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Connection failed: ' . $mysqli->connect_error]));
}

// Set charset
$mysqli->set_charset('utf8mb4');

?>
