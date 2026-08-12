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
            // ── Marcas adicionales (agregadas al catálogo demo) ────────────
            // Sin archivo de logo disponible aún en public/images/Marcas/ → file = null.
            [
                'name' => 'Acer',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'LG',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Corsair',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Gigabyte',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Nvidia',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Seagate',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Western Digital',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Epson',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Brother',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Anker',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Belkin',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Netgear',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Ubiquiti',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Nintendo',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'GoPro',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Hisense',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Huawei',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Motorola',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'OnePlus',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Realme',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Amazfit',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Garmin',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'HyperX',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'SteelSeries',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Marshall',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Edifier',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Philips',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Cooler Master',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Redragon',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Microsoft',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'SanDisk',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Toshiba',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'BenQ',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'ViewSonic',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Logitech G',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'NZXT',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Thermaltake',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'EVGA',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Crucial',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Google',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Bose',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Sennheiser',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'D-Link',
                'file' => null,
                'verified' => true,
            ],
            [
                'name' => 'Zyxel',
                'file' => null,
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

        $this->command->info('Seeder de Marcas ejecutado exitosamente con ' . count($marcasIniciales) . ' marcas.');
    }
}
