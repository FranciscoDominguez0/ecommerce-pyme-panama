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
            [
                'name' => 'Lenovo',
                'file' => 'Lenovo.webp',
                'verified' => true,
            ],
            [
                'name' => 'HP',
                'file' => 'hp.webp',
                'verified' => true,
            ],
            [
                'name' => 'Dell',
                'file' => 'Dell.webp',
                'verified' => true,
            ],
            [
                'name' => 'ASUS',
                'file' => 'Asus.webp',
                'verified' => true,
            ],
            [
                'name' => 'Samsung',
                'file' => 'Samsung.webp',
                'verified' => true,
            ],
            [
                'name' => 'TP-Link',
                'file' => 'tp-link.webp',
                'verified' => true,
            ],
            [
                'name' => 'Logitech',
                'file' => 'Logitech.webp',
                'verified' => true,
            ],
            [
                'name' => 'Apple',
                'file' => 'apple.svg',
                'verified' => true,
            ],
            [
                'name' => 'Adata',
                'file' => 'Adata.webp',
                'verified' => true,
            ],
            [
                'name' => 'AMD',
                'file' => 'Amd.webp',
                'verified' => true,
            ],
            [
                'name' => 'APC',
                'file' => 'Apc.webp',
                'verified' => true,
            ],
            [
                'name' => 'Canon',
                'file' => 'cannon.webp',
                'verified' => true,
            ],
            [
                'name' => 'Intel',
                'file' => 'intel.webp',
                'verified' => true,
            ],
            [
                'name' => 'JBL',
                'file' => 'Jbl.webp',
                'verified' => true,
            ],
            [
                'name' => 'Kingston',
                'file' => 'kingston.webp',
                'verified' => true,
            ],
            [
                'name' => 'MSI',
                'file' => 'Msi.webp',
                'verified' => true,
            ],
            [
                'name' => 'Razer',
                'file' => 'Razer.webp',
                'verified' => true,
            ],
            [
                'name' => 'Sony',
                'file' => 'Sony.webp',
                'verified' => true,
            ],
            [
                'name' => 'Xiaomi',
                'file' => 'Xiaomi.webp',
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
