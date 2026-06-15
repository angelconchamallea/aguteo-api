<?php

namespace Database\Seeders;

use App\Models\AgeStage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brands    = Brand::pluck('id', 'slug');
        $cats      = Category::pluck('id', 'slug');
        $stages    = AgeStage::pluck('id', 'slug');

        $agb  = $brands['aguteo-babys'];
        $amma = $brands['amma'];
        $hug  = $brands['huggies'];

        $this->seedRopa($agb, $amma, $cats, $stages);
        $this->seedAlimentacion($agb, $cats, $stages);
        $this->seedCuidado($agb, $hug, $cats, $stages);
    }

    private function seedRopa(int $agb, int $amma, $cats, $stages): void
    {
        $products = [
            [
                'sku'               => 'ROB-AGB-CONJ-001',
                'name'              => 'Conjunto ositos manga larga',
                'slug'              => 'conjunto-ositos-manga-larga',
                'short_description' => 'Conjunto de algodón peinado suave, ideal para los primeros meses.',
                'description'       => 'Conjunto de dos piezas (polera y pantalón) en algodón peinado 100%. Estampado de ositos con bordado en relieve. Cierre de broches en la entrepierna para cambios fáciles. Disponible en tallas desde recién nacido.',
                'brand_id'          => $agb,
                'category_id'       => $cats['conjuntos'],
                'price'             => 12990,
                'compare_at_price'  => 15990,
                'cost_price'        => 5500,
                'has_variants'      => true,
                'status'            => 'active',
                'featured'          => true,
                'weight_grams'      => 200,
                'stages'            => ['0-3m', '3-6m'],
                'variants'          => [
                    ['size' => 'RN',    'sku' => 'ROB-AGB-CONJ-001-RN',    'stock' => 8],
                    ['size' => '0-3m',  'sku' => 'ROB-AGB-CONJ-001-0-3M',  'stock' => 12],
                    ['size' => '3-6m',  'sku' => 'ROB-AGB-CONJ-001-3-6M',  'stock' => 10],
                    ['size' => '6-9m',  'sku' => 'ROB-AGB-CONJ-001-6-9M',  'stock' => 6],
                ],
            ],
            [
                'sku'               => 'ROB-AGB-CONJ-002',
                'name'              => 'Conjunto floral verano',
                'slug'              => 'conjunto-floral-verano',
                'short_description' => 'Conjunto liviano con estampado floral en tonos pastel para el verano.',
                'description'       => 'Conjunto de dos piezas (blusa y short) en tela de punto jersey liviano. Estampado floral en colores pastel. Elástico suave en cintura. Perfecto para los días de calor.',
                'brand_id'          => $agb,
                'category_id'       => $cats['conjuntos'],
                'price'             => 11990,
                'compare_at_price'  => null,
                'cost_price'        => 4800,
                'has_variants'      => true,
                'status'            => 'active',
                'featured'          => false,
                'weight_grams'      => 180,
                'stages'            => ['3-6m', '6-12m'],
                'variants'          => [
                    ['size' => '0-3m',  'sku' => 'ROB-AGB-CONJ-002-0-3M',  'stock' => 10],
                    ['size' => '3-6m',  'sku' => 'ROB-AGB-CONJ-002-3-6M',  'stock' => 14],
                    ['size' => '6-9m',  'sku' => 'ROB-AGB-CONJ-002-6-9M',  'stock' => 9],
                    ['size' => '9-12m', 'sku' => 'ROB-AGB-CONJ-002-9-12M', 'stock' => 5],
                ],
            ],
            [
                'sku'               => 'ROB-AMM-PIJ-001',
                'name'              => 'Pijama stars azul',
                'slug'              => 'pijama-stars-azul',
                'short_description' => 'Pijama abrigado con estrellas, perfecto para noches de invierno.',
                'description'       => 'Pijama de una pieza en polar suave. Estampado de estrellas en azul y blanco. Cremallera frontal con protector en el mentón. Puños con antideslizante en los pies para cuando empieza a gatear.',
                'brand_id'          => $amma,
                'category_id'       => $cats['pijamas'],
                'price'             => 9990,
                'compare_at_price'  => 12990,
                'cost_price'        => 4200,
                'has_variants'      => true,
                'status'            => 'active',
                'featured'          => true,
                'weight_grams'      => 220,
                'stages'            => ['0-3m', '3-6m', '6-12m'],
                'variants'          => [
                    ['size' => '0-3m',   'sku' => 'ROB-AMM-PIJ-001-0-3M',   'stock' => 8],
                    ['size' => '3-6m',   'sku' => 'ROB-AMM-PIJ-001-3-6M',   'stock' => 11],
                    ['size' => '6-9m',   'sku' => 'ROB-AMM-PIJ-001-6-9M',   'stock' => 9],
                    ['size' => '9-12m',  'sku' => 'ROB-AMM-PIJ-001-9-12M',  'stock' => 7],
                    ['size' => '12-18m', 'sku' => 'ROB-AMM-PIJ-001-12-18M', 'stock' => 4],
                ],
            ],
            [
                'sku'               => 'ROB-AGB-BDY-001',
                'name'              => 'Pack 3 bodies algodón manga corta',
                'slug'              => 'pack-3-bodies-algodon-manga-corta',
                'short_description' => 'Pack de 3 bodies de algodón 100% en colores neutros.',
                'description'       => 'Pack de 3 bodies de algodón peinado 100%. Colores: blanco, gris y beige. Cierre de broches en la entrepierna para cambios fáciles. Ideal para el uso diario, combinan con cualquier ropa.',
                'brand_id'          => $agb,
                'category_id'       => $cats['ropa-interior'],
                'price'             => 14990,
                'compare_at_price'  => null,
                'cost_price'        => 6000,
                'has_variants'      => true,
                'status'            => 'active',
                'featured'          => false,
                'weight_grams'      => 300,
                'stages'            => ['0-3m', '3-6m'],
                'variants'          => [
                    ['size' => 'RN',   'sku' => 'ROB-AGB-BDY-001-RN',   'stock' => 15],
                    ['size' => '0-3m', 'sku' => 'ROB-AGB-BDY-001-0-3M', 'stock' => 18],
                    ['size' => '3-6m', 'sku' => 'ROB-AGB-BDY-001-3-6M', 'stock' => 12],
                    ['size' => '6-9m', 'sku' => 'ROB-AGB-BDY-001-6-9M', 'stock' => 8],
                ],
            ],
        ];

        foreach ($products as $data) {
            $stagesSlugs = $data['stages'];
            $variantsData = $data['variants'];
            unset($data['stages'], $data['variants']);

            $product = Product::create($data);
            $product->ageStages()->attach(AgeStage::whereIn('slug', $stagesSlugs)->pluck('id'));

            foreach ($variantsData as $variant) {
                ProductVariant::create(array_merge($variant, ['product_id' => $product->id]));
            }
        }
    }

    private function seedAlimentacion(int $agb, $cats, $stages): void
    {
        $products = [
            [
                'sku'               => 'ALI-AGB-CMP-001',
                'name'              => 'Compota manzana y pera orgánica 113g',
                'slug'              => 'compota-manzana-pera-organica',
                'short_description' => 'Compota 100% orgánica sin azúcar añadida ni conservantes.',
                'description'       => 'Compota de manzana y pera elaborada con frutas orgánicas. Sin azúcar añadida, sin conservantes ni colorantes. Certificada orgánica. Apta desde los 4 meses. Formato exprimible fácil de usar.',
                'brand_id'          => $agb,
                'category_id'       => $cats['compotas'],
                'price'             => 1290,
                'compare_at_price'  => null,
                'cost_price'        => 490,
                'has_variants'      => false,
                'stock'             => 120,
                'status'            => 'active',
                'featured'          => false,
                'weight_grams'      => 130,
                'stages'            => ['3-6m', '6-12m', '12-24m'],
            ],
            [
                'sku'               => 'ALI-AGB-JUG-001',
                'name'              => 'Jugo natural de manzana sin azúcar 200ml',
                'slug'              => 'jugo-natural-manzana-sin-azucar',
                'short_description' => 'Jugo 100% natural de manzana, sin azúcar ni conservantes.',
                'description'       => 'Jugo natural de manzana elaborado con manzanas frescas seleccionadas. Sin azúcar añadida, sin conservantes ni colorantes. Pasteurizado para garantizar su seguridad. Apto desde los 6 meses.',
                'brand_id'          => $agb,
                'category_id'       => $cats['jugos'],
                'price'             => 890,
                'compare_at_price'  => null,
                'cost_price'        => 320,
                'has_variants'      => false,
                'stock'             => 90,
                'status'            => 'active',
                'featured'          => false,
                'weight_grams'      => 220,
                'stages'            => ['6-12m', '12-24m'],
            ],
            [
                'sku'               => 'ALI-AGB-ACC-001',
                'name'              => 'Cuchara de silicona con mango ergonómico',
                'slug'              => 'cuchara-silicona-mango-ergonomico',
                'short_description' => 'Cuchara suave de silicona, perfecta para las primeras papillas.',
                'description'       => 'Cuchara de silicona grado alimentario, libre de BPA. Punta suave que no daña las encías. Mango ergonómico fácil de sujetar por el bebé. Apta para lavavajillas. Colores surtidos (se envía uno al azar).',
                'brand_id'          => $agb,
                'category_id'       => $cats['accesorios-alimentacion'],
                'price'             => 4990,
                'compare_at_price'  => 6990,
                'cost_price'        => 1800,
                'has_variants'      => false,
                'stock'             => 60,
                'status'            => 'active',
                'featured'          => true,
                'weight_grams'      => 40,
                'stages'            => ['3-6m', '6-12m', '12-24m'],
            ],
            [
                'sku'               => 'ALI-AGB-ACC-002',
                'name'              => 'Babero impermeable con bolsillo atrapa-migas',
                'slug'              => 'babero-impermeable-bolsillo-atrapa-migas',
                'short_description' => 'Babero de silicona con bolsillo que atrapa lo que cae.',
                'description'       => 'Babero de silicona impermeable con bolsillo frontal que atrapa la comida que cae. Cierre de broche ajustable para diferentes tamaños de cuello. Fácil de limpiar con un trapo húmedo o lavavajillas. Colores: rosa, celeste, verde.',
                'brand_id'          => $agb,
                'category_id'       => $cats['accesorios-alimentacion'],
                'price'             => 3990,
                'compare_at_price'  => null,
                'cost_price'        => 1500,
                'has_variants'      => false,
                'stock'             => 45,
                'status'            => 'active',
                'featured'          => false,
                'weight_grams'      => 80,
                'stages'            => ['6-12m', '12-24m'],
            ],
        ];

        foreach ($products as $data) {
            $stagesSlugs = $data['stages'];
            unset($data['stages']);

            $product = Product::create($data);
            $product->ageStages()->attach(AgeStage::whereIn('slug', $stagesSlugs)->pluck('id'));
        }
    }

    private function seedCuidado(int $agb, int $hug, $cats, $stages): void
    {
        $products = [
            [
                'sku'               => 'CUI-HUG-PAN-001',
                'name'              => 'Pañales Huggies Recién Nacido x30',
                'slug'              => 'panales-huggies-recien-nacido-x30',
                'short_description' => 'Pañales ultraabsorbentes para recién nacido, suaves como algodón.',
                'description'       => 'Pañales Huggies Natural Care para recién nacidos. Capa interior suave como algodón, sistema ultraabsorbente que mantiene la piel seca hasta 12 horas. Indicador de humedad que cambia de color. Corte especial para el cordón umbilical. Pack x30 unidades.',
                'brand_id'          => $hug,
                'category_id'       => $cats['panales'],
                'price'             => 8990,
                'compare_at_price'  => 10990,
                'cost_price'        => 4200,
                'has_variants'      => false,
                'stock'             => 80,
                'status'            => 'active',
                'featured'          => true,
                'weight_grams'      => 800,
                'stages'            => ['0-3m'],
            ],
            [
                'sku'               => 'CUI-HUG-TOA-001',
                'name'              => 'Toallitas húmedas sin fragancia x80',
                'slug'              => 'toallitas-humedas-sin-fragancia-x80',
                'short_description' => 'Toallitas ultra suaves sin fragancia, ideales para piel sensible.',
                'description'       => 'Toallitas húmedas Huggies Pure sin fragancia, fabricadas con 99% de agua pura. Ideales para piel sensible y recién nacidos. Certificadas dermatológicamente. Sin alcohol, sin parabenos. Pack x80 unidades.',
                'brand_id'          => $hug,
                'category_id'       => $cats['toallitas-humedas'],
                'price'             => 3490,
                'compare_at_price'  => null,
                'cost_price'        => 1400,
                'has_variants'      => false,
                'stock'             => 150,
                'status'            => 'active',
                'featured'          => false,
                'weight_grams'      => 450,
                'stages'            => ['0-3m', '3-6m', '6-12m', '12-24m'],
            ],
            [
                'sku'               => 'CUI-AGB-CRE-001',
                'name'              => 'Crema protectora de pañal D-Panthenol 100g',
                'slug'              => 'crema-protectora-panal-d-panthenol',
                'short_description' => 'Crema protectora con D-Panthenol que previene y alivia la rozadura.',
                'description'       => 'Crema protectora para el área del pañal con D-Panthenol y óxido de zinc. Crea una barrera protectora que previene la rozadura y alivia la piel irritada. Fórmula sin perfume, sin colorantes. Apta para uso diario desde el primer día.',
                'brand_id'          => $agb,
                'category_id'       => $cats['cremas-pomadas'],
                'price'             => 5990,
                'compare_at_price'  => 7490,
                'cost_price'        => 2200,
                'has_variants'      => false,
                'stock'             => 70,
                'status'            => 'active',
                'featured'          => true,
                'weight_grams'      => 120,
                'stages'            => ['0-3m', '3-6m', '6-12m'],
            ],
            [
                'sku'               => 'CUI-AGB-CRE-002',
                'name'              => 'Shampoo suave sin lágrimas 200ml',
                'slug'              => 'shampoo-suave-sin-lagrimas-200ml',
                'short_description' => 'Shampoo ultrasuave con fórmula sin lágrimas para la hora del baño.',
                'description'       => 'Shampoo para bebés con fórmula ultrasuave sin lágrimas. pH balanceado para no irritar los ojos. Sin sulfatos, sin parabenos, sin colorantes. Aroma suave a lavanda. Dermatológicamente testeado. Apto desde el primer día.',
                'brand_id'          => $agb,
                'category_id'       => $cats['cremas-pomadas'],
                'price'             => 4990,
                'compare_at_price'  => null,
                'cost_price'        => 1900,
                'has_variants'      => false,
                'stock'             => 55,
                'status'            => 'active',
                'featured'          => false,
                'weight_grams'      => 210,
                'stages'            => ['0-3m', '3-6m', '6-12m', '12-24m'],
            ],
        ];

        foreach ($products as $data) {
            $stagesSlugs = $data['stages'];
            unset($data['stages']);

            $product = Product::create($data);
            $product->ageStages()->attach(AgeStage::whereIn('slug', $stagesSlugs)->pluck('id'));
        }
    }
}
