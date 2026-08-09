<?php

namespace App\Helpers;

class GeolocalizacionPanama
{
    public static function provincias(): array
    {
        return [
            'Bocas del Toro' => 'Bocas del Toro',
            'Coclé' => 'Coclé',
            'Colón' => 'Colón',
            'Chiriquí' => 'Chiriquí',
            'Darién' => 'Darién',
            'Herrera' => 'Herrera',
            'Los Santos' => 'Los Santos',
            'Panamá' => 'Panamá',
            'Panamá Oeste' => 'Panamá Oeste',
            'Veraguas' => 'Veraguas',
            'Emberá-Wounaan' => 'Emberá-Wounaan',
            'Guna Yala' => 'Guna Yala',
            'Ngäbe-Buglé' => 'Ngäbe-Buglé',
        ];
    }

    public static function distritosPorProvincia(string $provincia): array
    {
        $mapa = self::data();
        $mayus = mb_strtoupper($provincia);
        foreach ($mapa as $prov => $datos) {
            if (mb_strtoupper($prov) === $mayus) {
                return array_keys($datos['distritos']);
            }
        }
        return [];
    }

    public static function corregimientosPorDistrito(string $distrito): array
    {
        $mapa = self::data();
        $mayus = mb_strtoupper($distrito);
        foreach ($mapa as $prov => $datos) {
            foreach ($datos['distritos'] as $dist => $corregimientos) {
                if (mb_strtoupper($dist) === $mayus) {
                    return $corregimientos;
                }
            }
        }
        return [];
    }

    public static function allDistritos(): array
    {
        $resultado = [];
        $mapa = self::data();
        foreach ($mapa as $provincia => $datos) {
            foreach ($datos['distritos'] as $distrito => $corregimientos) {
                $resultado[$provincia][] = $distrito;
            }
        }
        return $resultado;
    }

    public static function data(): array
    {
        return [
            'Bocas del Toro' => [
                'distritos' => [
                    'Bocas del Toro' => ['Bocas del Toro', 'Bastimentos', 'Cauchero', 'Punta Laurel', 'Tierra Oscura'],
                    'Changuinola' => ['Changuinola', 'Almirante', 'Guabito', 'El Teribe', 'Valle del Risco', 'El Silencio', 'El Empalme', 'Las Tablas', 'Nance del Risco', 'Cochigro', 'La Mesa', 'Las Delicias'],
                    'Chiriquí Grande' => ['Chiriquí Grande', 'Miramar', 'Punta Peña', 'Punta Róbalo', 'Rambala', 'Bajo Cedro'],
                    'Almirante' => ['Almirante', 'Barriada Guaymí', 'Barrio Francés', 'Nance de Riscó'],
                ],
            ],
            'Coclé' => [
                'distritos' => [
                    'Aguadulce' => ['Aguadulce', 'El Cristo', 'El Roble', 'Pocrí', 'Barrios Unidos', 'Pueblo Nuevo', 'San Juan de Dios', 'Vista Hermosa'],
                    'Antón' => ['Antón', 'Cabuya', 'El Chirú', 'El Retiro', 'El Valle', 'Juan Díaz', 'Río Hato', 'San Juan de Dios', 'Santa Rita', 'Caballero'],
                    'La Pintada' => ['La Pintada', 'El Harino', 'El Potrero', 'Las Lomas', 'Llano Grande', 'Piedras Gordas'],
                    'Natá' => ['Natá', 'Capellanía', 'El Caño', 'Guzmán', 'Las Huacas', 'Toza', 'Villarreal'],
                    'Olá' => ['Olá', 'El Copé', 'El Palmar', 'El Picacho', 'La Pava'],
                    'Penonomé' => ['Penonomé', 'Cañaveral', 'Coclé', 'Chiguirí Arriba', 'El Coco', 'Pajonal', 'Río Grande', 'Río Indio', 'Toabré', 'Tulú'],
                ],
            ],
            'Colón' => [
                'distritos' => [
                    'Colón' => ['Barrio Norte', 'Barrio Sur', 'Buena Vista', 'Cativá', 'Ciricito', 'Cristóbal', 'Escobal', 'Limón', 'Nueva Providencia', 'Puerto Pilón', 'Sabanitas', 'Salamanca', 'San Juan', 'Santa Rosa'],
                    'Chagres' => ['Nuevo Chagres', 'Achiote', 'El Guabo', 'La Encantada', 'Palmas Bellas', 'Piña', 'Salud'],
                    'Donoso' => ['Miguel de la Borda', 'Coclé del Norte', 'El Guasimo', 'Gobea', 'Río Indio', 'San José del General'],
                    'Portobelo' => ['Portobelo', 'Cacique', 'Garrote', 'Isla Grande', 'María Chiquita', 'Nuevo Tonosí'],
                    'Santa Isabel' => ['Palenque', 'Cuango', 'Miramar', 'Nombre de Dios', 'Palmira', 'Playa Chiquita', 'Santa Isabel', 'Viento Frío'],
                ],
            ],
            'Chiriquí' => [
                'distritos' => [
                    'Alanje' => ['Alanje', 'Divalá', 'El Tejar', 'Guarumal', 'Palo Grande', 'Querévalo', 'Santo Tomás', 'Canta Gallo', 'Nuevo México'],
                    'Barú' => ['Puerto Armuelles', 'Limones', 'Progreso', 'Baco', 'Rodolfo Aguilar Delgado'],
                    'Boquerón' => ['Boquerón', 'Bágala', 'Cordillera', 'Guabal', 'Guayabal', 'Paraíso', 'Pedregal', 'Tijeras'],
                    'Boquete' => ['Boquete', 'Alto Boquete', 'Caldera', 'Jaramillo', 'Los Naranjos', 'Palmira'],
                    'Bugaba' => ['La Concepción', 'Aserrío de Gariché', 'Bugaba', 'Cerro Punta', 'El Bongo', 'Gómez', 'La Estrella', 'San Andrés', 'Santa Marta', 'Santa Rosa', 'Santo Domingo', 'Solano', 'Sortová'],
                    'David' => ['David', 'Bijagual', 'Cochea', 'Chiriquí', 'Guacá', 'Las Lomas', 'Pedregal', 'San Carlos', 'San Pablo Nuevo', 'San Pablo Viejo'],
                    'Dolega' => ['Dolega', 'Dos Ríos', 'Los Anastacios', 'Potrerillos', 'Potrerillos Abajo', 'Rovira', 'Tinajas'],
                    'Gualaca' => ['Gualaca', 'Hornito', 'Los Ángeles', 'Paja de Sombrero', 'Rincón'],
                    'Remedios' => ['Remedios', 'El Nancito', 'El Porvenir', 'El Puerto', 'Santa Lucía'],
                    'Renacimiento' => ['Río Sereno', 'Breñón', 'Cañas Gordas', 'Monte Lirio', 'Plaza de Caisán', 'Santa Cruz', 'Dominical', 'Santa Clara'],
                    'San Félix' => ['San Félix', 'Las Lajas', 'Lajas Adentro', 'Juay', 'San Juan'],
                    'San Lorenzo' => ['Horconcitos', 'Boca Chica', 'Boca del Monte', 'San Juan', 'San Lorenzo'],
                    'Tierras Altas' => ['Volcán', 'Cerro Punta', 'Cuesta de Piedra', 'Nueva California', 'Paso Ancho'],
                    'Tolé' => ['Tolé', 'Bella Vista', 'Cerro Viejo', 'El Cristo', 'Justo Fidel Palacios', 'Lajas de Tolé', 'Potrero de Caña', 'Quebrada de Piedra', 'Veladero'],
                ],
            ],
            'Darién' => [
                'distritos' => [
                    'Chepigana' => ['La Palma', 'Camoganti', 'Chepigana', 'Garachiné', 'Jaqué', 'Puerto Piña', 'Río Congo', 'Río Iglesias', 'Sambú', 'Setegantí', 'Taimatí', 'Tucutí'],
                    'Pinogana' => ['El Real de Santa María', 'Boca de Cupé', 'Metetí', 'Paya', 'Pinogana', 'Púcuro', 'Yape', 'Yaviza'],
                    'Santa Fe' => ['Santa Fe', 'Cupe', 'Río Congo', 'Agua Fría', 'Margaritas'],
                ],
            ],
            'Herrera' => [
                'distritos' => [
                    'Chitré' => ['Chitré', 'La Arena', 'Monagrillo', 'Llano Bonito', 'San Juan Bautista'],
                    'Las Minas' => ['Las Minas', 'Chepo', 'Chumical', 'El Toro', 'Leones', 'Quebrada del Rosario', 'Quebrada El Ciprián'],
                    'Los Pozos' => ['Los Pozos', 'El Capurí', 'El Calabacito', 'El Cedro', 'La Arena', 'La Pitaloza', 'Los Cerritos', 'Los Cerros de Paja', 'Las Llanas'],
                    'Ocú' => ['Ocú', 'Cerro Largo', 'Los Llanos', 'Llano Grande', 'Peñas Chatas', 'El Tijera', 'Menchaca'],
                    'Parita' => ['Parita', 'Cabuya', 'Los Castillos', 'Llano de la Cruz', 'París', 'Portobelillo', 'Potuga'],
                    'Pesé' => ['Pesé', 'Las Cabras', 'El Pájaro', 'El Barrero', 'El Pedregoso', 'El Ciruelo', 'Sabanagrande', 'Rincón Hondo'],
                    'Santa María' => ['Santa María', 'Chupampa', 'El Rincón', 'El Limón', 'Los Canelos'],
                ],
            ],
            'Los Santos' => [
                'distritos' => [
                    'Guararé' => ['Guararé', 'El Espinal', 'El Macano', 'Guararé Arriba', 'La Enea', 'La Pasera', 'Las Trancas', 'Llano Abajo', 'El Hato', 'Perales'],
                    'Las Tablas' => ['Las Tablas', 'Bajo Corral', 'Bayano', 'El Carate', 'El Cocal', 'El Manantial', 'El Muñoz', 'El Pedregoso', 'La Laja', 'La Miel', 'La Palma', 'La Tiza', 'Las Palmitas', 'Las Tablas Abajo', 'Nuario', 'Palmira', 'Peña Blanca', 'Río Hondo', 'San José', 'San Miguel', 'Santo Domingo', 'Sesteadero', 'Valle Rico', 'Vallerriquito'],
                    'Los Santos' => ['Los Santos', 'El Ejido', 'El Guásimo', 'La Colorada', 'La Espigadilla', 'Las Cruces', 'Las Guabas', 'Los Ángeles', 'Los Olivos', 'Llano Largo', 'Sabanagrande', 'Santa Ana', 'Tres Quebradas', 'Villa Lourdes', 'Agua Buena'],
                    'Macaracas' => ['Macaracas', 'Bahía Honda', 'Bajos de Güera', 'Corozal', 'Chupa', 'El Cedro', 'Espino Amarillo', 'La Mesa', 'Llano de Piedra', 'Las Palmas', 'Mogollón'],
                    'Pedasí' => ['Pedasí', 'Los Asientos', 'Mariabé', 'Purio', 'Oria Arriba'],
                    'Pocrí' => ['Pocrí', 'El Cañafístulo', 'Lajamina', 'Paraíso', 'Paritilla'],
                    'Tonosí' => ['Tonosí', 'Altos de Güera', 'Cañas', 'El Bebedero', 'El Cacao', 'El Cortezo', 'Flores', 'Guánico', 'La Tronosa', 'Cambutal', 'Isla de Cañas'],
                ],
            ],
            'Panamá' => [
                'distritos' => [
                    'Panamá' => ['San Felipe', 'El Chorrillo', 'Santa Ana', 'Calidonia', 'Curundú', 'Betania', 'Bella Vista', 'Pueblo Nuevo', 'San Francisco', 'Parque Lefevre', 'Río Abajo', 'Juan Díaz', 'Pedregal', 'Ancón', 'Chilibre', 'Las Cumbres', 'Pacora', 'San Martín', 'Tocumen', '24 de Diciembre', 'Ernesto Córdoba', 'Las Mañanitas', 'Alcalde Díaz', 'Don Bosco', 'Las Garzas'],
                    'Balboa' => ['San Miguel', 'La Ensenada', 'La Esmeralda', 'La Guinea', 'Pedro González', 'Saboga'],
                    'Chepo' => ['Chepo', 'Cañita', 'Chepillo', 'El Llano', 'Las Margaritas', 'Santa Cruz de Chinina', 'Madungandi', 'Tortí'],
                    'Chimán' => ['Chimán', 'Brujas', 'Gonzalo Vásquez', 'Pásiga', 'Unión Santeña'],
                    'San Miguelito' => ['Amelia Denis de Icaza', 'Belisario Porras', 'José Domingo Espinar', 'Mateo Iturralde', 'Victoriano Lorenzo', 'Arnulfo Arias', 'Belisario Frías', 'Omar Torrijos', 'Rufina Alfaro'],
                    'Taboga' => ['Taboga', 'Otoque Occidente', 'Otoque Oriente'],
                ],
            ],
            'Panamá Oeste' => [
                'distritos' => [
                    'Arraiján' => ['Arraiján', 'Burunga', 'Cerro Silvestre', 'Juan Demóstenes Arosemena', 'Nuevo Emperador', 'Santa Clara', 'Veracruz', 'Vista Alegre'],
                    'Capira' => ['Capira', 'Caimito', 'Campana', 'Cermeño', 'Cirí de Los Sotos', 'Cirí Grande', 'El Cacao', 'La Trinidad', 'Las Ollas Arriba', 'Lídice', 'Villa Carmen', 'Villa Rosario', 'Santa Rosa'],
                    'Chame' => ['Chame', 'Bejuco', 'Buenos Aires', 'Cabuya', 'Chicá', 'El Líbano', 'Las Lajas', 'Nueva Gorgona', 'Punta Chame', 'Sajalices', 'Sorá'],
                    'La Chorrera' => ['La Chorrera', 'Arosemena', 'El Arado', 'El Coco', 'Feuillet', 'Guadalupe', 'Herrera', 'Hurtado', 'Iturralde', 'La Represa', 'Los Díaz', 'Mendoza', 'Obaldía', 'Playa Leona', 'Puerto Caimito', 'Santa Rita'],
                    'San Carlos' => ['San Carlos', 'El Espino', 'El Higo', 'Guayabito', 'La Ermita', 'La Laguna', 'Las Uvas', 'Los Llanitos', 'San José'],
                ],
            ],
            'Veraguas' => [
                'distritos' => [
                    'Atalaya' => ['Atalaya', 'El Barrito', 'La Montañuela', 'San Antonio'],
                    'Calobre' => ['Calobre', 'Barnizal', 'Chitra', 'El Cocla', 'El Potrero', 'La Laguna', 'La Raya de Calobre', 'La Tetilla', 'La Yeguada', 'Las Guías', 'Monjarás', 'San José'],
                    'Cañazas' => ['Cañazas', 'Cerro de Plata', 'Los Valles', 'San Marcelo', 'El Picador', 'San José'],
                    'La Mesa' => ['La Mesa', 'Bisvalles', 'Boró', 'El Higo', 'Los Milagros', 'Llano Grande', 'San Bartolo'],
                    'Las Palmas' => ['Las Palmas', 'Cerro de Casa', 'Corozal', 'El María', 'El Prado', 'El Rincón', 'Lolá', 'Pixvae', 'Puerto Vidal', 'San Martín de Porres', 'Viguí', 'Zapotillo'],
                    'Mariato' => ['Mariato', 'Arenas', 'El Cacao', 'Quebro', 'Tebario'],
                    'Montijo' => ['Montijo', 'Cébaco', 'Cerro de los Inocentes', 'Costillas', 'El Pilón', 'Gobernadora', 'La Garceana', 'Leones', 'Los Corotúes', 'Ponuga', 'Río de Jesús', 'Unión', 'Uran'],
                    'Río de Jesús' => ['Río de Jesús', 'Catorce de Noviembre', 'Las Huacas', 'Los Castillos', 'Utira'],
                    'San Francisco' => ['San Francisco', 'Corral Falso', 'Los Hatillos', 'Remance', 'San Juan', 'San José'],
                    'Santa Fe' => ['Santa Fe', 'Calovébora', 'El Alto', 'El Cuay', 'El Pantano', 'Gatuncito', 'Río Luis', 'Rubén Cantú'],
                    'Santiago' => ['Santiago', 'Correa', 'Canto del Llano', 'Edwin Fábrega', 'La Colorada', 'La Peña', 'La Raya de Santa María', 'Los Algarrobos', 'Ponuga'],
                    'Soná' => ['Soná', 'Bahía Honda', 'Calidonia', 'Cativé', 'El Marañón', 'Guarumal', 'La Soledad', 'Quebrada de Oro', 'Río Grande', 'Rodeo Viejo'],
                ],
            ],
            'Emberá-Wounaan' => [
                'distritos' => [
                    'Cémaco' => ['Unión Chocó', 'Cirilo Guaynora', 'Lajas Blancas', 'Manuel Ortega'],
                    'Sambú' => ['Río Sábalo', 'Jingurudó'],
                ],
            ],
            'Guna Yala' => [
                'distritos' => [
                    'Guna Yala' => ['El Porvenir', 'Ailigandí', 'Cartí', 'Isla Pino', 'Madungandí', 'Narganá', 'Puerto Obaldía', 'Tubualá'],
                ],
            ],
            'Ngäbe-Buglé' => [
                'distritos' => [
                    'Besiko' => ['Soloy', 'Boca de Balsa', 'Cerro Banco', 'Cerro de Patena', 'Emplanada de Chorcha', 'Namoní', 'Niba'],
                    'Jirondai' => ['Jirondai', 'Burí', 'Guariviara', 'San Félix'],
                    'Kankintú' => ['Kankintú', 'Bisira', 'Guariviara', 'Mününí'],
                    'Kusapín' => ['Kusapín', 'Bahía Azul', 'Calovébora', 'Cerro Venado', 'Loma Yuca', 'Río Chiriquí', 'Tobobe'],
                    'Mironó' => ['Mironó', 'Hato Pilón', 'Cascabel', 'Piedra Roja'],
                    'Müna' => ['Peña Blanca', 'Cerro Caña', 'Cerro Iglesias', 'Cerro Silencio', 'Chichica'],
                    'Nole Duima' => ['Cerro Iglesias', 'Lajero Arriba', 'Hato Chamí', 'Susama', 'Jádeberi'],
                    'Ñürüm' => ['Buenos Aires', 'Agua de Salud', 'Cerro Pelado', 'El Bale', 'El Piro', 'Guayabito'],
                ],
            ],
        ];
    }
}
