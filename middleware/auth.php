<?php

/*
 * Middleware de autenticación en PHP puro (sin dependencias externas).
 * Recomendaciones:
 * - Definir `JWT_SECRET` e `INTERNAL_API_KEY` en .env o en constantes de config.
 * - Usar `requireServerAuth()` para endpoints que sólo deben ser llamados por otros servidores.
 */

function getAuthToken() {
    $headers = getallheaders();

    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
    }

    return null;
}

function validateJwt($token) {
    if (!$token) {
        return null;
    }

    // JWT::decode es la implementación local en config/jwt.php (HS256)
    $decoded = JWT::decode($token);

    if (!$decoded || !is_array($decoded)) {
        return null;
    }

    // Validaciones adicionales opcionales
    if (isset($decoded['nbf']) && $decoded['nbf'] > time()) {
        return null;
    }
    if (isset($decoded['iat']) && $decoded['iat'] > time()) {
        return null;
    }

    return $decoded;
}

function requireAuth() {
    $token = getAuthToken();

    if (!$token) {
        http_response_code(401);
        die(json_encode(['error' => 'Token no proporcionado']));
    }

    $decoded = validateJwt($token);

    if (!$decoded) {
        http_response_code(401);
        die(json_encode(['error' => 'Token inválido o expirado']));
    }

    return $decoded;
}

function getOptionalAuth() {
    $token = getAuthToken();

    if (!$token) {
        return null;
    }

    return validateJwt($token);
}

/**
 * requireServerAuth: para endpoints que sólo deben ser consumidos por servidores.
 * Debe configurarse `INTERNAL_API_KEY` en variables de entorno con un valor secreto
 * conocido sólo por los servidores autorizados.
 */
function requireServerAuth() {
    $headers = getallheaders();

    $keyHeader = null;
    if (isset($headers['X-Internal-Key'])) {
        $keyHeader = $headers['X-Internal-Key'];
    } elseif (isset($headers['x-internal-key'])) {
        $keyHeader = $headers['x-internal-key'];
    }

    $internalKey = defined('INTERNAL_API_KEY') ? INTERNAL_API_KEY : (getenv('INTERNAL_API_KEY') ?: '');

    if (!$internalKey || !$keyHeader || !hash_equals($internalKey, $keyHeader)) {
        http_response_code(401);
        die(json_encode(['error' => 'Acceso denegado']));
    }

    return true;
}

?>
