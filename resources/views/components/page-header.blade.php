<div class="d-flex flex-wrap align-items-center justify-content-between border-bottom px-4 py-3 mb-3 bg-white">
    <div class="d-flex align-items-center gap-2">
        <i class="bi {{ $icon ?? 'bi-diagram-3' }} fs-4 text-primary"></i>
        <span class="text-black fw-bold fs-4 mb-0">{{ $title }}</span>
    </div>
    @if(!empty($actionRoute) && !empty($actionText))
        <a class="btn btn-primary btn-sm" href="{{ $actionRoute }}">
            <i class="bi {{ $actionIcon ?? 'bi-plus-lg' }} fw-bold"></i> {{ $actionText }}
        </a>
    @endif
</div>
