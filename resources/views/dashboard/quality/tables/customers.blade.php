<table class="table table-sm table-bordered table-striped caption-top">
                <caption class ="border rounded-top p-2 text-dark bg-light">
                    <form action="{{ route('quality.search') }}" method="GET">
                            @csrf
                            <div class="row g-3 mb-0">
                                <div class="col-lg-6 col-12">
                                    <label for="customer" class="form-label">Sedes</label>
                                    <input type="text" class="form-control form-control-sm" id="search-customer" name="search_customer"
                                        value="{{ request('search_customer') }}" placeholder="Buscar por cliente">
                                </div>
                                <div class="col-auto">
                                    <label for="search-matrix" class="form-label">Origen</label>
                                    <select class="form-select form-select-sm" id="search-matrix" name="search_matrix">
                                        <option value="">Todas las matrices</option>
                                        @foreach ($matrix as $m)
                                            <option value="{{ $m->id }}"
                                                {{ request('search_matrix') == $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Botones -->
                                <div class="col-lg-12 d-flex justify-content-end m-0">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                </caption>
                <thead>
                    <tr>
                        <th class="fw-bold" scope="col">#</th>
                        <th class="fw-bold" scope="col">Sedes</th>
                        <th class="fw-bold" scope="col">Teléfono</th>
                        <th class="fw-bold" scope="col">Correo</th>
                        <th class="fw-bold" scope="col">Tipo</th>
                        <th class="fw-bold" scope="col">Origen</th>
                        <th class="fw-bold" scope="col">Administrado por</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    @foreach ($customers as $index => $customer)
                        <tr id="customer{{ $customer->id }}">
                            <th scope="row">{{ $index + 1 }}</th>
                            <td><a href="{{ route('quality.customer', ['id' => $customer->id]) }}">{{ $customer->name }}</a>
                            </td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->serviceType->name }}</td>
                            <td>{{ isset($customer->matrix->name) ? $customer->matrix->name . ' (' . $customer->matrix->id . ')' : 'Matriz' }}
                            <td class="fw-bold {{ $customer->administrative_id ? 'text-success' : 'text-danger' }}"> {{ $customer->administrative->name ?? '-' }} </td>
                        </tr>
                    @endforeach
                </tbody>
</table>
