<?php

/*
 * CORS simple en PHP puro.
 * Configura ALLOWED_ORIGINS en .env separado por comas (ej: https://midominio.com,https://app.com).
 * Si no hay header Origin (llamada servidor-servidor), se deja pasar sin CORS.
 */

function parseAllowedOrigins() {
    $env = getenv('ALLOWED_ORIGINS');
    if (!$env) {
        return [];
    }
    $parts = array_map('trim', explode(',', $env));
    return array_filter($parts, function ($o) {
        return $o !== '';
    });
}

function sendCors(array $allowedOrigins = [], $allowCredentials = false) {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
    $allowAll = in_array('*', $allowedOrigins, true);

    if ($origin) {
        if ($allowAll) {
            header('Access-Control-Allow-Origin: *');
        } elseif (in_array($origin, $allowedOrigins, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            if ($allowCredentials) {
                header('Access-Control-Allow-Credentials: true');
            }
        } else {
            http_response_code(403);
            die(json_encode(['error' => 'Origen no permitido']));
        }
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Internal-Key');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}

?>
