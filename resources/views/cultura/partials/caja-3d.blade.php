{{--
    Cultura Dasavena: la caja de Cereal Artesanal Quinoa & Almendras se arma
    con el scroll mientras aparecen los valores, uno por fase del armado.

    Lógica:  resources/js/cultura-caja/  (tiempos, scroll y recortes en config.js)
    Estilos: resources/css/cultura-caja.css
    Arte:    public/images/dasavena/caja-frontal.png y caja-trasera.png

    Sin JavaScript o sin WebGL la sección se muestra como una lista legible
    de valores con la foto del frente (clases .is-live / .is-fallback).
--}}
@php($total = count($valores))
<section class="caja3d" data-caja3d
         data-front-src="{{ asset('images/dasavena/caja-frontal.png') }}"
         data-back-src="{{ asset('images/dasavena/caja-trasera.png') }}"
         aria-label="Nuestra cultura: la caja de Dasavena se arma mientras conoces nuestros valores">

    <div class="caja3d-sticky">
        <div class="caja3d-stage" data-caja3d-stage aria-hidden="true"></div>

        <img class="caja3d-fallback" src="{{ asset('images/dasavena/caja-frontal.png') }}"
             alt="Caja de Dasavena Cereal Artesanal, Quinoa y Almendras" loading="lazy">

        <div class="caja3d-loader" aria-hidden="true"><span></span></div>

        <div class="caja3d-copy">
            <div class="caja3d-slot caja3d-intro" data-caja3d-slot>
                <p class="caja3d-eyebrow">Nuestra cultura</p>
                <h1 class="caja3d-title">De ingredientes naturales a una experiencia <em>artesanal.</em></h1>
                <p class="caja3d-lead">Desliza y mira cómo se arma, pieza por pieza, junto con los valores que nos sostienen.</p>
            </div>

            @foreach($valores as $i => $v)
                <article class="caja3d-slot caja3d-value caja3d-tone-{{ $v['color'] }}" data-caja3d-slot>
                    <p class="caja3d-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} <span>/ {{ str_pad($total, 2, '0', STR_PAD_LEFT) }}</span></p>
                    <h2 class="caja3d-value-title">{{ $v['titulo'] }}</h2>
                    <p class="caja3d-value-sub">{{ $v['subtitulo'] }}</p>
                    <p class="caja3d-value-text">{{ $v['texto'] }}</p>
                </article>
            @endforeach
        </div>

        <nav class="caja3d-rail" aria-label="Valores">
            @foreach($valores as $i => $v)
                <button type="button" class="caja3d-rail-dot" data-caja3d-goto="{{ $i + 1 }}">
                    <span class="caja3d-rail-label">{{ $v['titulo'] }}</span>
                    <span class="caja3d-rail-mark" aria-hidden="true"></span>
                </button>
            @endforeach
        </nav>

        <div class="caja3d-hint" data-caja3d-hint aria-hidden="true">
            <span>Desliza</span>
            <i></i>
        </div>

        <div class="caja3d-progress" aria-hidden="true"><span data-caja3d-progress></span></div>
    </div>
</section>

@vite('resources/js/cultura-caja/main.js')
