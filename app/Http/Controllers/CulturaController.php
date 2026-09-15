<?php

namespace App\Http\Controllers;

class CulturaController extends Controller
{
    public function index()
    {
        $valores = [
            [
                'titulo' => 'Compromiso',
                'imagen' => 'Compromiso.png',
                'subtitulo' => 'Llegamos juntos a la meta.',
                'texto' => 'El compromiso se construye en equipo: cada quien pone su bandera cuando cumple su palabra, sostiene a quien tiene al lado y no suelta hasta que el objetivo es de todos.',
                'color' => 'gold',
            ],
            [
                'titulo' => 'Valentía',
                'imagen' => 'Valentia .png',
                'subtitulo' => 'Nos atrevemos a caminar la cuerda floja.',
                'texto' => 'Valentía es tomar decisiones difíciles con la mirada al frente. No le tenemos miedo al riesgo calculado: probamos, ajustamos y seguimos avanzando aunque el camino sea angosto.',
                'color' => 'purple',
            ],
            [
                'titulo' => 'Liderazgo',
                'imagen' => 'Lider.png',
                'subtitulo' => 'Trazamos la ruta, no solo la seguimos.',
                'texto' => 'Un líder Dasavena no espera instrucciones: levanta la bandera, marca el rumbo y contagia a su equipo la confianza de llegar a puerto sin importar el tamaño del barco.',
                'color' => 'gold',
            ],
            [
                'titulo' => 'Espíritu Emprendedor',
                'imagen' => 'Espíritu Emprendedor.png',
                'subtitulo' => 'Hacemos más con lo que tenemos.',
                'texto' => 'Aquí una sola persona sostiene el negocio, la agenda y la cocina al mismo tiempo. El espíritu emprendedor es no esperar a que las condiciones sean perfectas para empezar.',
                'color' => 'purple',
            ],
            [
                'titulo' => 'Creatividad',
                'imagen' => 'Creatividad.png',
                'subtitulo' => 'Las ideas se celebran, no se archivan.',
                'texto' => 'Cada idea que lanzamos al aire es una oportunidad para reinventarnos. La creatividad se aplaude en equipo, saltando literalmente de la emoción.',
                'color' => 'gold',
            ],
        ];

        return view('cultura.index', ['valores' => $valores]);
    }
}
