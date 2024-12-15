<?php
session_start();

require_once '../includes/Database.php';
require_once '../includes/Comment.php';

use includes\Database;
use includes\Comment;

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $commentText = $_POST['comment'];
    $recipeId = $_POST['recipe_id'];
    $userName = $_SESSION['username'];

    if (!empty($commentText)) {
        $comment = new Comment($conn, $recipeId, $userName, $commentText);
        $comment->save();
    }
}

header("Location: recipe.php?id=" . $_POST['recipe_id']);
exit();
?>
