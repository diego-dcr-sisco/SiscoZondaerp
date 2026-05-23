<div class="section-title">CONCEPTOS</div>
<table class="products-table">
    <thead>
        <tr>
            <th>Cantidad</th>
            <th>Clave Prod/Serv</th>
            <th>Descripción</th>
            <th>Clave Unidad</th>
            <th>Subtotal</th>
            <th>Descuento</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoice->items as $item)
            <tr>
                <td>{{ $item->quantity ?? 1 }}</td>
                <td>{{ $item->product_code ?? '-' }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->unit_code }}</td>
                <td>${{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                <td>{{ number_format($item->discount_rate * 100, 2) }}%</td>
                <td>${{ number_format($item->subtotal, 2) }}</td>
                {{-- <td>${{ number_format($item->total ?? 0, 2) }}</td> --}}
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals-table"
    style="box-shadow: 0 2px 8px rgba(0,0,0,0.07); border-radius: 8px; overflow: hidden; background: #fff;">
    <tr>
        <td class="totals-label" style="border-top-left-radius:8px;">Subtotal:</td>
        <td style="text-align: right; font-weight: 500; background: #fafafa;">
            ${{ number_format($invoice->total - $invoice->tax, 2) }}</td>
    </tr>
    <tr>
        <td class="totals-label">IVA :</td>
        <td style="text-align: right; background: #fafafa;">${{ $invoice->tax }}</td>
    </tr>
    <tr>
        <td class="totals-label"
            style="font-size: 13px; color: #1a7f37; background: #e6f4ea; border-bottom-left-radius:8px;">Total:</td>
        <td
            style="text-align: right; font-size: 15px; font-weight: bold; color: #1a7f37; background: #e6f4ea; border-bottom-right-radius:8px;">
            ${{ number_format($invoice->total, 2) }}
        </td>
    </tr>
</table>

<div class="row" style="display: flex; gap: 24px;">
    <div class="col" style="flex: 1;">
        <div class="legal-text"><br>
            <div class="cfdi-tag">CFDI 4.0</div>
            <div class="cfdi-tag">FormaPago: {{ $invoice->paymentForm() }}</div>
            <div class="cfdi-tag mb-4">MetodoPago: {{ $invoice->paymentMethod() }}</div>
            <br>
            ESTE DOCUMENTO ES UNA REPRESENTACIÓN IMPRESA DE UN COMPROBANTE FISCAL DIGITAL (CFDI). SU VALIDEZ PUEDE SER
            VERIFICADA EN EL PORTAL DEL SAT.
            <br>
            ESTA FACTURA SE EXPIDE DE CONFORMIDAD CON LO DISPUESTO EN LA RESOLUCIÓN MISCELÁNEA FISCAL VIGENTE Y SU
            REGLAMENTACIÓN.
        </div>
    </div>
</div>
