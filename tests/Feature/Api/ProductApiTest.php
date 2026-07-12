<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_products_paginated(): void
    {
        Product::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/products?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_it_filters_products_by_category(): void
    {
        $category = Category::factory()->create(['slug' => 'flowers']);
        $matching = Product::factory()->create();
        $matching->categories()->attach($category);

        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products?category=flowers');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id);
    }

    public function test_it_searches_products_by_name(): void
    {
        $target = Product::factory()->create(['name' => 'Red Rose Bouquet']);
        Product::factory()->create(['name' => 'Blue Vase']);

        $response = $this->getJson('/api/v1/products?search=Rose');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $target->id);
    }

    public function test_it_filters_products_by_price_range(): void
    {
        $cheap = Product::factory()->create(['price' => 10]);
        Product::factory()->create(['price' => 500]);

        $response = $this->getJson('/api/v1/products?min_price=5&max_price=50');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $cheap->id);
    }

    public function test_it_sorts_products_by_price_ascending(): void
    {
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 20]);

        $response = $this->getJson('/api/v1/products?sort=price_asc');

        $response->assertStatus(200);
        $prices = collect($response->json('data'))->pluck('price')->all();
        $this->assertEquals([20.0, 100.0], $prices);
    }

    public function test_it_shows_a_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $product->id);
    }

    public function test_it_returns_404_for_missing_product(): void
    {
        $response = $this->getJson('/api/v1/products/999999');

        $response->assertStatus(404)->assertJson(['message' => 'Resource not found.']);
    }
}
