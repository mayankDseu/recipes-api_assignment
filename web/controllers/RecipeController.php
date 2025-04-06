<?php

require_once __DIR__ . '/../services/RecipeService.php';
require_once __DIR__ . '/../database/connection.php';

class RecipeController {
    private $service;

    public function __construct() {
        global $pdo;
        $this->service = new RecipeService($pdo);
    }

    private function isAuthorized() {

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)/', $authHeader, $matches)) {
            return $matches[1] === AUTH_TOKEN;
        }
        return false;
    }

    public function handleRequest($method, $url, $input) {
        header('Content-Type: application/json');

        if ($url[0] === 'recipes') {
            if ($method === 'GET' && count($url) === 1) {
                echo json_encode($this->service->getAllRecipes());

            } elseif ($method === 'GET' && isset($url[1]) && is_numeric($url[1])) {
                echo json_encode($this->service->getRecipeById($url[1]));
            } elseif ($method === 'GET' && count($url) === 2 && $url[1] === 'search' && isset($_GET['query'])) {
                echo json_encode($this->service->searchRecipes($_GET['query']));
            
            } elseif ($method === 'POST' && count($url) === 1) {
                if (!$this->isAuthorized()) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Unauthorized']);
                    return;
                }
                
            
                echo json_encode(['success' => $this->service->createRecipe($input)]);
            
            } elseif ($method === 'PUT' && isset($url[1])) {
                if (!$this->isAuthorized()) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Unauthorized']);
                    return;
                }
                echo json_encode(['success' => $this->service->updateRecipe($url[1], $input)]);

            } elseif ($method === 'DELETE' && isset($url[1])) {
                if (!$this->isAuthorized()) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Unauthorized']);
                    return;
                }
                echo json_encode(['success' => $this->service->deleteRecipe($url[1])]);

            } elseif ($method === 'POST' && isset($url[1]) && isset($url[2]) && $url[2] === 'rating') {
                echo json_encode(['success' => $this->service->rateRecipe($url[1], $input)]);

            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid request']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
    }
}
