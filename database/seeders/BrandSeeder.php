<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BrandSeeder extends Seeder
{
    use WithoutModelEvents;

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

            [
                'name' => 'Acer',
                'file' => 'Acer.webp',
                'verified' => true,
            ],
            [
                'name' => 'LG',
                'file' => 'LG.webp',
                'verified' => true,
            ],
            [
                'name' => 'Corsair',
                'file' => 'Corsair.webp',
                'verified' => true,
            ],
            [
                'name' => 'Gigabyte',
                'file' => 'Gigabyte.webp',
                'verified' => true,
            ],
            [
                'name' => 'Nvidia',
                'file' => 'nvidia.webp',
                'verified' => true,
            ],
            [
                'name' => 'Seagate',
                'file' => 'Seagate.webp',
                'verified' => true,
            ],
            [
                'name' => 'Western Digital',
                'file' => 'Western Digital.webp',
                'verified' => true,
            ],
            [
                'name' => 'Epson',
                'file' => 'Epson.webp',
                'verified' => true,
            ],
            [
                'name' => 'Brother',
                'file' => 'Brother.webp',
                'verified' => true,
            ],
            [
                'name' => 'Anker',
                'file' => 'Anker.webp',
                'verified' => true,
            ],
            [
                'name' => 'Belkin',
                'file' => 'Belkin.webp',
                'verified' => true,
            ],
            [
                'name' => 'Netgear',
                'file' => 'netgear.webp',
                'verified' => true,
            ],
            [
                'name' => 'Ubiquiti',
                'file' => 'Ubiquiti.webp',
                'verified' => true,
            ],
            [
                'name' => 'Nintendo',
                'file' => 'Nintendo.webp',
                'verified' => true,
            ],
            [
                'name' => 'GoPro',
                'file' => 'gopro.webp',
                'verified' => true,
            ],
            [
                'name' => 'Hisense',
                'file' => 'Hisense.webp',
                'verified' => true,
            ],
            [
                'name' => 'Huawei',
                'file' => 'huawei.webp',
                'verified' => true,
            ],
            [
                'name' => 'Motorola',
                'file' => 'Motorola.webp',
                'verified' => true,
            ],
            [
                'name' => 'OnePlus',
                'file' => 'oneplus.webp',
                'verified' => true,
            ],
            [
                'name' => 'Realme',
                'file' => 'realme.webp',
                'verified' => true,
            ],
            [
                'name' => 'Amazfit',
                'file' => 'Amazfit.webp',
                'verified' => true,
            ],
            [
                'name' => 'Garmin',
                'file' => 'garmin.webp',
                'verified' => true,
            ],
            [
                'name' => 'HyperX',
                'file' => 'HyperX.webp',
                'verified' => true,
            ],
            [
                'name' => 'SteelSeries',
                'file' => 'SteelSeries.webp',
                'verified' => true,
            ],
            [
                'name' => 'Marshall',
                'file' => 'Marshall.webp',
                'verified' => true,
            ],
            [
                'name' => 'Edifier',
                'file' => 'Edifier.webp',
                'verified' => true,
            ],
            [
                'name' => 'Philips',
                'file' => 'Phillips.webp',
                'verified' => true,
            ],
            [
                'name' => 'Cooler Master',
                'file' => 'Cooler-Mater.webp',
                'verified' => true,
            ],
            [
                'name' => 'Redragon',
                'file' => 'Redragon.webp',
                'verified' => true,
            ],
            [
                'name' => 'Microsoft',
                'file' => 'Microsoft.webp',
                'verified' => true,
            ],
            [
                'name' => 'SanDisk',
                'file' => 'SanDisk.webp',
                'verified' => true,
            ],
            [
                'name' => 'Toshiba',
                'file' => 'Toshiba.webp',
                'verified' => true,
            ],
            [
                'name' => 'BenQ',
                'file' => 'Benq.webp',
                'verified' => true,
            ],
            [
                'name' => 'ViewSonic',
                'file' => 'ViewSonic.webp',
                'verified' => true,
            ],
            [
                'name' => 'Logitech G',
                'file' => 'Logitech-G.webp',
                'verified' => true,
            ],
            [
                'name' => 'NZXT',
                'file' => 'NZXT.webp',
                'verified' => true,
            ],
            [
                'name' => 'Thermaltake',
                'file' => 'Thermaltake.webp',
                'verified' => true,
            ],
            [
                'name' => 'EVGA',
                'file' => 'EVGA.webp',
                'verified' => true,
            ],
            [
                'name' => 'Crucial',
                'file' => 'crucial.webp',
                'verified' => true,
            ],
            [
                'name' => 'Google',
                'file' => 'google.webp',
                'verified' => true,
            ],
            [
                'name' => 'Bose',
                'file' => 'Bose.webp',
                'verified' => true,
            ],
            [
                'name' => 'Sennheiser',
                'file' => 'Sennheiser.webp',
                'verified' => true,
            ],
            [
                'name' => 'D-Link',
                'file' => 'D-Link.webp',
                'verified' => true,
            ],
            [
                'name' => 'Zyxel',
                'file' => 'Zyxel.webp',
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
