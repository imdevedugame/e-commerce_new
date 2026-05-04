<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Kategori Bunga
        $categoriesData = [
            ['name' => 'Mawar', 'slug' => 'mawar'],
            ['name' => 'Tulip', 'slug' => 'tulip'],
            ['name' => 'Anggrek', 'slug' => 'anggrek'],
            ['name' => 'Bunga Matahari', 'slug' => 'bunga-matahari'],
            ['name' => 'Lily', 'slug' => 'lily'],
        ];

        $categories = collect();
        foreach ($categoriesData as $cat) {
            $categories->push(Category::create($cat));
        }

        // 2. Buat Produk Bunga
        $productsData = [
            [
                'name' => 'Buket Mawar Merah',
                'brief_description' => 'Buket mawar merah klasik yang elegan.',
                'description' => 'Sampaikan perasaan Anda dengan buket mawar merah segar ini. Cocok untuk hadiah ulang tahun, hari jadian, atau sekadar kejutan manis.',
                'price' => 150000,
                'old_price' => 200000,
                'SKU' => 'MWR-001',
                'stock_status' => 'instock',
                'quantity' => 20,
                'image' => 'images/products/main_image/buket-bunga-bangil-12-2.jpg',
                'images' => '[]',
                'category_slug' => 'mawar'
            ],
            [
                'name' => 'Tulip Spesial',
                'brief_description' => 'Tulip elegan yang melambangkan ketulusan.',
                'description' => 'Tulip impor, ditata rapi. Pilihan ideal untuk ucapan maaf atau penghargaan.',
                'price' => 250000,
                'old_price' => 300000,
                'SKU' => 'TLP-001',
                'stock_status' => 'instock',
                'quantity' => 15,
                'image' => 'images/products/main_image/liliane_product_midi_900x.jpg.webp',
                'images' => '[]',
                'category_slug' => 'tulip'
            ],
            [
                'name' => 'Pohon Anggrek Bulan',
                'brief_description' => 'Anggrek bulan ungu dalam pot keramik.',
                'description' => 'Hiasan ruangan yang bisa bertahan lama. Anggrek bulan premium ini sangat disukai sebagai hadiah grand opening atau dekorasi koleksi pribadi.',
                'price' => 450000,
                'old_price' => 500000,
                'SKU' => 'ANG-001',
                'stock_status' => 'instock',
                'quantity' => 10,
                'image' => 'images/products/main_image/bunga_anggrek.jpeg',
                'images' => '[]',
                'category_slug' => 'anggrek'
            ],
            [
                'name' => 'Buket Bunga Matahari',
                'brief_description' => 'Buket matahari ceria untuk mencerahkan hari.',
                'description' => 'Terdiri dari 3-5 tangkai bunga matahari besar yang cerah, dipadukan dengan bunga pengisi. Sempurna untuk hadiah wisuda atau ucapan selamat.',
                'price' => 120000,
                'old_price' => 150000,
                'SKU' => 'BMT-001',
                'stock_status' => 'instock',
                'quantity' => 25,
                'image' => 'images/products/main_image/bunga_matahari_buket.jpg.webp',
                'images' => '[]',
                'category_slug' => 'bunga-matahari'
            ],
            [
                'name' => 'Buket Lily Eksklusif',
                'brief_description' => 'Wangi khas lily yang memikat hati.',
                'description' => 'Buket lily premium yang mekar sempurna. Tampilannya sangat mewah, berkelas, dan wanginya dapat memenuhi udara ruangan.',
                'price' => 300000,
                'old_price' => 350000,
                'SKU' => 'LLY-001',
                'stock_status' => 'instock',
                'quantity' => 8,
                'image' => 'images/products/main_image/bunga_lily.jpeg',
                'images' => '[]',
                'category_slug' => 'lily'
            ],
        ];

        foreach ($productsData as $data) {
            $catSlug = $data['category_slug'];
            unset($data['category_slug']); // hapus sebelum insert ke tbl produk

            $product = Product::create($data);

            // Hubungkan produk dengan kategorinya
            $category = $categories->where('slug', $catSlug)->first();
            if ($category) {
                $product->categories()->attach($category->id);
            }
        }

        // 3. Buat Admin User
        User::factory()->create([
            'name' => 'yusuf',
            'email' => 'yusuf@isawi.com',
            'password' => bcrypt('password'), // Sebaiknya dienkripsi pakai bcrypt agar bisa login
            'is_admin' => true
        ]);
    }
}
