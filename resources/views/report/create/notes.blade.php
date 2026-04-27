<div id="order-notes" class="smnote" data-autosave-type="notes" style="height: 300px">
    {!! $order->notes ?? $order->technical_observations . '<br>' . $order->comments !!}
</div>

<input type="hidden" type="text" id="notes" name="notes" value="{{ $order->notes && $order->notes != '<br><br>' ? $order->notes : $order->technical_observations . '<br>' . $order->comments }}"/>

<div class="section-action-bar">
    <span id="autosave-status-notes" class="autosave-status">Sin cambios</span>
    <div class="section-action-buttons">
        <button type="button" class="btn btn-primary btn-sm report-save-btn" onclick="updateNotes()">
            <i class="bi bi-save"></i> Guardar notas
        </button>
    </div>
</div>
