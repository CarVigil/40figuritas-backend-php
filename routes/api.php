<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/rate_limit.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/QuestionController.php';

// CORS solo para orígenes permitidos (configura ALLOWED_ORIGINS en .env, separados por comas)
$allowedOrigins = parseAllowedOrigins();
sendCors($allowedOrigins, false);

// Manejo de preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Rate limit simple por IP (60 req / 60s por defecto)
$clientIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
rateLimit($clientIp, 60, 60);

// Todos los endpoints requieren clave interna (server-to-server)
requireServerAuth();

// Instanciar controladores
$userController = new UserController($GLOBALS['mysqli']);
$questionController = new QuestionController($GLOBALS['mysqli']);

// Parsear la URL
$method = $_SERVER['REQUEST_METHOD'];

// Obtener la ruta de la solicitud sin query string
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Calcular el prefijo/base de la aplicación (donde está index.php)
$scriptName = $_SERVER['SCRIPT_NAME']; // ej: /server-php/index.php
$basePath = rtrim(dirname($scriptName), '/\\');

// Remover el prefijo/base de la ruta solicitada
$request = $requestUri;
if ($basePath !== '' && strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}

$request = ltrim($request, '/');

// Rutas
$parts = explode('/', $request);

if (empty($parts[0])) {
    echo json_encode(['message' => 'API de 40 Figuritas']);
    exit();
}

$resource = $parts[0];
$id = isset($parts[1]) ? $parts[1] : null;

// RUTAS DE LOGIN (verificar ANTES que /users)
if ($resource === 'users' && isset($parts[1]) && $parts[1] === 'login') {
    if ($method === 'POST') {
        $userController->login();
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
}
// RUTAS DE USUARIOS
elseif ($resource === 'users') {
    if ($method === 'GET' && $id) {
        $userController->getById($id);
    } elseif ($method === 'GET') {
        $userController->getAll();
    } elseif ($method === 'POST') {
        $userController->create();
    } elseif ($method === 'PUT' && $id) {
        $userController->update($id);
    } elseif ($method === 'DELETE' && $id) {
        $userController->delete($id);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
}
// RUTAS DE PREGUNTAS
elseif ($resource === 'questions') {
    if ($method === 'GET' && $id) {
        $questionController->getByUserId($id);
    } elseif ($method === 'GET') {
        $questionController->getAll();
    } elseif ($method === 'POST') {
        $questionController->create();
    } elseif ($method === 'PUT' && $id) {
        $questionController->update($id);
    } elseif ($method === 'DELETE' && $id) {
        $questionController->deleteById($id);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
}
else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
}

?>
