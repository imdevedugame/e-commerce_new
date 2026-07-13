<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductImportApiTest extends TestCase
{
    use RefreshDatabase;

    private function csv(string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('products.csv', $content);
    }

    public function test_guest_cannot_import(): void
    {
        $file = $this->csv("name,sku,price,quantity\nA,SKU-1,1000,5\n");

        $response = $this->postJson('/api/v1/products/import', ['file' => $file]);

        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_import(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $file = $this->csv("name,sku,price,quantity\nA,SKU-1,1000,5\n");

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/products/import', ['file' => $file]);

        $response->assertStatus(403);
    }

    public function test_admin_can_import_products_and_attach_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create(['slug' => 'mawar']);

        $csv = "name,brief_description,description,price,old_price,sku,stock_status,quantity,image,categories\n"
            ."Buket Anyelir,Deskripsi singkat,Deskripsi lengkap,95000,120000,ANY-001,instock,15,img.jpg,mawar\n";

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/products/import', ['file' => $this->csv($csv)]);

        $response->assertStatus(200)
            ->assertJsonPath('summary.created', 1)
            ->assertJsonPath('summary.failed', 0);

        $product = Product::where('SKU', 'ANY-001')->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->categories->contains($category));
    }

    public function test_import_upserts_existing_product_by_sku(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Product::factory()->create(['SKU' => 'ANY-001', 'name' => 'Nama Lama', 'quantity' => 3]);

        $csv = "name,sku,price,quantity\nNama Baru,ANY-001,99000,20\n";

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/products/import', ['file' => $this->csv($csv)]);

        $response->assertStatus(200)
            ->assertJsonPath('summary.created', 0)
            ->assertJsonPath('summary.updated', 1);

        $this->assertDatabaseHas('products', ['SKU' => 'ANY-001', 'name' => 'Nama Baru', 'quantity' => 20]);
        $this->assertEquals(1, Product::where('SKU', 'ANY-001')->count());
    }

    public function test_import_reports_invalid_rows_without_failing_whole_file(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $csv = "name,sku,price,quantity\n"
            ."Produk Valid,VALID-1,1000,5\n"
            .",INVALID-1,abc,-1\n";

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/products/import', ['file' => $this->csv($csv)]);

        $response->assertStatus(200)
            ->assertJsonPath('summary.created', 1)
            ->assertJsonPath('summary.failed', 1);

        $this->assertDatabaseHas('products', ['SKU' => 'VALID-1']);
        $this->assertDatabaseMissing('products', ['SKU' => 'INVALID-1']);
    }

    public function test_import_rejects_csv_missing_required_columns(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $csv = "name,sku\nA,SKU-1\n";

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/products/import', ['file' => $this->csv($csv)]);

        $response->assertStatus(422);
    }
}
