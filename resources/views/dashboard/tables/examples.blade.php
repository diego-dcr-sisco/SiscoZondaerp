@extends('layouts.app')

@section('content')
<div class="card mb-3" style="max-width: 800px;margin: 20px auto 0;"> 
<a href="{{ route('crm', ['status'=> 1,'page' => 1]) }}" class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a><h1>Graficas</h1>
    
    <br>
    <h2>Ordenes registradas por usuario</h2>
    <div id="chartdiv1" class="chartdiv"></div>
    <br>
    <h2>Estado de las ordenes</h2>
    <div id="chartdiv2" class="chartdiv"></div>
    <br>
    <h2>Dinero obtenido por mes</h2>
    <div id="chartdiv3" class="chartdiv"></div>
    <br>
    <h2>Areas mas comunes citadas en las ordenes</h2>
    <div id="chartdiv4" class="chartdiv"></div>
    <br>
    <h2>Ordenes terminadas a tiempo</h2>
    <div id="chartdiv5" class="chartdiv"></div>
    <div id="chartdiv6" class="chartdiv"></div>
    <div id="chartdiv7" class="chartdiv"></div>
    <div id="chartdiv8" class="chartdiv"></div>
    <div id="chartdiv9" class="chartdiv"></div>
</div>
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
        var orderData = {!! json_encode($orderData) !!};
        var orderData1 = {!! json_encode($orderData1) !!};
        var orderData2 = {!! json_encode($orderData2) !!};
        var orderData3 = {!! json_encode($orderData3) !!};
        var orderData4 = {!! json_encode($orderData4) !!};
        var orderData5 = {!! json_encode($orderData5) !!};
        am4core.useTheme(am4themes_animated);

        // Gráfico 1: Número de Órdenes por Usuario
        var chart1 = am4core.create("chartdiv1", am4charts.XYChart);
        chart1.data = orderData1;

        var categoryAxis1 = chart1.xAxes.push(new am4charts.CategoryAxis());
        categoryAxis1.dataFields.category = "administrative_name"; // Nombre del administrativo
        categoryAxis1.title.text = "Nombre Administrativo";

        var valueAxis1 = chart1.yAxes.push(new am4charts.ValueAxis());
        valueAxis1.title.text = "Nº Órdenes";

        var series1 = chart1.series.push(new am4charts.ColumnSeries());
        series1.dataFields.valueY = "order_count"; // Número de órdenes
        series1.dataFields.categoryX = "administrative_name"; // Nombre del administrativo
        series1.name = "Órdenes";
        series1.tooltipText = "{name}: [bold]{valueY}[/]";

        chart1.cursor = new am4charts.XYCursor();

        // Gráfico 2: Estado de Órdenes
        var chart2 = am4core.create("chartdiv2", am4charts.PieChart);
        chart2.data = orderData2;

        var pieSeries2 = chart2.series.push(new am4charts.PieSeries());
        pieSeries2.dataFields.value = "order_count";
        pieSeries2.dataFields.category = "status_name";
        pieSeries2.slices.template.tooltipText = "{category}: {value}";

        chart2.legend = new am4charts.Legend();

        // Gráfico 3: Dinero Obtenido por Mes
        var chart3 = am4core.create("chartdiv3", am4charts.XYChart);
        chart3.data = orderData3;

        var dateAxis3 = chart3.xAxes.push(new am4charts.DateAxis());
        dateAxis3.renderer.grid.template.location = 0;
        dateAxis3.startLocation = 0.5;
        dateAxis3.endLocation = 0.5;
        dateAxis3.title.text = "Mes";

        var valueAxis3 = chart3.yAxes.push(new am4charts.ValueAxis());
        valueAxis3.title.text = "$ Obtenido";

        var series3 = chart3.series.push(new am4charts.ColumnSeries());
        series3.dataFields.valueY = "total_price";
        series3.dataFields.dateX = "month";
        series3.tooltipText = "Mes: {dateX}\nDinero: {valueY}";
        
        chart3.cursor = new am4charts.XYCursor();
        chart3.scrollbarX = new am4core.Scrollbar();

        // Gráfico 4: Distribución de Áreas
        var chart4 = am4core.create("chartdiv4", am4charts.PieChart);
        chart4.data = orderData;

        // Gráfico 4: Áreas Más Nombradas
        var chart4 = am4core.create("chartdiv4", am4charts.PieChart);
        chart4.data = orderData4;

        var pieSeries4 = chart4.series.push(new am4charts.PieSeries());
        pieSeries4.dataFields.value = "area_count"; // Número de ocurrencias del área
        pieSeries4.dataFields.category = "areas"; // Nombre del área
        pieSeries4.slices.template.tooltipText = "{category}: {value}";

        chart4.legend = new am4charts.Legend();

        // Gráfico 5: Órdenes Completadas en Fecha Programada (Step Count Chart)
        var chart5 = am4core.create("chartdiv5", am4charts.XYChart);
        chart5.data = orderData5;

        var dateAxis5 = chart5.xAxes.push(new am4charts.DateAxis());
        dateAxis5.renderer.grid.template.location = 0;
        dateAxis5.startLocation = 0.5;
        dateAxis5.endLocation = 0.5;
        dateAxis5.title.text = "Fecha Programada";

        var valueAxis5 = chart5.yAxes.push(new am4charts.ValueAxis());
        valueAxis5.title.text = "Número de Órdenes";

        var series5 = chart5.series.push(new am4charts.StepLineSeries());
        series5.dataFields.valueY = "order_count";
        series5.dataFields.dateX = "programmed_date";
        series5.tooltipText = "Fecha: {dateX}\nÓrdenes: {valueY}";
        series5.noRisers = true;

        chart5.cursor = new am4charts.XYCursor();

        chart5.scrollbarX = new am4core.Scrollbar();

       
        });
    </script>
@endsection
