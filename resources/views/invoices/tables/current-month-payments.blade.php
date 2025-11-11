<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($currentMonthPayments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->customer->name }}</td>
                <td>${{ $payment->amount }}</td>
                <td>{{ $payment->date }}</td>
                <td>
                    @if ($payment->status == 'pagado')
                        <span class="badge bg-success">Pagado</span>
                    @elseif ($payment->status == 'pendiente' || $payment->status == 'parcial')
                        <span class="badge bg-warning">Pendiente</span>
                    @else
                        <span class="badge bg-danger">Moroso</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $currentMonthPayments->links('pagination::bootstrap-5') }}

