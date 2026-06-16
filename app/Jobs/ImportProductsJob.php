<?php

namespace App\Jobs;

use App\Models\AgeStage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Tag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        private string $filePath,
        private string $importId,
    ) {}

    public function handle(): void
    {
        $this->updateCache(['status' => 'processing', 'step' => 'Leyendo archivo...']);

        $fullPath = Storage::disk('local')->path($this->filePath);
        $spreadsheet = IOFactory::load($fullPath);

        $result = [
            'status'   => 'done',
            'products' => ['created' => 0, 'updated' => 0],
            'variants' => ['created' => 0, 'updated' => 0],
            'errors'   => [],
        ];

        // --- Hoja Productos ---
        $this->updateCache(['status' => 'processing', 'step' => 'Procesando hoja Productos...']);
        $sheet = $spreadsheet->getSheetByName('Productos');
        if ($sheet) {
            $this->importProducts($sheet, $result);
        }

        // --- Hoja Variantes ---
        $this->updateCache(['status' => 'processing', 'step' => 'Procesando hoja Variantes...']);
        $sheet = $spreadsheet->getSheetByName('Variantes');
        if ($sheet) {
            $this->importVariants($sheet, $result);
        }

        $this->updateCache($result);
    }

    public function failed(\Throwable $e): void
    {
        $this->updateCache([
            'status'  => 'failed',
            'message' => $e->getMessage(),
        ]);
    }

    // -------------------------------------------------------------------------

    private function importProducts(Worksheet $sheet, array &$result): void
    {
        $headers  = $this->getHeaders($sheet);
        $allRows  = $this->getDataRows($sheet, $headers, startRow: 3);
        $chunks   = array_chunk($allRows, 50);

        $rowNumber = 2; // header=1, instruction row=2, data starts=3
        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                $rowNumber++;
                $sku = trim($row['sku'] ?? '');

                if ($this->isInstructionRow($sku)) continue;
                if (empty($sku)) continue;

                $this->processProductRow($row, $rowNumber, $result);
            }
        }
    }

    private function processProductRow(array $row, int $rowNum, array &$result): void
    {
        $sku    = trim($row['sku'] ?? '');
        $nombre = trim($row['nombre'] ?? '');
        $precio = $row['precio'] ?? null;

        if (empty($nombre)) {
            $result['errors'][] = ['hoja' => 'Productos', 'fila' => $rowNum, 'campo' => 'nombre', 'error' => 'El nombre es obligatorio'];
            return;
        }
        if (!is_numeric($precio) || (int) $precio <= 0) {
            $result['errors'][] = ['hoja' => 'Productos', 'fila' => $rowNum, 'campo' => 'precio', 'error' => 'El precio debe ser un número positivo'];
            return;
        }

        // Categoría
        $categoryId = null;
        $cat = trim($row['categoria'] ?? '');
        if ($cat) {
            $category = Category::where('name', $cat)->first();
            if ($category) {
                $categoryId = $category->id;
            } else {
                $result['errors'][] = ['hoja' => 'Productos', 'fila' => $rowNum, 'campo' => 'categoria', 'error' => "Categoría '{$cat}' no encontrada — importado sin categoría"];
            }
        }

        // Marca
        $brandId = null;
        $marca = trim($row['marca'] ?? '');
        if ($marca) {
            $brand   = Brand::firstOrCreate(['name' => $marca], ['slug' => Str::slug($marca), 'is_active' => true]);
            $brandId = $brand->id;
        }

        $statusMap   = ['activo' => 'active', 'borrador' => 'draft', 'agotado' => 'out_of_stock'];
        $status      = $statusMap[strtolower(trim($row['estado'] ?? 'borrador'))] ?? 'draft';
        $hasVariants = strtolower(trim($row['tiene_variantes'] ?? 'no')) === 'si';
        $stock       = (!$hasVariants && is_numeric($row['stock'] ?? null)) ? (int) $row['stock'] : null;

        $existing = Product::where('sku', $sku)->first();

        if ($existing) {
            $existing->update([
                'name'             => $nombre,
                'description'      => $row['descripcion'] ?? '',
                'brand_id'         => $brandId,
                'category_id'      => $categoryId,
                'price'            => (int) $precio,
                'compare_at_price' => is_numeric($row['precio_oferta'] ?? null) ? (int) $row['precio_oferta'] : null,
                'has_variants'     => $hasVariants,
                'stock'            => $stock,
                'status'           => $status,
                'weight_grams'     => is_numeric($row['peso_gramos'] ?? null) ? (int) $row['peso_gramos'] : null,
            ]);
            $product = $existing;
            $result['products']['updated']++;
        } else {
            $slug    = $this->uniqueSlug($nombre);
            $product = Product::create([
                'sku'              => $sku,
                'name'             => $nombre,
                'slug'             => $slug,
                'description'      => $row['descripcion'] ?? '',
                'brand_id'         => $brandId,
                'category_id'      => $categoryId,
                'price'            => (int) $precio,
                'compare_at_price' => is_numeric($row['precio_oferta'] ?? null) ? (int) $row['precio_oferta'] : null,
                'has_variants'     => $hasVariants,
                'stock'            => $stock,
                'status'           => $status,
                'weight_grams'     => is_numeric($row['peso_gramos'] ?? null) ? (int) $row['peso_gramos'] : null,
            ]);
            $result['products']['created']++;
        }

        // Etapas
        $etapas = trim($row['etapas'] ?? '');
        if ($etapas) {
            $slugs = array_filter(array_map('trim', explode(',', $etapas)));
            $ids   = AgeStage::whereIn('slug', $slugs)->pluck('id');
            $product->ageStages()->sync($ids);
        }

        // Tags
        $etiquetas = trim($row['etiquetas'] ?? '');
        if ($etiquetas) {
            $names = array_filter(array_map('trim', explode(',', $etiquetas)));
            $ids   = collect($names)->map(fn($n) => Tag::firstOrCreate(['name' => $n], ['slug' => Str::slug($n)])->id)->toArray();
            $product->tags()->sync($ids);
        }

        // Imágenes — no error si no existen aún
        $imagenes = trim($row['imagenes'] ?? '');
        if ($imagenes) {
            $files = array_filter(array_map('trim', explode(',', $imagenes)));
            foreach ($files as $i => $filename) {
                ProductImage::firstOrCreate(
                    ['product_id' => $product->id, 'path' => "products/{$filename}"],
                    ['sort_order' => $i, 'alt_text' => $nombre],
                );
            }
        }
    }

    // -------------------------------------------------------------------------

    private function importVariants(Worksheet $sheet, array &$result): void
    {
        $headers  = $this->getHeaders($sheet);
        $allRows  = $this->getDataRows($sheet, $headers, startRow: 3);
        $chunks   = array_chunk($allRows, 50);

        $rowNumber = 2;
        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                $rowNumber++;
                $skuVariante = trim($row['sku_variante'] ?? '');

                if ($this->isInstructionRow($skuVariante)) continue;
                if (empty($skuVariante)) continue;

                $this->processVariantRow($row, $rowNumber, $result);
            }
        }
    }

    private function processVariantRow(array $row, int $rowNum, array &$result): void
    {
        $skuProducto = trim($row['sku_producto'] ?? '');
        $skuVariante = trim($row['sku_variante'] ?? '');
        $stock       = $row['stock'] ?? null;

        if (empty($skuProducto)) {
            $result['errors'][] = ['hoja' => 'Variantes', 'fila' => $rowNum, 'campo' => 'sku_producto', 'error' => 'El SKU del producto es obligatorio'];
            return;
        }
        if (!is_numeric($stock)) {
            $result['errors'][] = ['hoja' => 'Variantes', 'fila' => $rowNum, 'campo' => 'stock', 'error' => 'El stock debe ser un número'];
            return;
        }

        $product = Product::where('sku', $skuProducto)->first();
        if (!$product) {
            $result['errors'][] = ['hoja' => 'Variantes', 'fila' => $rowNum, 'campo' => 'sku_producto', 'error' => "Producto con SKU '{$skuProducto}' no encontrado"];
            return;
        }

        $existing = ProductVariant::where('sku', $skuVariante)->first();

        if ($existing) {
            $existing->update([
                'size'           => trim($row['talla'] ?? '') ?: null,
                'color'          => trim($row['color'] ?? '') ?: null,
                'stock'          => (int) $stock,
                'price_override' => is_numeric($row['precio_diferente'] ?? null) ? (int) $row['precio_diferente'] : null,
            ]);
            $result['variants']['updated']++;
        } else {
            ProductVariant::create([
                'product_id'     => $product->id,
                'sku'            => $skuVariante,
                'size'           => trim($row['talla'] ?? '') ?: null,
                'color'          => trim($row['color'] ?? '') ?: null,
                'stock'          => (int) $stock,
                'price_override' => is_numeric($row['precio_diferente'] ?? null) ? (int) $row['precio_diferente'] : null,
            ]);
            $result['variants']['created']++;
        }
    }

    // -------------------------------------------------------------------------

    private function getHeaders(Worksheet $sheet): array
    {
        $headers = [];
        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $v = $cell->getValue();
                if ($v !== null && $v !== '') $headers[] = (string) $v;
            }
        }
        return $headers;
    }

    private function getDataRows(Worksheet $sheet, array $headers, int $startRow = 3): array
    {
        $rows = [];
        foreach ($sheet->getRowIterator($startRow) as $row) {
            $rowData  = [];
            $hasValue = false;
            $colIndex = 0;
            $cellIter = $row->getCellIterator();
            $cellIter->setIterateOnlyExistingCells(false);
            foreach ($cellIter as $cell) {
                if ($colIndex >= count($headers)) break;
                $v                         = $cell->getValue();
                $rowData[$headers[$colIndex]] = $v;
                if ($v !== null && $v !== '') $hasValue = true;
                $colIndex++;
            }
            if ($hasValue) $rows[] = $rowData;
        }
        return $rows;
    }

    private function isInstructionRow(string $value): bool
    {
        $lower = strtolower($value);
        return str_contains($lower, 'ej:') || str_contains($lower, 'único') || str_contains($lower, 'debe existir');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function updateCache(array $data): void
    {
        Cache::put("import:{$this->importId}", $data, now()->addHours(2));
    }
}
