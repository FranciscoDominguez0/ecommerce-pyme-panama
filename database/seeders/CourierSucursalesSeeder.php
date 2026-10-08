<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CourierSucursal;

class CourierSucursalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('courier_sucursales')->truncate();

        $csvFile = fopen(database_path('data/courier_sucursales.csv'), 'r');
        $firstLine = true;

        while (($data = fgetcsv($csvFile, 2000, ',')) !== false) {
            if ($firstLine) {
                $firstLine = false;
                continue;
            }

            CourierSucursal::create([
                'zona' => $data[1],
                'courier' => $data[2],
                'sucursal' => $data[3],
                'direccion' => $data[4],
                'tarifa_uno_hasta_7lb' => $data[5] !== '' ? $data[5] : 6.50, // Default price if empty
                'verificacion' => $data[6],
                'observaciones' => $data[7],
                'activo' => true, // El CSV trae 0, los forzamos a true para que se muestren
                'telefono' => $data[9],
                'fuente_url' => $data[10],
                'tipo_punto' => $data[11],
            ]);
        }

        fclose($csvFile);
    }
}
