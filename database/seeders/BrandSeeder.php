<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marcasIniciales = [
            // Las 8 Sugeridas principales
            [
                'name' => 'Lenovo',
                'file' => 'Lenovo.webp',
                'is_suggested' => true,
                'verified' => true,
            ],
            [
                'name' => 'HP',
                'file' => 'hp.webp',
                'is_suggested' => true,
                'verified' => true,
            ],
            [
                'name' => 'Dell',
                'file' => 'Dell.webp',
                'is_suggested' => true,
                'verified' => true,
            ],
            [
                'name' => 'ASUS',
                'file' => 'Asus.webp',
                'is_suggested' => true,
                'verified' => true,
            ],
            [
                'name' => 'Samsung',
                'file' => 'Samsung.webp',
                'is_suggested' => true,
                'verified' => true,
            ],
            [
                'name' => 'TP-Link',
                'file' => 'tp-link.webp',
                'is_suggested' => true,
                'verified' => true,
            ],
            [
                'name' => 'Logitech',
                'file' => 'Logitech.webp',
                'is_suggested' => true,
                'verified' => true,
            ],
            [
                'name' => 'Apple',
                'file' => null,
                'is_suggested' => true,
                'verified' => true,
            ],

            // Las otras 10 marcas del catálogo
            [
                'name' => 'Adata',
                'file' => 'Adata.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'AMD',
                'file' => 'Amd.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'APC',
                'file' => 'Apc.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'Canon',
                'file' => 'cannon.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'Intel',
                'file' => 'intel.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'JBL',
                'file' => 'Jbl.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'MSI',
                'file' => 'Msi.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'Razer',
                'file' => 'Razer.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'Sony',
                'file' => 'Sony.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
            [
                'name' => 'Xiaomi',
                'file' => 'Xiaomi.webp',
                'is_suggested' => false,
                'verified' => true,
            ],
        ];

        $marcasDir = public_path('images/Marcas');

        foreach ($marcasIniciales as $marcaData) {
            $slug = Str::slug($marcaData['name']);
            $imageResource = null;
            $imageMime = null;
            $imagePath = null;

            if (!empty($marcaData['file'])) {
                $fullFilePath = $marcasDir . DIRECTORY_SEPARATOR . $marcaData['file'];
                if (File::exists($fullFilePath)) {
                    $rawBytes = File::get($fullFilePath);
                    $imageMime = 'image/webp';
                    $imagePath = 'images/Marcas/' . $marcaData['file'];
                    
                    // En PostgreSQL bytea se inserta con stream resource o hex
                    $imageResource = fopen('php://memory', 'r+');
                    fwrite($imageResource, $rawBytes);
                    rewind($imageResource);
                }
            }

            $brand = Brand::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $marcaData['name'],
                    'image' => $imageResource,
                    'image_mime' => $imageMime,
                    'image_path' => $imagePath,
                    'is_suggested' => $marcaData['is_suggested'],
                    'verified' => $marcaData['verified'],
                ]
            );

            if (is_resource($imageResource)) {
                fclose($imageResource);
            }

            // Vincular productos existentes con esta marca si coincide el nombre
            Producto::where('marca', 'ILIKE', $marcaData['name'])
                ->orWhere('marca', 'ILIKE', $slug)
                ->update(['brand_id' => $brand->id, 'marca' => $marcaData['name']]);
        }

        $this->command->info('Seeder de Marcas ejecutado exitosamente con 18 marcas iniciales.');
    }
}
