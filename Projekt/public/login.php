<?php
session_start();

require_once '../includes/Database.php';
require_once '../includes/User.php';

use includes\Database;
use includes\User;

// Adatbázis kapcsolat létrehozása
$db = new Database();
$conn = $db->getConnection();

// User osztály példányosítása
$user = new User($conn);

// Ha létezik a "username" cookie, automatikusan belépteti a felhasználót
if (isset($_COOKIE['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
    if (isset($_COOKIE['password'])) {
        $_SESSION['password'] = $_COOKIE['password'];
    }
    header("Location: home.php");
    exit();
}

// Bejelentkezési és regisztrációs akció
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $remember = isset($_POST['remember']); // Emlékezz rám opció

        // Bejelentkezés
        $login_result = $user->login($username, $password);

        if ($login_result === true) {
            // Sikeres bejelentkezés
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $_SESSION['user_id']; // Felhasználói azonosító

            // Ha "Emlékezz rám" be van pipálva, beállít egy cookie-t
            if ($remember) {
                setcookie("username", $username, time() + (86400 * 30), "/"); // 30 napos cookie
                setcookie("password", $password, time() + (86400 * 30), "/"); // 30 napos cookie
                $_SESSION['remember'] = true;
            } else {
                // Ha nincs kipipálva, töröljük a korábbi cookie-kat
                setcookie("username", "", time() - 3600, "/");
                setcookie("password", "", time() - 3600, "/");
                unset($_SESSION['remember']);
            }

            // Irányítsuk a felhasználót a kezdőlapra vagy a korábban mentett URL-re
            if (isset($_SESSION['redirect_to'])) {
                $redirect_url = $_SESSION['redirect_to'];
                unset($_SESSION['redirect_to']);
                header("Location: $redirect_url");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            $_SESSION['error'] = $login_result;
        }
    } elseif ($action == 'register') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Regisztráció
        $register_result = $user->register($username, $password, $email);

        if ($register_result === true) {
            $_SESSION['success'] = "Sikeres regisztráció!";
        } else {
            $_SESSION['error'] = $register_result;
        }

        header("Location: index.php");
        exit();
    }
}
?>
