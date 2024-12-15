<?php

namespace includes;

require_once 'Database.php';

class User
{
    private $db;

    public function __construct()
    {
        // Adatbázis kapcsolat létrehozása
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Regisztráció
    public function register($username, $password, $email)
    {
        // Ellenőrzés, hogy létezik-e már a felhasználó
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return "A felhasználónév már létezik!";
        }

        // Jelszó titkosítása
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Felhasználó hozzáadása az adatbázishoz
        $stmt = $this->db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $email);
        if ($stmt->execute()) {
            return true;
        } else {
            return "Hiba történt a regisztráció során.";
        }
    }

    // Bejelentkezés
    public function login($username, $password)
    {
        // Felhasználó keresése
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return "A felhasználó nem található.";
        }

        $user = $result->fetch_assoc();

        // Jelszó ellenőrzése
        if (password_verify($password, $user['password'])) {
            // Sikeres bejelentkezés
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            return true;
        } else {
            return "Hibás jelszó.";
        }
    }

    // Kijelentkezés
    public function logout()
    {
        session_unset();
        session_destroy();
    }

    // Ellenőrzi, hogy a felhasználó be van-e jelentkezve
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    // Visszaadja a bejelentkezett felhasználó adatait
    public function getUserData()
    {
        if ($this->isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email']
            ];
        } else {
            return null;
        }
    }
}
?>
