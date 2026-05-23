@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <span class="text-black fw-bold fs-4">
                APARIENCIA DEL CERTIFICADO
            </span>
        </div>

        <div class="appearance-container">
            <form action="{{ route('config.appearance.update') }}" method="POST" enctype="multipart/form-data" id="appearance-form">
                @csrf
                @method('PUT')
                
                <!-- Sección de Logo -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="bi bi-image me-2"></i>Logo del certificado
                    </div>
                    <div class="settings-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <p>Suba una nueva imagen para cambiar el logo del certificado. Formato recomendado: PNG. Tamaño máximo: 2MB. Dimensiones: 300x110</p>
                                
                                <label for="logo-upload" class="custom-file-upload">
                                    <i class="bi bi-cloud-upload me-2"></i>Seleccionar Imagen
                                </label>
                                <input id="logo-upload" name="logo" type="file" accept="image/*"/>
                                
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" id="reset-logo" name="reset_logo">
                                    <label class="form-check-label" for="reset-logo">
                                        Restablecer logo predeterminado
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="logo-preview">
                                    <img src="{{ asset('images/logo_reporte.png') }}" alt="Vista previa del logo" id="logo-preview-img">
                                </div>
                                <small class="text-muted">Vista previa del logo actual</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de Marca de Agua -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="bi bi-droplet me-2"></i>Marca de Agua del certificado
                    </div>
                    <div class="settings-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <p>Suba una imagen para utilizar como marca de agua en el certificado. Formato recomendado: PNG transparente. Tamaño máximo: 2MB.</p>
                                
                                <label for="watermark-upload" class="custom-file-upload">
                                    <i class="bi bi-cloud-upload me-2"></i>Seleccionar Marca de Agua
                                </label>
                                <input id="watermark-upload" name="watermark" type="file" accept="image/*"/>
                                
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" id="reset-watermark" name="reset_watermark">
                                    <label class="form-check-label" for="reset-watermark">
                                        Restablecer marca de agua predeterminada
                                    </label>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label">Opacidad de la marca de agua:</label>
                                    <input type="range" class="form-range" id="watermark-opacity" name="watermark_opacity" 
                                           min="0" max="100" value="{{( $appearance->watermark_opacity ?? 0.1)*100 }}">
                                    <output for="watermark-opacity" id="opacity-value">{{( $appearance->watermark_opacity ?? 0.1)*100 }}%</output>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="watermark-preview">
                                    <img src="{{ asset($appearance->watermark_path ?? 'images/watermark.png') }}" alt="Vista previa de la marca de agua" id="watermark-preview-img" style="opacity: {{ ($appearance->watermark_opacity ?? 10)  }};">
                                </div>
                                <small class="text-muted">Vista previa de la marca de agua actual</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de Colores -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="bi bi-palette me-2"></i>Esquema de Colores
                    </div>
                    <div class="settings-card-body">
                        <div class="mb-3">
                            <label class="form-label">Color Principal</label>
                            <div>
                                @php
                                    $primaryColor = $appearance->primary_color ?? '#64b5f6';
                                    $colorOptions = ['#64b5f6', '#4e73df', '#36b9cc', '#1cc88a', '#f6c23e', '#e74a3b', '#6f42c1'];
                                @endphp
                                
                                @foreach ($colorOptions as $color)
                                    <div class="color-option {{ $color == $primaryColor ? 'selected' : '' }}" 
                                         style="background-color: {{ $color }};" 
                                         data-color="{{ $color }}"
                                         data-target="primary"></div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <label class="form-label">O elegir un color personalizado:</label>
                                <input type="color" class="form-control form-control-color" id="custom-primary-color" 
                                       name="primary_color" value="{{ $primaryColor }}">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Color Secundario</label>
                            <div>
                                @php
                                     $secondaryColor = $appearance->secondary_color ?? '#b0bec5';
                                    $secondaryOptions = ['#b0bec5', '#5a5c69', '#dddfeb', '#b7b9cc', '#eaecf4'];
                                @endphp
                                
                                @foreach ($secondaryOptions as $color)
                                    <div class="color-option {{ $color == $secondaryColor ? 'selected' : '' }}" 
                                         style="background-color: {{ $color }};" 
                                         data-color="{{ $color }}"
                                         data-target="secondary"></div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <label class="form-label">O elegir un color personalizado:</label>
                                <input type="color" class="form-control form-control-color" id="custom-secondary-color" 
                                       name="secondary_color" value="{{ $secondaryColor }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Vista Previa -->
                <div class="preview-section">
                    <h5 class="mb-3"><i class="bi bi-eye me-2"></i>Vista Previa</h5>
                    <div class="preview-navbar" id="preview-navbar">
                        <span class="fw-bold" style="color:black ">SERVICIOS</span>
                    </div>
                    <div>
                        <span class="fw-ligth">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Numquam magnam ipsa nulla dolores! Libero, numquam ex eveniet optio vero inventore unde nihil odio tempora fuga quae, maiores, deleniti expedita alias!</span>
                    </div>
                    <div class="p-3 border rounded">
                        <p>Esta es una vista previa de cómo se verán los cambios en el certificado.</p>
                        <button type="button" class="btn btn-primary-preview fw-bold">Encabezados</button>
                        <button type="button" class="btn btn-secondary-preview ms-2 fw-bold">Dispositivos</button>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-danger" onclick="resetForm()">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm ms-2">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .appearance-container {
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fc;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .settings-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .settings-card-header {
            background-color: #f8f9fc;
            padding: 15px 20px;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 600;
            color: #4e73df;
        }
        
        .settings-card-body {
            padding: 20px;
        }
        
        .logo-preview, .watermark-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            overflow: hidden;
            background-color: #f8f9fc;
            transition: all 0.3s;
        }
        
        .logo-preview:hover, .watermark-preview:hover {
            border-color: #4e73df;
            background-color: #eaecf4;
        }
        
        .logo-preview img, .watermark-preview img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .color-option:hover, .color-option.selected {
            transform: scale(1.1);
            border-color: #333;
        }
        
        .custom-file-upload {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4e73df;
            color: white;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 15px;
        }
        
        .custom-file-upload:hover {
            background-color: #3a5ccc;
        }
        
        /* .btn-save {
            background-color: #4e73df;
            color: white;
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
            border-radius: 5px;
        } */
        
        .btn-save:hover {
            background-color: #3a5ccc;
            transform: translateY(-2px);
        }
        
        .preview-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .preview-navbar {
            background-color: #4e73df;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .form-control-color {
            width: 60px;
            height: 40px;
        }
        
        .form-range {
            width: 100%;
        }
        
        output {
            display: inline-block;
            margin-left: 10px;
            font-weight: bold;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Previsualización de imagen seleccionada (logo)
            document.getElementById('logo-upload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('logo-preview-img').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Previsualización de imagen seleccionada (marca de agua)
            document.getElementById('watermark-upload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('watermark-preview-img').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Controlador de opacidad
            const opacitySlider = document.getElementById('watermark-opacity');
            const opacityOutput = document.getElementById('opacity-value');
            
            opacitySlider.addEventListener('input', function() {
                const opacityValue = this.value;
                opacityOutput.textContent = opacityValue + '%';
                document.getElementById('watermark-preview-img').style.opacity = opacityValue / 100;
            });
            
            // Selección de colores predefinidos
            document.querySelectorAll('.color-option').forEach(option => {
                option.addEventListener('click', function() {
                    const color = this.getAttribute('data-color');
                    const target = this.getAttribute('data-target');
                    
                    // Quitar selección anterior
                    document.querySelectorAll(`.color-option[data-target="${target}"]`).forEach(el => {
                        el.classList.remove('selected');
                    });
                    
                    // Marcar como seleccionado
                    this.classList.add('selected');
                    
                    // Actualizar el input de color
                    document.getElementById(`custom-${target}-color`).value = color;
                    
                    // Actualizar vista previa
                    updatePreview();
                });
            });
            
            // Cambio de color personalizado
            document.getElementById('custom-primary-color').addEventListener('input', updatePreview);
            document.getElementById('custom-secondary-color').addEventListener('input', updatePreview);
            
            function updatePreview() {
                const primaryColor = document.getElementById('custom-primary-color').value;
                const secondaryColor = document.getElementById('custom-secondary-color').value;
                
                // Actualizar barra de navegación en vista previa
                document.getElementById('preview-navbar').style.backgroundColor = primaryColor;
                
                // Actualizar botones en vista previa
                const primaryButtons = document.querySelectorAll('.btn-primary-preview');
                primaryButtons.forEach(btn => {
                    btn.style.backgroundColor = primaryColor;
                    btn.style.borderColor = primaryColor;
                });
                
                const secondaryButtons = document.querySelectorAll('.btn-secondary-preview');
                secondaryButtons.forEach(btn => {
                    btn.style.backgroundColor = secondaryColor;
                    btn.style.borderColor = secondaryColor;
                });
            }
            
            // Inicializar vista previa
            updatePreview();
        });
        
        function resetForm() {
            document.getElementById('appearance-form').reset();
            // Recargar la página para restaurar valores originales
            location.reload();
        }
    </script>
@endsection