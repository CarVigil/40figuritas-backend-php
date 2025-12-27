<?php

require_once __DIR__ . '/../models/Question.php';

class QuestionController {
    private $questionModel;

    public function __construct($mysqli) {
        $this->questionModel = new Question($mysqli);
    }

    public function getAll() {
        header('Content-Type: application/json');
        $questions = $this->questionModel->getAll();
        echo json_encode($questions);
    }

    public function getById($id) {
        header('Content-Type: application/json');
        $question = $this->questionModel->getById($id);
        
        if (!$question) {
            http_response_code(404);
            echo json_encode(['error' => 'Pregunta no encontrada']);
            return;
        }
        
        echo json_encode($question);
    }

    public function getByUserId($userId) {
        header('Content-Type: application/json');
        $questions = $this->questionModel->getByUserId($userId);
        echo json_encode($questions);
    }

    public function create() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['questionNumber']) || !isset($data['userId']) || !isset($data['cardAssigned'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan campos requeridos']);
            return;
        }

        if (!$this->questionModel->create($data['questionNumber'], $data['userId'], $data['cardAssigned'])) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear la pregunta']);
            return;
        }

        http_response_code(201);
        echo json_encode(['message' => 'Pregunta creada']);
    }

    public function update($id) {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->questionModel->update($id, $data)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar la pregunta']);
            return;
        }

        echo json_encode(['message' => 'Pregunta actualizada']);
    }

    public function deleteById($id) {
        header('Content-Type: application/json');
        
        if (!$this->questionModel->deleteById($id)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar la pregunta']);
            return;
        }

        echo json_encode(['message' => 'Pregunta eliminada']);
    }

    public function deleteByUserId($userId) {
        header('Content-Type: application/json');
        
        if (!$this->questionModel->deleteByUserId($userId)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar las preguntas del usuario']);
            return;
        }

        echo json_encode(['message' => 'Preguntas eliminadas']);
    }
}

?>
