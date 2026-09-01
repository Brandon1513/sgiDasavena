<?php

namespace Database\Seeders;

use App\Domains\Incidencias\Models\OrigenAccionCorrectiva;
use Illuminate\Database\Seeder;

class OrigenAccionCorrectivaSeeder extends Seeder
{
    public function run(): void
    {
        $origenes = [
            [
                'nombre' => 'Auditoría externa',
                'descripcion' => 'Acción correctiva originada por un hallazgo de auditoría externa.',
            ],
            [
                'nombre' => 'Auditoría interna',
                'descripcion' => 'Acción correctiva originada por un hallazgo de auditoría interna.',
            ],
            [
                'nombre' => 'Queja de cliente',
                'descripcion' => 'Acción correctiva originada por una queja o reclamación de cliente.',
            ],
            [
                'nombre' => 'Desviación',
                'descripcion' => 'Acción correctiva originada por una desviación detectada.',
            ],
            [
                'nombre' => 'Incumplimiento',
                'descripcion' => 'Acción correctiva originada por un incumplimiento.',
            ],
            [
                'nombre' => 'Otro',
                'descripcion' => 'Otro origen no contemplado en el catálogo.',
            ],
        ];

        foreach ($origenes as $origen) {
            OrigenAccionCorrectiva::updateOrCreate(
                ['nombre' => $origen['nombre']],
                $origen
            );
        }
    }
}