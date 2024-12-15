<?php
session_start();

require_once '../includes/Database.php';
require_once '../includes/Recipe.php';

use includes\Database;
use includes\Recipe;

// Ellenőrzés: Bejelentkezett-e a felhasználó
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Ha nincs bejelentkezve, irányítsa a bejelentkezési oldalra
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new Database('localhost', 'root', '', 'mydb');
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category'];

    // Kép feltöltési könyvtár beállítása a Projekt mappán belüli images mappára
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/Projekt/images/";

    if (!is_dir($upload_dir)) {
        $_SESSION['error'] = "A feltöltési könyvtár nem létezik.";
    } else {
        $unique_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $path = $upload_dir . $unique_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);

        if (empty($title) || empty($description) || empty($category_id)) {
            $_SESSION['error'] = "Minden mezőt ki kell tölteni.";
        } elseif (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Csak JPG, PNG és GIF formátumú képek engedélyezettek.";
        } else {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO recipes 
                (title, description, users_id, categories_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssii", $title, $description, $user_id, $category_id);
                $stmt->execute();
                $recipe_id = $stmt->insert_id;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
                    $stmt_image = $conn->prepare("INSERT INTO images (path, recipes_id) VALUES (?, ?)");
                    // Csak a relatív útvonalat tároljuk az adatbázisban
                    $relative_path = "Projekt/images/" . $unique_name;
                    $stmt_image->bind_param("si", $relative_path, $recipe_id);
                    $stmt_image->execute();
                    $stmt_image->close();
                } else {
                    throw new Exception("Hiba történt a kép feltöltése során.");
                }

                $conn->commit();
                $_SESSION['success'] = "Recept és kép sikeresen feltöltve!";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error'] = "Hiba történt: " . $e->getMessage();
            }
        }
    }
}

$categories_result = $db->query("SELECT id, name FROM categories");
$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új Recept Feltöltése</title>
    <link rel="stylesheet" href="../css/newrecipe.css">
</head>
<body>
<div class="container">
    <h2>Új Recept Feltöltése</h2>
    <?php
    if (isset($_SESSION['error'])) {
        echo "<p class='message error'>" . $_SESSION['error'] . "</p>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<p class='message success'>" . $_SESSION['success'] . "</p>";
        unset($_SESSION['success']);
    }
    ?>
    <form action="newrecipe.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Recept címe" required>
        <textarea name="description" placeholder="Leírás" required></textarea>
        <input type="file" name="image" accept="image/*" required>
        <select name="category" required>
            <option value="">Válassz kategóriát</option>
            <?php while ($row = $categories_result->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Recept Feltöltése</button>
    </form>
    <form action="home.php" method="get">
        <button type="submit">Vissza a Kezdőlapra</button>
    </form>
</div>
</body>
</html>
