@php
    $notesValue = $order->notes && $order->notes != '<br><br>'
        ? $order->notes
        : $order->technical_observations . '<br>' . $order->comments;

    $notesHtml = $notesValue !== strip_tags($notesValue)
        ? $notesValue
        : nl2br(e($notesValue));
@endphp

<div id="order-notes" class="smnote" data-change-type="notes" style="height: 300px">
    {!! $notesHtml !!}
</div>

<input type="hidden" id="notes" name="notes" value="{{ $notesHtml }}"/>

<div class="section-action-bar">
    <span id="change-status-notes" class="change-status">Sin cambios</span>
    <div class="section-action-buttons">
        <button type="button" class="btn btn-primary btn-sm report-save-btn" onclick="updateNotes()">
            <i class="bi bi-save"></i> Guardar notas
        </button>
    </div>
</div>
