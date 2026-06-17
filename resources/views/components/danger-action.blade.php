<div class="border border-danger rounded shadow-sm p-3 my-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <div class="fw-bold text-danger">{{ $title ?? 'Zona de peligro' }}</div>
            @isset($description)
                <div class="text-muted small">{{ $description }}</div>
            @endisset
        </div>
        <a href="{{ $actionRoute }}" class="btn btn-danger btn-sm"
            onclick="return confirm('{{ $confirmMessage ?? __('messages.are_you_sure_delete') }}')">
            <i class="bi {{ $icon ?? 'bi-trash-fill' }}"></i> {{ $buttonText ?? __('buttons.delete') }}
        </a>
    </div>
</div>
