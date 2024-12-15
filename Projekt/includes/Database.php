<?php

namespace includes;

class Database {
    private $conn;

    public function __construct($host = 'localhost', $username = 'root', $password = '', $dbname = 'mydb') {
        $this->conn = new \mysqli($host, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Kapcsolódási hiba: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        if ($params) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function close() {
        $this->conn->close();
    }
}
?>
