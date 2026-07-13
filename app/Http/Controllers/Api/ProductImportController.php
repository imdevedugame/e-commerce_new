<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductImportController extends Controller
{
    private const REQUIRED_COLUMNS = ['name', 'sku', 'price', 'quantity'];

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = array_map(fn ($col) => strtolower(trim($col)), fgetcsv($handle));

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);
        if (! empty($missing)) {
            fclose($handle);

            return response()->json([
                'message' => 'CSV header is missing required column(s): '.implode(', ', $missing),
            ], 422);
        }

        $imported = 0;
        $updated = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'sku' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0'],
                'quantity' => ['required', 'integer', 'min:0'],
                'brief_description' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
                'old_price' => ['nullable', 'numeric', 'min:0'],
                'stock_status' => ['nullable', 'in:instock,outstock'],
                'image' => ['nullable', 'string'],
                'categories' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                $errors[] = ['row' => $rowNumber, 'errors' => $validator->errors()->all()];

                continue;
            }

            $valid = $validator->validated();

            DB::transaction(function () use ($valid, &$imported, &$updated) {
                $existed = Product::where('SKU', $valid['sku'])->exists();

                $product = Product::updateOrCreate(
                    ['SKU' => $valid['sku']],
                    [
                        'name' => $valid['name'],
                        'brief_description' => $valid['brief_description'] ?? '',
                        'description' => $valid['description'] ?? '',
                        'price' => $valid['price'],
                        'old_price' => $valid['old_price'] ?? $valid['price'],
                        'stock_status' => $valid['stock_status'] ?? 'instock',
                        'quantity' => $valid['quantity'],
                        'image' => $valid['image'] ?? '',
                        'images' => json_encode([]),
                    ]
                );

                $existed ? $updated++ : $imported++;

                if (! empty($valid['categories'])) {
                    $slugs = array_filter(array_map('trim', explode(',', $valid['categories'])));
                    $categoryIds = Category::whereIn('slug', $slugs)->pluck('id');
                    $product->categories()->syncWithoutDetaching($categoryIds);
                }
            });
        }

        fclose($handle);

        return response()->json([
            'message' => 'Import completed.',
            'summary' => [
                'created' => $imported,
                'updated' => $updated,
                'failed' => count($errors),
            ],
            'errors' => $errors,
        ], 200);
    }
}
