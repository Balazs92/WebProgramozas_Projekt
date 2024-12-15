<?php

namespace includes;

class Rating {
    private $db;
    private $recipeId;
    private $userId;
    private $rating;

    public function __construct($db, $recipeId = null, $userId = null, $rating = null) {
        $this->db = $db;
        $this->recipeId = $recipeId;
        $this->userId = $userId;
        $this->rating = $rating;
    }

    public function save() {
        $stmt = $this->db->prepare("INSERT INTO ratings (recipe_id, user_id, rating) 
        VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating)");
        $stmt->bind_param("iii", $this->recipeId, $this->userId, $this->rating);
        $stmt->execute();
    }

    public static function getAverageRating($recipeId, $db) {
        $stmt = $db->prepare("SELECT AVG(rating) as average_rating FROM ratings WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipeId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['average_rating'];
    }
}
?>
