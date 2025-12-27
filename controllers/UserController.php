<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/mail.php';

class UserController {
    private $userModel;

    public function __construct($mysqli) {
        $this->userModel = new User($mysqli);
    }

    public function getAll() {
        header('Content-Type: application/json');
        $users = $this->userModel->getAll();
        echo json_encode($users);
    }

    public function getById($id) {
        header('Content-Type: application/json');
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }
        
        echo json_encode($user);
    }

    public function create() {
        header('Content-Type: application/json');
        
        // Intentar leer JSON desde el body
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        // Si no hay JSON (por ejemplo se envía form-data), tomar de $_POST
        if ((!$data || !is_array($data)) && !empty($_POST)) {
            $data = $_POST;
        }
        
        if (!isset($data['fullname']) || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan campos requeridos']);
            return;
        }

        // Validar email único
        if ($this->userModel->emailExists($data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El email ingresado ya está en uso']);
            return;
        }

        // Crear usuario
        if (!$this->userModel->create($data['fullname'], $data['email'], $data['password'])) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al registrar el usuario']);
            return;
        }

        // Enviar email (comentado: implementar función sendWelcomeEmail en config/mail.php)
        // sendWelcomeEmail($data['email'], $data['fullname'], $data['password']);

        http_response_code(201);
        echo json_encode(['message' => 'Usuario creado con éxito']);
    }

    public function login() {
        header('Content-Type: application/json');
        
        // Leer cuerpo (JSON o form-data)
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        if ((!$data || !is_array($data)) && !empty($_POST)) {
            $data = $_POST;
        }

        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan campos requeridos']);
            return;
        }

        $user = $this->userModel->getByEmail($data['email']);
        
        if (!$user || !$this->userModel->verifyPassword($data['password'], $user['pass'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Email o contraseña no válidos']);
            return;
        }

        // Generar token JWT
        $payload = [
            'userId' => $user['id'],
            'name' => $user['fullname'],
            'email' => $user['email'],
            'exp' => time() + (2 * 3600) // 2 horas
        ];
        
        $token = JWT::encode($payload);

        echo json_encode(['token' => $token]);
    }

    public function update($id) {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->userModel->update($id, $data)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el usuario']);
            return;
        }

        echo json_encode(['message' => 'Usuario actualizado']);
    }

    public function delete($id) {
        header('Content-Type: application/json');
        
        if (!$this->userModel->delete($id)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar el usuario']);
            return;
        }

        echo json_encode(['message' => 'Usuario eliminado']);
    }
}

?>
