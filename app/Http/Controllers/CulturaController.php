<?php

namespace App\Http\Controllers;

class CulturaController extends Controller
{
    public function index()
    {
        $valores = [
            [
                'titulo' => 'Candidez',
                // Aún no existe una ilustración propia para este valor nuevo;
                // se reutiliza temporalmente Lider.png (ya no se usa, el valor
                // "Liderazgo" se quitó) hasta que se suba una imagen definitiva.
                'imagen' => 'Lider.png',
                'subtitulo' => 'Decimos las cosas como son, sin filtros ni rodeos.',
                'texto' => 'La candidez es hablar con el corazón por delante: ser honestos incluso cuando es incómodo, reconocer los errores sin excusas y celebrar los aciertos sin disfraces. Así construimos confianza real, no apariencia.',
                'color' => 'gold',
            ],
            [
                'titulo' => 'Creatividad',
                'imagen' => 'Creatividad.png',
                'subtitulo' => 'Las ideas se celebran, no se archivan.',
                'texto' => 'Cada idea que lanzamos al aire es una oportunidad para reinventarnos. La creatividad se aplaude en equipo, saltando literalmente de la emoción.',
                'color' => 'purple',
            ],
            [
                'titulo' => 'Compromiso',
                'imagen' => 'Compromiso.png',
                'subtitulo' => 'Llegamos juntos a la meta.',
                'texto' => 'El compromiso se construye en equipo: cada quien pone su bandera cuando cumple su palabra, sostiene a quien tiene al lado y no suelta hasta que el objetivo es de todos.',
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
                'titulo' => 'Valentía',
                'imagen' => 'Valentia .png',
                'subtitulo' => 'Nos atrevemos a caminar la cuerda floja.',
                'texto' => 'Valentía es tomar decisiones difíciles con la mirada al frente. No le tenemos miedo al riesgo calculado: probamos, ajustamos y seguimos avanzando aunque el camino sea angosto.',
                'color' => 'gold',
            ],
        ];

        return view('cultura.index', ['valores' => $valores]);
    }
}
