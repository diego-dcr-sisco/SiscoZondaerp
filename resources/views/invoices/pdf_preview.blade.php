<style>
    @page {
        margin: 0;
        size: letter;
    }

    .invoice-container {
        width: 21cm;
        min-height: 29.7cm;
        margin: 0 auto;
        padding: 1cm;
        box-sizing: border-box;
        background: white;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: 1px solid #adb5bd;
    }

    .header {
        display: table;
        width: 100%;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #000;
    }

    .header-row {
        display: table-row;
    }

    .logo-container {
        display: table-cell;
        width: 30%;
        vertical-align: top;
        text-align: left;
    }

    .document-info {
        display: table-cell;
        width: 70%;
        vertical-align: top;
        text-align: right;
    }

    .company-name {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .report-title {
        font-size: 6px;
        font-weight: bold;
        margin-bottom: 10px;
        text-transform: uppercase;
    }


    .sat-badge {
        display: inline-block;
        background-color: #000;
        color: #fff;
        padding: 2px 5px;
        font-size: 9px;
        font-weight: bold;
        margin-top: 5px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 15px;
    }

    .info-item {
        margin-bottom: 5px;
        font-size: 10px;
    }

    .info-label {
        font-weight: bold;
        display: inline-block;
        width: 120px;
    }

    .info-value {
        display: inline-block;
    }

    .section-title {
        font-size: 12px;
        font-weight: bold;
        margin: 10px 0 5px 0;
        padding-bottom: 3px;
        border-bottom: 1px solid #ccc;
    }

    .products-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        page-break-inside: avoid;
    }

    .products-table th {
        background-color: #f2f2f2;
        padding: 5px;
        text-align: left;
        border: 1px solid #000;
        font-weight: bold;
        font-size: 9px;
    }

    .products-table td {
        font-size: 9px;
        padding: 5px;
        border: 1px solid #000;
    }

    .totals-table {
        width: 40%;
        margin-left: auto;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .totals-table td {
        padding: 5px;
        border: 1px solid #000;
        font-size: 10px;
    }

    .totals-label {
        font-weight: bold;
        background-color: #f2f2f2;
    }

    .payment-info {
        margin-top: 15px;
        padding: 8px;
        border: 1px solid #000;
        font-size: 10px;
    }

    .legal-text {
        margin-top: 30px;
        font-size: 8px;
        text-align: justify;
    }

    .qr-container {
        text-align: center;
        margin: 15px 0;
    }

    .signatures-container {
        width: 100%;
        margin-top: 30px;
    }

    .signatures {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    .signature-box {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding-top: 40px;
    }

    .signature-line {
        width: 80%;
        border-top: 1px solid #000;
        margin: 0 auto;
    }

    .signature-title {
        margin-top: 5px;
        font-weight: bold;
        text-align: center;
        width: 100%;
        font-size: 10px;
    }

    .signature-name {
        margin-top: 4px;
        text-align: center;
        width: 100%;
        font-size: 9px;
    }

    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 8px;
        color: #666;
        border-top: 1px solid #000;
        padding-top: 8px;
    }

    .cfdi-tag {
        display: inline-block;
        background-color: #f2f2f2;
        padding: 2px 5px;
        border-radius: 3px;
        font-size: 9px;
        margin-right: 5px;
        border: 1px solid #ccc;
    }

    .warning-box {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        padding: 10px;
        margin: 15px 0;
        border-radius: 5px;
        font-size: 10px;
    }

    @media print {
        .invoice-container {
            box-shadow: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .no-print {
            display: none;
        }
    }
</style>

<div class="invoice-container">
    <div class="header">
        <div class="header-row">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" style="width: 300px; margin: 0;">
                <div class="company-name">{{ config('services.sat.business_name') }}</div>
                <div class="document-details">
                    <div>RFC: {{ config('services.sat.rfc') }}</div>
                    <div>Régimen Fiscal: {{ config('services.sat.tax_regime') }} -
                        {{ config('services.sat.tax_regime_name') }}</div>
                    <div>Teléfono: {{ config('services.company.phone') }}</div>
                    <div>Licencia Sanitaria: {{ config('services.company.sanitary_license') }} <br>
                        {{ config('services.company.sanitary_license_2') }}</div>
                </div>
            </div>
            <div class="document-info">
                <div class="document-details mt-4">
                    <div style="color:#8bc34a; font-weight:bold">
                        FACTURA - {{ $invoice->folio ?? 'A' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . '-' . now()->format('Y') }}
                    </div>
                    <div><strong style="font-size:10px;">FOLIO FISCAL (UUID)</strong><br>
                        <span style="font-size:10px;">{{ $invoice->uuid ?? '' }}</span>
                    </div>
                    <div><strong style="font-size:10px;">NO. DE SERIE DEL CERTIFICADO DEL EMISOR</strong><br>
                        <span style="font-size:10px;">{{ config('services.sat.emitter_certificate_number') }}</span>
                    </div>
                    <div><strong style="font-size:10px;">LUGAR DE EXPEDICIÓN</strong><br>
                        <span style="font-size:10px;">{{ config('services.sat.zip_code') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="info-grid">
        {{-- DATOS DEL EMISOR --}}
        <div>
            <div class="section-title">DATOS DEL EMISOR</div>
            <div class="info-item"><span class="info-label">Nombre:</span> <span
                    class="info-value">{{ config('services.sat.business_name') }}</span></div>
            <div class="info-item"><span class="info-label">RFC:</span> <span
                    class="info-value">{{ config('services.sat.rfc') }}</span></div>
            <div class="info-item"><span class="info-label">Regimen Fiscal:</span> <span
                    class="info-value">{{ config('services.sat.tax_regime_name') }}</span></div>
            <div class="info-item"><span class="info-label">Domicilio:</span> <span
                    class="info-value">{{ config('services.sat.address') }}</span></div>
        </div>
        {{-- DATOS DEL RECEPTOR --}}
        <div>
            <div class="section-title">DATOS DEL RECEPTOR</div>
            <div class="info-item"><span class="info-label">Nombre:</span> <span
                    class="info-value">{{ $invoice->customer->social_reason }}</span></div>
            <div class="info-item"><span class="info-label">RFC:</span> <span
                    class="info-value">{{ $invoice->customer->rfc ?? 'XAXX010101000' }}</span></div>
            <div class="info-item"><span class="info-label">Uso CFDI:</span> <span
                    class="info-value">{{ $invoice->customer->cfdiUsage->code ?? 'G03' }} -
                    {{ $invoice->customer->taxData->cfdiUsage->description ?? 'Gastos en general' }}</span></div>
            <div class="info-item"><span class="info-label">Domicilio:</span> <span
                    class="info-value">{{ $invoice->customer->address }}</span></div>
        </div>
    </div>

    {{-- CONCEPTOS --}}

    @include('invoices.tables.pdf_order_concepts')

    {{-- TABLA DE TOTALES --}}

    <div class="footer">
        <p>{{ config('services.sat.business_name') }} • RFC: {{ config('services.sat.rfc') }} •
            {{ config('services.sat.address') }}, CP {{ config('services.sat.zip_code') }}</p>
        <p>Teléfono: {{ config('services.company.phone') }} • www.siscoplagas.mx • contacto@zonda</p>
        <p>Este documento es una representación impresa de un Comprobante Fiscal Digital por Internet</p>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateTotal(index) {
            var quantity = parseFloat(document.querySelector('input.quantity-input[data-index="' + index + '"]')
                .value) || 1;
            var cost = parseFloat(document.querySelector('input.cost-input[data-index="' + index + '"]')
                .value) || 0;
            var discountInput = document.querySelector('input[name="services[' + index + '][discount]"]');
            var discount = discountInput ? parseFloat(discountInput.value) || 0 : 0;
            var total = ((cost - discount) * quantity).toFixed(2);
            document.getElementById('total-' + index).textContent = total;
        }

        document.querySelectorAll('.quantity-input, .cost-input').forEach(function(input) {
            input.addEventListener('input', function() {
                var index = this.getAttribute('data-index');
                updateTotal(index);
            });
        });
    });

    function updateTotal(index) {
        var quantity = parseFloat(document.querySelector('input.quantity-input[data-index="' + index + '"]').value) ||
        1;
        var cost = parseFloat(document.querySelector('input.cost-input[data-index="' + index + '"]').value) || 0;
        var discountInput = document.querySelector('input[name="services[' + index + '][discount]"]');
        var discount = discountInput ? parseFloat(discountInput.value) || 0 : 0;
        var total = ((cost - discount) * quantity).toFixed(2);
        document.getElementById('total-' + index).textContent = total;
        updateTotalsTable();
    }

    function updateTotalsTable() {
        var subtotal = 0;
        document.querySelectorAll('.quantity-input').forEach(function(qInput) {
            var index = qInput.getAttribute('data-index');
            var quantity = parseFloat(qInput.value) || 1;
            var costInput = document.querySelector('input.cost-input[data-index="' + index + '"]');
            var cost = costInput ? parseFloat(costInput.value) || 0 : 0;
            var discountInput = document.querySelector('input[name="services[' + index + '][discount]"]');
            var discount = discountInput ? parseFloat(discountInput.value) || 0 : 0;
            subtotal += (cost - discount) * quantity;
        });
        var iva = subtotal * 0.16;
        var total = subtotal + iva;
        document.getElementById('subtotal-cell').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('iva-cell').textContent = '$' + iva.toFixed(2);
        document.getElementById('total-cell').textContent = '$' + total.toFixed(2);
    }

    document.querySelectorAll('.quantity-input, .cost-input').forEach(function(input) {
        input.addEventListener('input', function() {
            var index = this.getAttribute('data-index');
            updateTotal(index);
        });
    });

    // Calculo inicial
    updateTotalsTable();
</script>
