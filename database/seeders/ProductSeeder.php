<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::all();

        $products = [
            ['name' => 'MacBook Pro 14 M3', 'supplier' => 'Apple', 'cost' => 14500.00],
            ['name' => 'Magic Mouse', 'supplier' => 'Apple', 'cost' => 550.00],
            ['name' => 'Teclado Mecânico Logitech G Pro X', 'supplier' => 'Logitech', 'cost' => 580.00],
            ['name' => 'Mouse Sem Fio Logitech MX Master 3S', 'supplier' => 'Logitech', 'cost' => 390.00],
            ['name' => 'Webcam Logitech C920s Pro HD', 'supplier' => 'Logitech', 'cost' => 280.00],
            ['name' => 'Monitor LG UltraGear 27" IPS 144Hz', 'supplier' => 'LG', 'cost' => 950.00],
            ['name' => 'Monitor Dell 27" 4K S2721QS', 'supplier' => 'Dell', 'cost' => 1600.00],
            ['name' => 'Headset Gamer HyperX Cloud II', 'supplier' => 'HyperX', 'cost' => 350.00],
            ['name' => 'Microfone Kingston HyperX QuadCast S', 'supplier' => 'HyperX', 'cost' => 650.00],
            ['name' => 'SSD NVMe Kingston NV2 1TB', 'supplier' => 'Kingston', 'cost' => 290.00],
            ['name' => 'HD Externo Portátil Seagate Expansion 2TB', 'supplier' => 'Seagate', 'cost' => 340.00],
            ['name' => 'SSD Portátil SanDisk Extreme 1TB', 'supplier' => 'SanDisk', 'cost' => 580.00],
            ['name' => 'Cadeira Ergonômica Comfy Caprice', 'supplier' => 'Comfy', 'cost' => 850.00],
            ['name' => 'Mesa Digitalizadora Wacom Intuos Small', 'supplier' => 'Wacom', 'cost' => 310.00],
            ['name' => 'Hub USB-C 7 em 1 Anker PowerExpand', 'supplier' => 'Anker', 'cost' => 210.00],
            ['name' => 'Carregador GaN 65W Baseus', 'supplier' => 'Baseus', 'cost' => 120.00],
            ['name' => 'Cabo USB-C para USB-C 2m Baseus', 'supplier' => 'Baseus', 'cost' => 30.00],
            ['name' => 'Suporte para Notebook Ergonomico', 'supplier' => 'Elg', 'cost' => 55.00],
            ['name' => 'Luminária de Monitor ScreenBar BenQ', 'supplier' => 'BenQ', 'cost' => 600.00],
            ['name' => 'Filtro de Linha iClamper Energia 5', 'supplier' => 'Clamper', 'cost' => 45.00],
            ['name' => 'Nobreak Intelbras Attiv 600VA', 'supplier' => 'Intelbras', 'cost' => 270.00],
            ['name' => 'Roteador TP-Link Archer AX12 Wi-Fi 6', 'supplier' => 'TP-Link', 'cost' => 190.00],
            ['name' => 'Impressora Laser Brother HL-L2320D', 'supplier' => 'Brother', 'cost' => 890.00],
            ['name' => 'Processador AMD Ryzen 5 5600X', 'supplier' => 'AMD', 'cost' => 700.00],
            ['name' => 'Placa de Vídeo ASUS Dual RTX 4060', 'supplier' => 'ASUS', 'cost' => 1750.00],
            ['name' => 'Memória RAM Corsair Vengeance LPX 16GB', 'supplier' => 'Corsair', 'cost' => 210.00],
            ['name' => 'Fonte Corsair CV550 550W 80 Plus', 'supplier' => 'Corsair', 'cost' => 250.00],
            ['name' => 'Gabinete Gamer Pichau Apus Black', 'supplier' => 'Pichau', 'cost' => 140.00],
            ['name' => 'Placa-Mãe MSI B550M Pro-VDH WiFi', 'supplier' => 'MSI', 'cost' => 560.00],
            ['name' => 'Water Cooler DeepCool LE520 240mm', 'supplier' => 'DeepCool', 'cost' => 280.00],
        ];

        foreach ($user as $u) {
             foreach ($products as $index => $item) {
                $cost = $item['cost'];
                $sale = $cost * fake()->randomFloat(2, 1.1, 1.8);

                Product::updateOrCreate(
                    ['name' => $item['name'], 'user_id' => $u->id],
                    [
                        'description' => fake()->sentence(10),
                        'status' => 'ativo',
                        'quantity' => fake()->numberBetween(1, 150),
                        'cost_price' => round($cost, 2),
                        'sale_price' => round($sale, 2),
                        'supplier' => $item['supplier'],
                    ]
                );
            }
        }
    }
}
