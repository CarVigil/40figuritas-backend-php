<?php

class Question {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getAll() {
        $result = $this->mysqli->query("SELECT * FROM questions");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->mysqli->prepare("SELECT * FROM questions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
        $stmt->close();
        return $question;
    }

    public function getByUserId($userId) {
        // Convertir userId a integer para compatibilidad con la BD
        $userId = (int) $userId;
        $stmt = $this->mysqli->prepare("SELECT * FROM questions WHERE userId = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $questions;
    }

    public function create($questionNumber, $userId, $cardAssigned) {
        $stmt = $this->mysqli->prepare("INSERT INTO questions (questionNumber, userId, cardAssigned) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $questionNumber, $userId, $cardAssigned);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function update($id, $data) {
        $fields = [];
        $types = "";
        $values = [];

        if (isset($data['questionNumber'])) {
            $fields[] = "questionNumber = ?";
            $types .= "i";
            $values[] = $data['questionNumber'];
        }

        if (isset($data['cardAssigned'])) {
            $fields[] = "cardAssigned = ?";
            $types .= "i";
            $values[] = $data['cardAssigned'];
        }

        if (isset($data['userId'])) {
            $fields[] = "userId = ?";
            $types .= "s";
            $values[] = $data['userId'];
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $types .= "i";

        $sql = "UPDATE questions SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    public function deleteById($id) {
        $stmt = $this->mysqli->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function deleteByUserId($userId) {
        // Convertir userId a integer para compatibilidad con la BD
        $userId = (int) $userId;
        $stmt = $this->mysqli->prepare("DELETE FROM questions WHERE userId = ?");
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}

?>
