<?php
session_start();

require_once '../includes/Database.php';
require_once '../includes/User.php';

use includes\Database;
use includes\User;

// Üzenetek megjelenítése
function displayMessages() {
    if (isset($_SESSION['error'])) {
        echo "<p style='color:red; text-align:center;'>" . $_SESSION['error'] . "</p>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<p style='color:green; text-align:center;'>" . $_SESSION['success'] . "</p>";
        unset($_SESSION['success']);
    }
}

$username = isset($_COOKIE['username']) ? $_COOKIE['username'] : '';
$password = isset($_COOKIE['password']) ? $_COOKIE['password'] : '';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés és Regisztráció</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
<div class="container">
    <?php displayMessages(); ?>

    <h2>Bejelentkezés</h2>
    <form action="login.php" method="POST">
        <input type="hidden" name="action" value="login">
        <input type="text" name="username" placeholder="Felhasználónév" value="<?php echo $username; ?>" required>
        <input type="password" name="password" placeholder="Jelszó" value="<?php echo $password; ?>" required>
        <label>
            <input type="checkbox" name="remember" <?php if ($username != '') echo 'checked'; ?>> Emlékezz rám
        </label>
        <button type="submit">Bejelentkezés</button>
    </form>

    <h2>Regisztráció</h2>
    <form action="login.php" method="POST">
        <input type="hidden" name="action" value="register">
        <input type="text" name="username" placeholder="Felhasználónév" required>
        <input type="password" name="password" placeholder="Jelszó" required>
        <input type="email" name="email" placeholder="Email cím" required>
        <button type="submit">Regisztráció</button>
    </form>
</div>
</body>
</html>
