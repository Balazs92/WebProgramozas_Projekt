<?php
session_start();
require_once '../includes/Database.php';
require_once '../includes/Recipe.php';
require_once '../includes/Rating.php';
require_once '../includes/Comment.php';

use includes\Database;
use includes\Recipe;
use includes\Rating;
use includes\Comment;

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$recipe_id = intval($_GET['id']);
$db = new Database('localhost', 'root', '', 'mydb');
$connection = $db->getConnection();
$recipe = Recipe::findById($recipe_id, $connection);

// Értékelés kezelés
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating'])) {
    $recipe->addRating($_SESSION['user_id'], $_POST['rating']);
    header("Location: recipe.php?id=" . $recipe_id);
    exit();
}

// Hozzászólás kezelés
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $recipe->addComment($_SESSION['username'], $_POST['comment']);
    header("Location: recipe.php?id=" . $recipe_id);
    exit();
}

// Recept adatainak lekérdezése
$comments = Recipe::getComments($recipe_id, $connection);
$averageRating = $recipe->getAverageRating();

$db->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe->getTitle()) ?></title>
    <link rel="stylesheet" href="../css/recipe.css">
</head>
<body>
<nav>
    <div class="nav-wrapper">
        <div class="logo">
            <a href="home.php">Recept Oldal</a>
        </div>
        <div class="menu">
            <ul>
                <li><a href="home.php">Vissza a főoldalra</a></li>
                <li><a href="logout.php">Kijelentkezés</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="content">
    <div class="recipe-details">
        <h1><?= htmlspecialchars($recipe->getTitle()) ?></h1>
        <?php if ($recipe->getPath()): ?>
            <img src="../<?= htmlspecialchars($recipe->getPath()) ?>" alt="<?= htmlspecialchars($recipe->getTitle()) ?>">
        <?php endif; ?>
        <p><strong>Kategória:</strong> <?= htmlspecialchars($recipe->getCategory()) ?></p>
        <p><strong>Átlagos értékelés:</strong> <?= round($averageRating, 1) ?: 'Nincs értékelés' ?></p>
        <p><?= nl2br(htmlspecialchars($recipe->getDescription())) ?></p>
    </div>
    <div class="recipe-comments">
        <h2>Hozzászólások</h2>
        <form method="post">
            <textarea name="comment" required></textarea>
            <button type="submit">Hozzászólás</button>
        </form>
        <?php foreach ($comments as $cmt): ?>
            <div class="comment">
                <p><strong><?= htmlspecialchars($cmt['user_name']) ?>:</strong> <?= htmlspecialchars($cmt['comment']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="recipe-rating">
        <h2>Értékelés</h2>
        <form method="post">
            <input type="number" name="rating" min="1" max="5" required>
            <button type="submit">Értékelés</button>
        </form>
    </div>
</div>
</body>
</html>
