<x-app-layout>

    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <a
                    href="{{ route('acciones-correctivas.index') }}"
                    class="text-decoration-none text-muted small"
                >
                    ← Acciones Correctivas
                </a>

                <h1 class="h3 mt-2 mb-1">
                    Nueva Acción Correctiva
                </h1>

                <p class="text-muted mb-0">
                    Registra una nueva Acción Correctiva.
                </p>
            </div>

        </div>

        @if ($errors->any())

            <div class="alert alert-danger">

                <div class="fw-semibold mb-2">
                    Revisa los siguientes campos:
                </div>

                <ul class="mb-0">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <form
                    method="POST"
                    action="{{ route('acciones-correctivas.store') }}"
                >

                    @csrf

                    <div class="mb-4">

                        <label
                            for="descripcion"
                            class="form-label fw-semibold"
                        >
                            Descripción
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            class="form-control"
                            rows="5"
                            maxlength="2000"
                            required
                            placeholder="Describe la situación que origina la Acción Correctiva..."
                        >{{ old('descripcion') }}</textarea>

                    </div>

                    <div class="row g-4">

                        <div class="col-md-6">

                            <label
                                for="origen_id"
                                class="form-label fw-semibold"
                            >
                                Origen
                            </label>

                            <select
                                id="origen_id"
                                name="origen_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Selecciona un origen
                                </option>

                                @foreach($origenes as $origen)

                                    <option
                                        value="{{ $origen->id }}"
                                        @selected(old('origen_id') == $origen->id)
                                    >
                                        {{ $origen->nombre }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label
                                for="responsable_id"
                                class="form-label fw-semibold"
                            >
                                Responsable
                            </label>

                            <select
                                id="responsable_id"
                                name="responsable_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Selecciona un responsable
                                </option>

                                @foreach($responsables as $responsable)

                                    <option
                                        value="{{ $responsable->id }}"
                                        @selected(old('responsable_id') == $responsable->id)
                                    >
                                        {{ $responsable->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">

                        <a
                            href="{{ route('acciones-correctivas.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Crear Acción Correctiva
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>