<x-app-layout>

    <div class="container-fluid py-4">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h1 class="h3 mb-1">
                    Acciones Correctivas
                </h1>

                <p class="text-muted mb-0">
                    Gestión y seguimiento de acciones correctivas.
                </p>
            </div>

        </div>

        {{-- Filtros --}}
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body">

                <form
                    method="GET"
                    action="{{ route('acciones-correctivas.index') }}"
                >

                    <div class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label">
                                Buscar
                            </label>

                            <input
                                type="text"
                                name="buscar"
                                value="{{ request('buscar') }}"
                                class="form-control"
                                placeholder="Código o descripción..."
                            >

                        </div>

                        <div class="col-md-3">

                            <label class="form-label">
                                Estado
                            </label>

                            <select
                                name="estado"
                                class="form-select"
                            >

                                <option value="">
                                    Todos
                                </option>

                                @foreach($estados as $estado)

                                    <option
                                        value="{{ $estado->codigo }}"
                                        @selected(
                                            request('estado') === $estado->codigo
                                        )
                                    >
                                        {{ $estado->nombre }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label">
                                Responsable
                            </label>

                            <select
                                name="responsable"
                                class="form-select"
                            >

                                <option value="">
                                    Todos
                                </option>

                                @foreach($responsables as $responsable)

                                    <option
                                        value="{{ $responsable->id }}"
                                        @selected(
                                            (string) request('responsable')
                                            ===
                                            (string) $responsable->id
                                        )
                                    >
                                        {{ $responsable->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-2 d-flex align-items-end">

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Filtrar
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- Tabla --}}
        <div class="card shadow-sm border-0">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>
                                <th>AC</th>
                                <th>Descripción</th>
                                <th>Origen</th>
                                <th>Responsable</th>
                                <th>Estado</th>
                                <th>Avance</th>
                                <th>Ciclo</th>
                                <th></th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($accionesCorrectivas as $ac)

                                <tr>

                                    <td>
                                        <strong>
                                            {{ $ac->codigo }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $ac->descripcion }}
                                    </td>

                                    <td>
                                        {{ $ac->origen->nombre ?? 'Sin origen' }}
                                    </td>

                                    <td>
                                        {{ $ac->responsable->name ?? 'Sin responsable' }}
                                    </td>

                                    <td>

                                        <span class="badge bg-secondary">
                                            {{ $ac->estado->nombre ?? 'Sin estado' }}
                                        </span>

                                    </td>

                                    <td style="min-width: 150px;">

                                        <div class="d-flex align-items-center gap-2">

                                            <div
                                                class="progress flex-grow-1"
                                                style="height: 7px;"
                                            >
                                                <div
                                                    class="progress-bar"
                                                    role="progressbar"
                                                    style="width: {{ $ac->porcentaje_avance }}%"
                                                ></div>
                                            </div>

                                            <small>
                                                {{ number_format($ac->porcentaje_avance, 0) }}%
                                            </small>

                                        </div>

                                    </td>

                                    <td>
                                        {{ $ac->ciclo_actual }}
                                    </td>

                                    <td class="text-end">

                                        <a
                                            href="{{ route(
                                                'acciones-correctivas.show',
                                                $ac
                                            ) }}"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Ver
                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="8"
                                        class="text-center py-5 text-muted"
                                    >
                                        No hay acciones correctivas registradas.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            @if($accionesCorrectivas->hasPages())

                <div class="card-footer bg-white">

                    {{ $accionesCorrectivas->links() }}

                </div>

            @endif

        </div>

    </div>

</x-app-layout>