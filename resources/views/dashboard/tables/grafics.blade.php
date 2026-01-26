@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif
    <style>
        #chartdiv {
        width: 100%;
        height: 500px;
        }
    </style>
    <!-- AmCharts Resources -->
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
    <body>
    <div class="row p-3 border-bottom">
        <a href="{{ route('crm', ['status'=> 1,'page' => 1]) }}" class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
        <h1 class="col-auto fs-2 fw-bold m-0">Clientes Registrados por Mes</h1>
        <div class="card">
            <div class="card-body">
                <div id="chartdiv" style="width: 100%; height: 400px;"></div>
            </div>
        </div>
    </div>
<script>
    var chartData = {!! json_encode($customerData) !!};
    
    // Create chart instance
    var chart = am4core.create("chartdiv", am4charts.XYChart);
    
    // Add data
    chart.data = chartData;
    
    // Create axes
    var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "month";
    categoryAxis.renderer.grid.template.location = 0;
    categoryAxis.renderer.minGridDistance = 30;

    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    var series = chart.series.push(new am4charts.ColumnSeries());
    series.dataFields.valueY = "total";
    series.dataFields.categoryX = "month";
    series.name = "Clientes registrados por mes";
    series.columns.template.tooltipText = "Mes: [bold]{categoryX}[/]\n Clientes: [bold]{valueY}[/]";
    series.columns.template.fillOpacity = .8;

    // Add cursor
    chart.cursor = new am4charts.XYCursor();
</script>

</body>
@endsection