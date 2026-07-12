<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function billingPayload(): array
    {
        return [
            'country' => 'Indonesia',
            'billing_address' => 'Jl. Mawar No. 1',
            'city' => 'Bandung',
            'state' => 'Jawa Barat',
            'zipcode' => '40123',
            'phone' => '081234567890',
        ];
    }

    public function test_guest_cannot_create_an_order(): void
    {
        $response = $this->postJson('/api/v1/orders', ['items' => []]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_an_order_and_stock_is_decremented(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50, 'quantity' => 10]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            'billing' => $this->billingPayload(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', 150);

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 150]);
        $this->assertEquals(7, $product->fresh()->quantity);
        $this->assertDatabaseHas('billing_details', ['user_id' => $user->id, 'city' => 'Bandung']);
    }

    public function test_order_creation_fails_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50, 'quantity' => 2]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'billing' => $this->billingPayload(),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('items');
        $this->assertEquals(2, $product->fresh()->quantity);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_user_only_sees_their_own_orders_in_the_index(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Order::factory()->count(2)->create(['user_id' => $user->id]);
        Order::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/orders');

        $response->assertStatus(200)->assertJsonPath('meta.total', 2);
    }

    public function test_a_user_cannot_view_another_users_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_an_admin_can_view_any_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $order->id);
    }
}
