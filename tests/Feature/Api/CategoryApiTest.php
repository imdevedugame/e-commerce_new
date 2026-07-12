<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    public function test_it_shows_a_single_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)->assertJsonPath('data.slug', $category->slug);
    }

    public function test_it_returns_404_for_missing_category(): void
    {
        $response = $this->getJson('/api/v1/categories/999999');

        $response->assertStatus(404)->assertJson(['message' => 'Resource not found.']);
    }
}
