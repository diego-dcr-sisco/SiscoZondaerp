
@if ($warehouse->is_active && $warehouse->name != 'SISCOPLAGAS-MRO')
    {{-- Editar --}}
    @if(auth()->user()->work_department_id == 1)
        <a href="{{ route('stock.edit', ['id' => $warehouse->id]) }}"
            class="btn btn-secondary btn-sm">
            <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
        </a>
    @endif
    @if(auth()->user()->work_department_id == 1 || auth()->user()->work_department_id == 5 || auth()->user()->work_department_id == 6)
        {{-- Entradas --}}
        {{-- <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#inputModal"
            data-products="{{ json_encode($warehouse->products) }}"
            warehouse-id="{{ $warehouse->id }}"
            onclick="setDestinationWarehouses(this)">
            <i class="bi bi-box-arrow-in-down-right"></i> {{ __('buttons.input') }}
        </button> --}}
        <a href="{{ route('stock.entry', ['id' => $warehouse->id]) }}" class="btn btn-success btn-sm">
            <i class="bi bi-box-arrow-in-down-right"></i> {{ __('buttons.input') }}
        </a>
        {{-- Salidas --}}
        {{-- <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#outputModal"
            data-products="{{ json_encode($warehouse->products) }}" onclick="movementConfig(this)">
            <i class="bi bi-box-arrow-up-left"></i> {{ __('buttons.output') }}
        </button> --}}
        <a href="{{ route('stock.exits', ['id' => $warehouse->id]) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-box-arrow-up-left"></i> {{ __('buttons.output') }}
        </a>
    @endif
    {{-- Movimientos --}}
    <a href="{{ route('stock.movements.warehouse', ['id' => $warehouse->id, 'type' => 1]) }}"
        class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-left-right"></i> {{ __('buttons.movements') }}
    </a>
    {{-- Stock --}}
    <a href="{{ route('stock.showProducts', ['id' => $warehouse->id]) }}"
        class="btn btn-dark btn-sm">
        <i class="bi bi-boxes"></i> Productos
    </a>
    {{-- Eliminar --}}
    @if(auth()->user()->work_department_id == 1)
        <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $warehouse->id }})">
            <i class="bi bi-trash-fill"></i> {{ __('buttons.delete') }}
        </button>
    @endif
    <form id="delete-form-{{ $warehouse->id }}" action="{{ route('stock.destroy', ['id' => $warehouse->id]) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <script>
        function confirmDelete(id) {        
            if (confirm('¿Estás seguro de que deseas eliminar este almacén?')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@endif