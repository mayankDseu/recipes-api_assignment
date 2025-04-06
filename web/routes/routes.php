<?php

require_once __DIR__ . '/../controllers/RecipeController.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$url = explode('/', parse_url($requestUri, PHP_URL_PATH));
$url = array_values(array_filter($url)); 

$controller = new RecipeController();

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$controller->handleRequest($method, $url, $input);

?>
