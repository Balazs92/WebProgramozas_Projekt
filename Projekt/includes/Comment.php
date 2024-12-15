<?php

namespace includes;

class Comment {
    private $db;
    private $recipeId;
    private $userName;
    private $comment;

    public function __construct($db, $recipeId = null, $userName = null, $comment = null) {
        $this->db = $db;
        $this->recipeId = $recipeId;
        $this->userName = $userName;
        $this->comment = $comment;
    }

    public function save() {
        $stmt = $this->db->prepare("INSERT INTO comments (recipe_id, user_name, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $this->recipeId, $this->userName, $this->comment);
        $stmt->execute();
    }

    public static function getComments($recipeId, $db) {
        $stmt = $db->prepare(
            "SELECT c.comment, c.user_name, u.username AS user_display_name 
             FROM comments c 
             LEFT JOIN users u ON c.user_name = u.username
             WHERE c.recipe_id = ?"
        );
        $stmt->bind_param("i", $recipeId);
        $stmt->execute();
        $result = $stmt->get_result();

        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = [
                'comment' => $row['comment'],
                'user_name' => $row['user_name'],
                'user_display_name' => $row['user_display_name'] ?? $row['user_name']
            ];
        }
        return $comments;
    }
}
?>
