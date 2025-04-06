<?php

require_once __DIR__ . '/../database/connection.php';

class RecipeService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllRecipes() {

        $defaultLimit = 10;
        $defaultPage = 1;
        $maxLimit = 50; 
    
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : $defaultLimit;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : $defaultPage;
    
        $limit = min(max($limit, 1), $maxLimit);
        $page = max($page, 1);
        $offset = ($page - 1) * $limit;
    
        $stmt = $this->pdo->prepare("
            SELECT *, 
                CASE 
                    WHEN ratings_count = 0 THEN 0 
                    ELSE ROUND(ratings_sum::decimal / ratings_count, 1) 
                END AS avg_rating
            FROM recipes
            LIMIT :limit OFFSET :offset
        ");
    
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRecipeById($id) {
        $stmt = $this->pdo->prepare("
            SELECT *, 
                CASE 
                    WHEN ratings_count = 0 THEN 0 
                    ELSE ROUND(ratings_sum::decimal / ratings_count, 1) 
                END AS avg_rating
            FROM recipes 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($recipe) {
            return $recipe; 
        } else {
            http_response_code(404);
            return ["message" => "Recipe not found"];
        }
    }
    
    public function searchRecipes($query) {
        $stmt = $this->pdo->prepare("
            SELECT *, 
                CASE 
                    WHEN ratings_count = 0 THEN 0 
                    ELSE ROUND(ratings_sum::decimal / ratings_count, 1) 
                END AS avg_rating
            FROM recipes 
            WHERE name ILIKE ?
        ");
        $stmt->execute(["%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function createRecipe($data) {
        if (!isset($data['vegetarian']) || !is_bool($data['vegetarian'])) {
            throw new Exception("Invalid 'vegetarian' value. Must be true or false.");
        }
    
        $stmt = $this->pdo->prepare("
            INSERT INTO recipes (name, prep_time, difficulty, vegetarian, ratings_count, ratings_sum)
            VALUES (?, ?, ?, ?, 0, 0)
        ");
    
        return $stmt->execute([
            $data['name'],
            $data['prep_time'],
            $data['difficulty'],
            $data['vegetarian'] ? 1 : 0  
        ]);
    }
    
    
    public function updateRecipe($id, $data) {
        $updateFields = [];
        $values = [];
    
        if (isset($data['name'])) {
            $updateFields[] = "name = ?";
            $values[] = $data['name'];
        }
        if (isset($data['prep_time'])) {
            $updateFields[] = "prep_time = ?";
            $values[] = $data['prep_time'];
        }
        if (isset($data['difficulty'])) {
            $updateFields[] = "difficulty = ?";
            $values[] = $data['difficulty'];
        }
        if (isset($data['vegetarian'])) {
            $updateFields[] = "vegetarian = ?";
            $values[] = $data['vegetarian'];
        }
    
        if (empty($updateFields)) {
            return false;
        }
    
        $values[] = $id;
    
        $sql = "UPDATE recipes SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    public function deleteRecipe($id) {
        $stmt = $this->pdo->prepare("DELETE FROM recipes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function rateRecipe($id, $data) {
        if (!isset($data['rating']) || !is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            http_response_code(400);
            echo json_encode(["message" => "Rating must be between 1 and 5"]);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE recipes 
                SET ratings_count = ratings_count + 1, 
                    ratings_sum = ratings_sum + ? 
                WHERE id = ?
                RETURNING ratings_count, ratings_sum
            ");
            $stmt->execute([$data['rating'], $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $avgRating = round($result['ratings_sum'] / $result['ratings_count'], 1);
                echo json_encode([
                    "message" => "Rating added successfully",
                    "new_avg_rating" => $avgRating
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Recipe not found"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Database error: " . $e->getMessage()]);
        }
    }
}

?>
