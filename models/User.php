<?php

class User {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getAll() {
        $result = $this->mysqli->query("SELECT id, fullname, email FROM users");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->mysqli->prepare("SELECT id, fullname, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public function getByEmail($email) {
        $stmt = $this->mysqli->prepare("SELECT id, fullname, email, pass FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public function emailExists($email) {
        $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function create($fullname, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $this->mysqli->prepare("INSERT INTO users (fullname, email, pass) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $email, $hashedPassword);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    public function update($id, $data) {
        $fields = [];
        $types = "";
        $values = [];

        if (isset($data['fullname'])) {
            $fields[] = "fullname = ?";
            $types .= "s";
            $values[] = $data['fullname'];
        }

        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $types .= "s";
            $values[] = $data['email'];
        }

        if (isset($data['pass'])) {
            $fields[] = "pass = ?";
            $types .= "s";
            $values[] = password_hash($data['pass'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $types .= "i";

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    public function delete($id) {
        $stmt = $this->mysqli->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }
}

?>
