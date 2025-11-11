<div class="row">
    <div class="col-12">
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Plaga</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Cantidad encontrada</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->pests as $i => $order_pest)
                    <tr>
                        <th scope="row">{{ $i + 1 }}</th>
                        <th scope="row">{{ $order_pest->pest->name }}</th>
                        <th scope="row">{{ $order_pest->pest->pestCategory->category }}</th>
                        <th scope="row">{{ $order_pest->total }}</th>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
