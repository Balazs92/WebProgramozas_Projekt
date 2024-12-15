<?php
namespace includes;

class Recipe {
    private $id;
    private $title;
    private $description;
    private $category;
    private $averageRating;
    private $path;
    private $db;
    private $users_id;

    public function __construct($id, $title, $description, $category, $averageRating, $path, $db, $users_id) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->averageRating = $averageRating;
        $this->path = $path;
        $this->db = $db;
        $this->users_id = $users_id;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getAverageRating() {
        return $this->averageRating;
    }

    public function getPath() {
        return $this->path;
    }

    public function save() {
        $stmt = $this->db->prepare("INSERT INTO recipes (title, description, users_id, categories_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $this->title, $this->description, $this->users_id, $this->category);
        $stmt->execute();
        $this->id = $stmt->insert_id;
    }


    public static function findById($id, $db) {
        $stmt = $db->prepare("SELECT recipes.id, recipes.title, recipes.description, categories.name AS category, images.path, AVG(ratings.rating) AS average_rating 
        FROM recipes 
        LEFT JOIN categories ON recipes.categories_id = categories.id 
        LEFT JOIN ratings ON recipes.id = ratings.recipe_id 
        LEFT JOIN images ON recipes.id = images.recipes_id 
        WHERE recipes.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $recipeData = $stmt->get_result()->fetch_assoc();
        return new Recipe($id, $recipeData['title'], $recipeData['description'], $recipeData['category'], $recipeData['average_rating'], $recipeData['path'], $db, null);
    }

    public static function getAllRecipes($db, $category_id = null) {
        $query = "SELECT recipes.id, recipes.title, recipes.description, categories.name 
                AS category, images.path, AVG(ratings.rating) AS average_rating 
                  FROM recipes 
                  LEFT JOIN categories ON recipes.categories_id = categories.id 
                  LEFT JOIN ratings ON recipes.id = ratings.recipe_id 
                  LEFT JOIN images ON recipes.id = images.recipes_id";
        if ($category_id) {
            $query .= " WHERE categories.id = ?";
        }
        $query .= " GROUP BY recipes.id";

        $stmt = $db->prepare($query);
        if ($category_id) {
            $stmt->bind_param("i", $category_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $recipes = [];
        while ($row = $result->fetch_assoc()) {
            $recipes[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'category' => $row['category'],
                'path' => $row['path'],
                'average_rating' => $row['average_rating']
            ];
        }
        return $recipes;
    }

    public function addRating($user_name, $rating) {
        $stmt = $this->db->prepare("INSERT INTO ratings (recipe_id, user_name, rating) 
        VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating)");
        $stmt->bind_param("isi", $this->id, $user_name, $rating);
        $stmt->execute();
        $this->updateAverageRating();
    }

    public function addComment($user_name, $comment) {
        $stmt = $this->db->prepare("INSERT INTO comments (recipe_id, user_name, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $this->id, $user_name, $comment);
        $stmt->execute();
    }

    public static function getComments($recipe_id, $db) {
        $stmt = $db->prepare("SELECT user_name, comment FROM comments WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        return $comments;
    }

    public static function getRatings($recipe_id, $db) {
        $stmt = $db->prepare("SELECT user_id, rating FROM ratings WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ratings = [];
        while ($row = $result->fetch_assoc()) {
            $ratings[] = $row;
        }
        return $ratings;
    }

    private function updateAverageRating() {
        $stmt = $this->db->prepare("SELECT AVG(rating) as average_rating FROM ratings WHERE recipe_id = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $this->averageRating = $result['average_rating'];
    }
}
