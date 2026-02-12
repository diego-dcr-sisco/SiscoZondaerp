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
            <div class="col-lg-6 col-12 mb-3">
                {{-- Tipos de servicios realizados en el mes --}}
                @include('crm.charts.comercial.services')
            </div>
            <div class="col-lg-6 col-12 mb-3">
                {{-- Plagas más presentadas --}}
                @include('crm.charts.comercial.pests-donut')
            </div>
            <div class="col-lg-6 col-12 mb-3">
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

    <!-- Fullscreen Spinner -->
    <div id="fullscreen-spinner" class="d-none">
        <div class="spinner-overlay">
            <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="text-light mt-2">Procesando...</div>
        </div>
    </div>

    <style>
        #fullscreen-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #fullscreen-spinner.d-none {
            display: none !important;
        }

        .spinner-overlay {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>

    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Funciones globales para el spinner fullscreen
        function showFullscreenSpinner() {
            const spinner = document.getElementById('fullscreen-spinner');
            if (spinner) {
                spinner.classList.remove('d-none');
            }
        }

        function hideFullscreenSpinner() {
            const spinner = document.getElementById('fullscreen-spinner');
            if (spinner) {
                spinner.classList.add('d-none');
            }
        }

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

                        // Gráficas
                        const charts = [{
                                id: 'customersYearlyChart',
                                title: 'Clientes por mes',
                                description: 'Análisis mensual de nuevos clientes'
                            },
                            {
                                id: 'leadsYearlyChart',
                                title: 'Leads por mes',
                                description: 'Clientes potenciales captados'
                            },
                            {
                                id: 'monthlyServicesChart',
                                title: 'Servicios por mes',
                                description: 'Distribución de servicios por tipo'
                            },
                            {
                                id: 'pestsDonutChart',
                                title: 'Plagas más presentadas',
                                description: 'Top 10 plagas con mayor incidencia'
                            },
                            {
                                id: 'servicesProgrammedChart',
                                title: 'Tipo de servicio por mes',
                                description: 'Órdenes programadas por tipo'
                            },
                            {
                                id: 'trackingsYearlyChart',
                                title: 'Seguimientos programados',
                                description: 'Seguimientos del año'
                            },
                            {
                                id: 'servicesCompletedChart',
                                title: 'Servicios realizados',
                                description: 'Servicios completados por mes'
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

                            currentY += 8;

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
