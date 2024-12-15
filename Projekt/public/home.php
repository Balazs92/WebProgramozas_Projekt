<?php
session_start();
require_once '../includes/Database.php';
require_once '../includes/Recipe.php';

use includes\Database;
use includes\Recipe;

// Ellenőrzés: Bejelentkezett-e a felhasználó
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Kapcsolódás az adatbázishoz
$db = new Database('localhost', 'root', '', 'mydb');

// Kategóriák lekérdezése a szűrőhöz
$categories_result = $db->query("SELECT id, name FROM categories");

// Recept szűrés kategória szerint (ha van)
$category_filter = null;
if (isset($_GET['category']) && $_GET['category'] != "") {
    $category_filter = intval($_GET['category']);
}

// Receptek lekérdezése a Recipe osztály segítségével
if ($category_filter) {
    $recipes_result = Recipe::getAllRecipes($db->getConnection(), $category_filter);
} else {
    $recipes_result = Recipe::getAllRecipes($db->getConnection());
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kezdőlap</title>
    <link rel="stylesheet" href="../css/home.css">
</head>
<body>
<nav>
    <div class="nav-wrapper">
        <div class="logo">
            <a href="home.php">Recept Oldal</a>
        </div>
        <div class="menu">
            <ul>
                <li><a href="newrecipe.php">Új Recept Feltöltése</a></li>
                <li><a href="logout.php">Kijelentkezés</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="content">
    <div class="filter">
        <form method="GET" action="home.php">
            <select name="category">
                <option value="">Minden kategória</option>
                <?php while ($row = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $row['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Szűrés</button>
        </form>
    </div>
    <div class="recipes">
        <?php if (count($recipes_result) > 0): ?>
            <?php foreach ($recipes_result as $recipe): ?>
                <div class="recipe">
                    <?php if ($recipe['path']): ?>
                        <img src="../<?= htmlspecialchars($recipe['path']) ?>" alt="<?= htmlspecialchars($recipe['title']) ?>">
                    <?php endif; ?>
                    <h3><a href="recipe.php?id=<?= $recipe['id'] ?>"><?= htmlspecialchars($recipe['title']) ?></a></h3>
                    <p class="category">Kategória: <?= htmlspecialchars($recipe['category']) ?></p>
                    <p class="rating">Értékelés: <?= round($recipe['average_rating'], 1) ?: 'Nincs értékelés' ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nincsenek receptek a megadott kategóriában.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

<?php
$db->close();
?>
