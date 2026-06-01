<div class="d-flex flex-wrap align-items-center justify-content-between border-bottom px-3 py-2 mb-2 bg-white">
    <div class="d-flex align-items-center gap-2">
        @if(!empty($backRoute))
            <a href="{{ $backRoute }}" class="btn btn-outline-dark btn-sm" data-bs-toggle="tooltip" title="Regresar">
                <i class="bi bi-arrow-left"></i>
            </a>
        @endif
        <i class="bi {{ $icon ?? 'bi-diagram-3' }} fs-4 {{ $iconColor ?? 'text-primary' }}"></i>
        <span class="text-black fw-bold fs-4 mb-0">{{ $title }}</span>
    </div>
    @if(!empty($actionButtonId) && !empty($actionText))
        <button class="btn {{ $actionClass ?? 'btn-primary' }} btn-sm" id="{{ $actionButtonId }}">
            <span id="{{ $actionButtonContentId ?? 'btnContent' }}">
                <i class="bi {{ $actionIcon ?? 'bi-plus-lg' }} fw-bold"></i> {{ $actionText }}
            </span>
            @if(!empty($actionLoadingText))
                <span id="{{ $actionButtonLoadingId ?? 'btnLoading' }}" style="display: none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ $actionLoadingText }}
                </span>
            @endif
        </button>
    @elseif(!empty($actionRoute) && !empty($actionText))
        <a class="btn btn-primary btn-sm" href="{{ $actionRoute }}">
            <i class="bi {{ $actionIcon ?? 'bi-plus-lg' }} fw-bold"></i> {{ $actionText }}
        </a>
    @endif
</div>
