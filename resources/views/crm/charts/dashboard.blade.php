@extends('layouts.app')
@section('content')
    @php
        use Carbon\Carbon;
    @endphp
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    <div class="container-fluid">
        {{-- Botón para generar PDF --}}
        <div class="d-flex align-items-center justify-content-between border-bottom ps-4 p-2">
            <div class="d-flex align-items-center">
                <!-- <a href="{{ route('order.index') }}" class="text-decoration-none pe-3">
                                                                <i class="bi bi-arrow-left fs-4"></i>
                                                            </a> -->
                <a href="#" onclick="window.history.back(); return false;" class="text-decoration-none pe-3">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <span class="text-black fw-bold fs-4">
                    Estadisticas, graficas e indicadores del cliente
                </span>
            </div>
            <div class="pe-4">
                <button class="btn btn-dark btn-sm" id="generatePdfBtn">
                    <i class="bi bi-file-pdf-fill"></i> Generar Reporte
                </button>

                <span id="pdfLoading" class="ms-2 text-muted" style="display: none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Generando reporte...
                </span>
            </div>
        </div>

        <div class="row p-3">
            <div class="col-lg-6 col-12 mb-4">
                {{-- Clientes por mes (todo el año) --}}
                @include('crm.charts.comercial.yearly-customers')
            </div>
            <div class="col-lg-6 col-12 mb-4">
                {{-- Leads por mes (todo el año) --}}
                @include('crm.charts.comercial.yearly-leads')
            </div>
            <div class="col-lg-4 col-12 mb-3">
                {{-- Tipos de servicios realizados en el mes --}}
                @include('crm.charts.comercial.services')
            </div>
            <div class="col-lg-4 col-12 mb-3">
                {{-- Plagas más presentadas --}}
                @include('crm.charts.comercial.pests-donut')
            </div>
            <div class="col-lg-4 col-12 mb-3">
                {{-- Tipo de servicio por mes (órdenes generadas) --}}
                @include('crm.charts.comercial.services-programmed')
            </div>
            <div class="col-lg-6 col-12 mb-3">
                {{-- Seguimientos programados por mes --}}
                @include('crm.charts.comercial.trackings-by-month')
            </div>

            <div class="col-lg-6 col-12 mb-3">
                {{-- Servicios completados por mes --}}
                @include('crm.charts.comercial.yearly-services-completed')
            </div>

        </div>
    </div>

    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Función para cargar imagen como base64
        function loadImageAsBase64(url) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                };
                img.onerror = () => resolve(null);
                img.src = url;
            });
        }

        // Función para generar análisis descriptivo de las gráficas
        function generateChartAnalysis(chartId, chartTitle) {
            const canvas = document.getElementById(chartId);
            if (!canvas) return '';

            // Obtener la instancia del chart
            const chartInstance = Chart.getChart(canvas);
            if (!chartInstance || !chartInstance.data) return '';

            const datasets = chartInstance.data.datasets;
            const labels = chartInstance.data.labels;

            if (!datasets || datasets.length === 0) return '';

            // Análisis para gráficas de línea/barra con series temporales
            if (chartInstance.config.type === 'line' || chartInstance.config.type === 'bar') {
                // Sumar todos los datasets para obtener totales por periodo
                const totals = labels.map((label, index) => {
                    return datasets.reduce((sum, dataset) => {
                        const value = dataset.data[index] || 0;
                        return sum + value;
                    }, 0);
                });

                const total = totals.reduce((a, b) => a + b, 0);
                const avg = total / totals.length;

                // Encontrar máximo y mínimo
                const maxValue = Math.max(...totals);
                const minValue = Math.min(...totals);
                const maxIndex = totals.indexOf(maxValue);
                const minIndex = totals.indexOf(minValue);
                const maxLabel = labels[maxIndex];
                const minLabel = labels[minIndex];

                // Calcular tendencia
                let trend = 'estable';
                let increases = 0, decreases = 0;
                for (let i = 1; i < totals.length; i++) {
                    if (totals[i] > totals[i - 1]) increases++;
                    else if (totals[i] < totals[i - 1]) decreases++;
                }
                if (increases > decreases * 1.5) trend = 'creciente';
                else if (decreases > increases * 1.5) trend = 'descendente';

                // Variación entre último y penúltimo periodo con datos
                let variationText = '';
                const nonZeroIndices = totals.map((v, i) => v > 0 ? i : -1).filter(i => i !== -1);
                if (nonZeroIndices.length >= 2) {
                    const lastIndex = nonZeroIndices[nonZeroIndices.length - 1];
                    const prevIndex = nonZeroIndices[nonZeroIndices.length - 2];
                    const lastValue = totals[lastIndex];
                    const prevValue = totals[prevIndex];
                    
                    if (prevValue > 0) {
                        const variation = ((lastValue - prevValue) / prevValue * 100).toFixed(1);
                        const changeType = variation > 0 ? 'incremento' : 'disminución';
                        variationText = `En comparación con ${labels[prevIndex]}, se registró un ${changeType} del ${Math.abs(variation)}%. `;
                    }
                }

                // Generar insight
                let insight = '';
                if (maxValue > avg * 1.5) {
                    insight = `Se destaca un pico significativo en ${maxLabel} que supera el promedio en ${((maxValue / avg - 1) * 100).toFixed(0)}%.`;
                } else if (trend === 'creciente') {
                    insight = 'La tendencia general muestra crecimiento sostenido.';
                } else if (trend === 'descendente') {
                    insight = 'Se observa una tendencia a la baja que requiere atención.';
                } else {
                    insight = 'Los valores se mantienen relativamente estables.';
                }

                return `Durante el periodo analizado se observa una tendencia ${trend}, alcanzando su punto máximo en ${maxLabel} con ${maxValue} registros. ${variationText}El periodo con menor actividad fue ${minLabel} (${minValue} registros). ${insight}`;
            }

            // Análisis para gráficas donut/pie
            if (chartInstance.config.type === 'doughnut' || chartInstance.config.type === 'pie') {
                const data = datasets[0].data.map(v => Number(v) || 0); // Convertir a números
                const total = data.reduce((a, b) => a + b, 0);
                
                if (total === 0) return 'No se registraron datos para el periodo seleccionado.';

                const maxValue = Math.max(...data);
                const maxIndex = data.indexOf(maxValue);
                const maxLabel = labels[maxIndex] || 'N/A';
                const percentage = ((maxValue / total) * 100).toFixed(1);

                // Encontrar segundo lugar usando un enfoque diferente
                const dataWithIndices = data.map((value, index) => ({ value, index }))
                    .sort((a, b) => b.value - a.value);
                
                const secondItem = dataWithIndices[1] || dataWithIndices[0];
                const secondValue = secondItem.value;
                const secondIndex = secondItem.index;
                const secondLabel = labels[secondIndex] || 'N/A';
                const secondPercentage = total > 0 ? ((secondValue / total) * 100).toFixed(1) : '0.0';

                // Calcular distribución
                let distribution = 'equilibrada';
                const maxPercent = parseFloat(percentage);
                if (maxPercent > 50) distribution = 'concentrada';
                else if (maxPercent < 25) distribution = 'diversificada';

                return `La distribución de los datos muestra que ${maxLabel} representa el ${percentage}% del total con ${maxValue} registros, siendo la categoría predominante. Le sigue ${secondLabel} con ${secondPercentage}% (${secondValue} registros). La distribución general es ${distribution}.`;
            }

            return '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const generatePdfBtn = document.getElementById('generatePdfBtn');
            const pdfLoading = document.getElementById('pdfLoading');

            if (generatePdfBtn) {
                generatePdfBtn.addEventListener('click', async function() {
                    generatePdfBtn.disabled = true;
                    pdfLoading.style.display = 'inline-block';

                    try {
                        // Cargar el logo
                        const logoData = await loadImageAsBase64('/images/logo.png');

                        // Esperar a que todas las gráficas estén renderizadas
                        await new Promise(resolve => setTimeout(resolve, 1000));

                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF('p', 'mm', 'letter');

                        const pageWidth = pdf.internal.pageSize.getWidth();
                        const pageHeight = pdf.internal.pageSize.getHeight();
                        const margin = 15;
                        const contentWidth = pageWidth - (margin * 2);

                        let currentY = margin;

                        // Header
                        const headerStartY = 10; // Iniciar header 10mm más abajo
                        pdf.setFillColor(255, 255, 255); // Fondo blanco
                        pdf.rect(0, headerStartY, pageWidth, 20, 'F');

                        // Borde inferior del header
                        // pdf.setDrawColor(10, 41, 134); // #0A2986
                        // pdf.setLineWidth(0.5);
                        // pdf.line(0, headerStartY + 20, pageWidth, headerStartY + 20);

                        // Agregar logo en el header (lado derecho)
                        if (logoData) {
                            try {
                                pdf.addImage(logoData, 'PNG', pageWidth - margin - 25, headerStartY + 3,
                                    20, 15);
                            } catch (error) {
                                console.error('Error agregando logo:', error);
                            }
                        }

                        // Texto del lado izquierdo
                        pdf.setTextColor(1, 38, 64); // #012640
                        pdf.setFontSize(14);
                        pdf.setFont(undefined, 'bold');
                        pdf.text('Reporte de Estadísticas - CRM', margin, headerStartY + 10);

                        pdf.setFontSize(8);
                        pdf.setFont(undefined, 'normal');
                        pdf.setTextColor(100, 100, 100);
                        pdf.text('Sistema de Gestión Empresarial SISCO ZONDA', margin, headerStartY +
                            16);

                        currentY = headerStartY + 28;

                        // Información del reporte
                        pdf.setTextColor(0, 0, 0);
                        pdf.setFontSize(10);
                        pdf.setFont(undefined, 'bold');
                        pdf.text('Fecha de generación:', margin, currentY);
                        pdf.setFont(undefined, 'normal');
                        pdf.text(new Date().toLocaleString('es-MX'), margin + 50, currentY);

                        currentY += 5;
                        pdf.setFont(undefined, 'bold');
                        pdf.text('Usuario:', margin, currentY);
                        pdf.setFont(undefined, 'normal');
                        pdf.text('{{ auth()->user()->name ?? 'Sistema' }}', margin + 50, currentY);

                        currentY += 5;
                        pdf.setFont(undefined, 'bold');
                        pdf.text('Periodo:', margin, currentY);
                        pdf.setFont(undefined, 'normal');
                        pdf.text('Año {{ now()->year }}', margin + 50, currentY);

                        currentY += 12;

                        // Obtener valores de los filtros
                        const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                        
                        const getFilterText = (yearSelector, monthSelector = null) => {
                            const yearEl = document.getElementById(yearSelector);
                            const year = yearEl ? yearEl.value : '';
                            
                            if (monthSelector) {
                                const monthEl = document.getElementById(monthSelector);
                                const month = monthEl ? monthEl.value : '';
                                const monthName = month ? monthNames[parseInt(month) - 1] : '';
                                return `${monthName} ${year}`;
                            }
                            return `Año ${year}`;
                        };

                        // Gráficas
                        const charts = [{
                                id: 'customersYearlyChart',
                                title: 'Clientes por mes',
                                description: 'Análisis mensual de nuevos clientes',
                                period: getFilterText('yearSelectorCustomers')
                            },
                            {
                                id: 'leadsYearlyChart',
                                title: 'Leads por mes',
                                description: 'Clientes potenciales captados',
                                period: getFilterText('yearSelectorLeads')
                            },
                            {
                                id: 'monthlyServicesChart',
                                title: 'Servicios por mes',
                                description: 'Distribución de servicios por tipo',
                                period: getFilterText('yearServicesSelector', 'monthServicesSelector')
                            },
                            {
                                id: 'pestsDonutChart',
                                title: 'Plagas más presentadas',
                                description: 'Top 10 plagas con mayor incidencia',
                                period: getFilterText('yearPestsSelector', 'monthPestsSelector')
                            },
                            {
                                id: 'servicesProgrammedChart',
                                title: 'Tipo de servicio por mes',
                                description: 'Órdenes programadas por tipo',
                                period: getFilterText('yearServicesProgrammedSelector', 'monthServicesProgrammedSelector')
                            },
                            {
                                id: 'trackingsYearlyChart',
                                title: 'Seguimientos programados',
                                description: 'Seguimientos del año',
                                period: getFilterText('yearSelectorTrackings')
                            },
                            {
                                id: 'servicesCompletedChart',
                                title: 'Servicios realizados',
                                description: 'Servicios completados por mes',
                                period: getFilterText('yearSelectorServicesCompleted')
                            }
                        ];

                        for (let i = 0; i < charts.length; i++) {
                            const chart = charts[i];
                            const canvas = document.getElementById(chart.id);

                            if (!canvas) continue;

                            // Verificar si necesitamos nueva página
                            if (currentY > pageHeight - 100) {
                                pdf.addPage();
                                currentY = margin;
                            }

                            // Título de la gráfica
                            pdf.setFontSize(14);
                            pdf.setFont(undefined, 'bold');
                            pdf.setTextColor(1, 38, 64);
                            pdf.text(chart.title, margin, currentY);

                            currentY += 6;

                            // Descripción
                            pdf.setFontSize(9);
                            pdf.setFont(undefined, 'normal');
                            pdf.setTextColor(100, 100, 100);
                            pdf.text(chart.description, margin, currentY);

                            currentY += 5;

                            // Periodo filtrado
                            if (chart.period) {
                                pdf.setFontSize(8);
                                pdf.setFont(undefined, 'bold');
                                pdf.setTextColor(10, 41, 134); // Color corporativo
                                pdf.text('Periodo: ' + chart.period, margin, currentY);
                                currentY += 6;
                            }

                            // Generar y agregar análisis descriptivo
                            const analysis = generateChartAnalysis(chart.id, chart.title);
                            if (analysis) {
                                // Verificar si necesitamos nueva página antes del análisis
                                if (currentY > pageHeight - 120) {
                                    pdf.addPage();
                                    currentY = margin;
                                }

                                pdf.setFontSize(9);
                                pdf.setFont(undefined, 'normal');
                                pdf.setTextColor(40, 40, 40);
                                
                                // Dividir el texto en líneas que quepan en el ancho
                                const maxWidth = contentWidth - 10;
                                const lines = pdf.splitTextToSize(analysis, maxWidth);
                                
                                // Agregar fondo claro para el análisis
                                const textHeight = lines.length * 4;
                                pdf.setFillColor(245, 247, 250); // Gris muy claro
                                pdf.roundedRect(margin, currentY - 2, contentWidth, textHeight + 4, 2, 2, 'F');
                                
                                // Agregar el texto
                                pdf.text(lines, margin + 5, currentY + 2);
                                currentY += textHeight + 8;
                            }

                            // Agregar imagen de la gráfica
                            try {
                                const imgData = canvas.toDataURL('image/png', 1.0);
                                const imgWidth = contentWidth;
                                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                                // Limitar altura máxima
                                const maxHeight = 80;
                                const finalHeight = Math.min(imgHeight, maxHeight);
                                const finalWidth = (canvas.width * finalHeight) / canvas.height;

                                pdf.addImage(imgData, 'PNG', margin, currentY, finalWidth, finalHeight);
                                currentY += finalHeight + 15;
                            } catch (error) {
                                console.error('Error adding chart:', chart.id, error);
                            }
                        }

                        // Footer en todas las páginas
                        const totalPages = pdf.internal.getNumberOfPages();
                        for (let i = 1; i <= totalPages; i++) {
                            pdf.setPage(i);
                            pdf.setFontSize(8);
                            pdf.setTextColor(100, 100, 100);
                            pdf.text(
                                `Página ${i} de ${totalPages} | Generado automáticamente por SISCO ZONDA ERP`,
                                pageWidth / 2,
                                pageHeight - 10, {
                                    align: 'center'
                                }
                            );
                        }

                        // Descargar PDF
                        const fileName =
                            `reporte_estadisticas_${new Date().toISOString().slice(0, 10)}.pdf`;
                        pdf.save(fileName);

                    } catch (error) {
                        console.error('Error generando PDF:', error);
                        alert('Error al generar el PDF. Por favor, intente nuevamente.');
                    } finally {
                        generatePdfBtn.disabled = false;
                        pdfLoading.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endsection
