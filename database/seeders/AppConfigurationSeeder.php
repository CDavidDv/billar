<?php

namespace Database\Seeders;

use App\Models\AppConfiguration;
use Illuminate\Database\Seeder;

class AppConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'key' => 'billing_hourly_rate',
                'value' => '50',
                'type' => 'number',
                'label' => 'Tarifa por hora (billar común)',
                'description' => 'Precio en pesos por hora de uso de mesa de billar.',
                'group' => 'precios',
            ],
            [
                'key' => 'billing_private_rate',
                'value' => '100',
                'type' => 'number',
                'label' => 'Tarifa cuarto privado',
                'description' => 'Precio por hora del cuarto privado.',
                'group' => 'precios',
            ],
            [
                'key' => 'billing_rounding',
                'value' => 'fraction',
                'type' => 'select',
                'label' => 'Redondeo de cobro',
                'description' => 'fraction = cobra fracción exacta · hour = redondea a hora completa.',
                'group' => 'precios',
            ],
            [
                'key' => 'caguama_alert_hours',
                'value' => '8',
                'type' => 'number',
                'label' => 'Horas antes de alerta en caguama',
                'description' => 'Cuántas horas abierta antes de mostrar advertencia de calidad.',
                'group' => 'caguamas',
            ],
            [
                'key' => 'low_stock_notify',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Alerta de stock bajo en dashboard',
                'description' => 'Mostrar badge de alerta cuando haya productos bajo el mínimo.',
                'group' => 'inventario',
            ],
        ];

        foreach ($configs as $config) {
            AppConfiguration::updateOrCreate(['key' => $config['key']], $config);
        }
    }
}
