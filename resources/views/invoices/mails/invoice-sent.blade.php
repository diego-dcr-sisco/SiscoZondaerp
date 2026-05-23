<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura Enviada</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #333;">Factura #{{ $invoice->id }}</h2>
        
        <p>Estimado/a 
            @if($order && $order->customer)
                {{ $order->customer->tax_name ?? $order->customer->name }},
            @elseif($contract && $contract->customer)
                {{ $contract->customer->tax_name ?? $contract->customer->name }},
            @else
                Cliente,
            @endif
        </p>
        
        <p>Nos complace enviarle su factura con los siguientes detalles:</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Detalles de la Factura</h3>
            <p><strong>Número de Factura:</strong> #{{ $invoice->id }}</p>
            <p><strong>Fecha:</strong> {{ $invoice->created_at->format('d/m/Y') }}</p>
            <p><strong>Total:</strong> ${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</p>
            @if($order)
                <p><strong>Orden:</strong> {{ $order->folio }}</p>
            @endif
            @if($contract)
                <p><strong>Contrato:</strong> #{{ $contract->id }}</p>
            @endif
        </div>
        
        <p>En los archivos adjuntos encontrará:</p>
        <ul>
            @if($invoice->pdf_file)
                <li>Factura en formato PDF</li>
            @endif
            @if($invoice->xml_file)
                <li>Archivo XML de la factura</li>
            @endif
        </ul>
        
        <p>Si tiene alguna pregunta sobre esta factura, no dude en contactarnos.</p>
        
        <p>Gracias por su preferencia.</p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        <p style="color: #666; font-size: 12px;">
            Este es un correo automático, por favor no responda a esta dirección.
        </p>
    </div>
</body>
</html>