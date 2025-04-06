<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class RecipeTest extends TestCase
{
    private $client;
    private $baseUrl;
    private $authToken;

    protected function setUp(): void
    {
        $this->baseUrl = 'http://host.docker.internal:8080';
        $this->authToken = 'secret-token-123'; 

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false
        ]);
    }

    public function testGetAllRecipes()
    {
        $response = $this->client->get('/recipes');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody()->getContents());
    }
    public function testCreateRecipe()
    {
        $response = $this->client->post('/recipes', [
            'json' => [
                'name' => 'Test Recipe',
                'prep_time' => 30,
                'difficulty' => 2,
                'vegetarian' => true
            ],
            'headers' => ['Authorization' => "Bearer {$this->authToken}"]
        ]);
    
        $this->assertEquals(200, $response->getStatusCode()); 
        $data = json_decode($response->getBody()->getContents(), true);
        
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
    

    public function testUpdateRecipe()
    {
        $response = $this->client->put('/recipes/2', [
            'json' => ['name' => 'Updated Recipe'],
            'headers' => ['Authorization' => "Bearer {$this->authToken}"]
        ]);
        
        $this->assertContains($response->getStatusCode(), [200, 400, 404]); 
    
        $data = json_decode($response->getBody()->getContents(), true);
        
        if ($response->getStatusCode() === 200) {
            $this->assertTrue($data['success']);
        } else {
            $this->assertFalse($data['success']);
        }
    }
    
    public function testDeleteRecipe()
    {
        $response = $this->client->delete('/recipes/1', [
            'headers' => ['Authorization' => "Bearer {$this->authToken}"]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchRecipes()
    {
        $response = $this->client->get('/recipes/search?query=Test');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getBody()->getContents());
    }

    public function testRateRecipe()
    {
        $response = $this->client->post('/recipes/2/rating', [
            'json' => ['rating' => 5],
            'headers' => ['Authorization' => "Bearer {$this->authToken}"]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}


