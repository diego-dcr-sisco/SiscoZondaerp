<div id="order-notes" class="smnote" style="height: 300px">
    {!! $order->notes ?? $order->technical_observations . '<br>' . $order->comments !!}
</div>

<input type="hidden" type="text" id="notes" name="notes" value="{{ $order->notes && $order->notes != '<br><br>' ? $order->notes : $order->technical_observations . '<br>' . $order->comments }}"/>

<button type="button" class="btn btn-primary btn-sm mt-3" onclick="updateNotes()">
    Actualizar notas
</button>
