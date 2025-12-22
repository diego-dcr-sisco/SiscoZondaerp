@php
    $answer = null;
    $pests_data = [];

    function cleanHtmlSimple(string $html, array $config = []): string
    {
        // Configuración por defecto
        $defaultConfig = [
            'keepHtml' => true,
            'keepOnlyTags' => '<p><br><ul><ol><li><a><b><strong>',
            'badTags' => ['style', 'script', 'applet', 'embed', 'noframes', 'noscript'],
            'badAttributes' => ['style', 'start', 'dir', 'class'],
            'newline' => '<br>',
            'keepClasses' => false,
        ];

        $config = array_merge($defaultConfig, $config);

        // Si no se debe mantener HTML
        if (!$config['keepHtml']) {
            return nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        // 1. Primero eliminar las etiquetas peligrosas con su contenido
        foreach ($config['badTags'] as $tag) {
            $pattern = '/<' . $tag . '\b[^>]*>.*?<\/' . $tag . '>/is';
            $html = preg_replace($pattern, '', $html);
        }

        // 2. Aplicar strip_tags para permitir solo ciertas etiquetas
        $html = strip_tags($html, $config['keepOnlyTags']);

        // 3. Eliminar atributos de las etiquetas restantes
        if (!empty($config['badAttributes'])) {
            $html = removeAttributes($html, $config['badAttributes'], $config['keepClasses']);
        }

        // 4. Normalizar espacios y saltos de línea
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/(\r\n|\r|\n)+/', $config['newline'], $html);

        return trim($html);
    }

    function removeAttributes(string $html, array $badAttributes, bool $keepClasses = false): string
    {
        // Si keepClasses es true, remover 'class' de los atributos a eliminar
        if ($keepClasses) {
            $badAttributes = array_diff($badAttributes, ['class']);
        }

        // Patrón para encontrar atributos en etiquetas
        foreach ($badAttributes as $attr) {
            $pattern = '/\s+' . preg_quote($attr, '/') . '\s*=\s*"[^"]*"/i';
            $html = preg_replace($pattern, '', $html);

            $pattern = '/\s+' . preg_quote($attr, '/') . '\s*=\s*\'[^\']*\'/i';
            $html = preg_replace($pattern, '', $html);

            $pattern = '/\s+' . preg_quote($attr, '/') . '\s*=\s*[^\s>]+/i';
            $html = preg_replace($pattern, '', $html);
        }

        return $html;
    }
@endphp

@foreach ($order->services as $service)
    <div class="row">
        <div class="col-12">
            <div class="border border-bottom-0 rounded-top-1 p-2 bg-secondary-subtle">
                <span class="fw-bold">Servicio - {{ $service->name }} </span>
            </div>
        </div>
        <div class="col-12">
            <div class="p-2 border border-bottom-0 border-top-0">
                <div class="form-check">
                    <input class="form-check-input border-dark" type="checkbox" value="1"
                        id="service{{ $service->id }}-can-propagate" {{ $order->contract_id ? '' : 'disabled' }}>
                    <label class="form-check-label" for="flexCheckDefault">
                        Replicar a todas las órdenes incluidas en el contrato (si corresponde a MIP).
                    </label>
                </div>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="p-2 border border-top-0 rounded-bottom-1">
                <div id="service{{ $service->id }}-text" class="smnote" style="height: 300px">
                    @if ($order->propagateByService($service->id))
                        {!! cleanHtmlSimple($order->propagateByService($service->id)->text) !!}
                    @else
                        @if ($order->setting && $order->setting->service_description)
                            {!! $order->setting->service_description !!}
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-primary btn-sm" onclick="updateDescription({{ $service->id }})">
        Actualizar descripción
    </button>
@endforeach
