@extends('layouts.app')

@section('content')
    <style>
        .import-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .import-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .import-header {
            background: linear-gradient(135deg, #012640 0%, #0A2986 100%);
            color: white;
            padding: 25px;
            border-radius: 8px 8px 0 0;
        }

        .import-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .import-header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .import-body {
            padding: 30px;
        }

        .upload-zone {
            border: 2px dashed #0A2986;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f5f7fb;
            cursor: pointer;
        }

        .upload-zone:hover {
            border-color: #512A87;
            background-color: #e8ecf5;
        }

        .upload-zone.dragover {
            border-color: #512A87;
            background-color: #e8ecf5;
        }

        .upload-icon {
            font-size: 48px;
            color: #0A2986;
            margin-bottom: 15px;
        }

        .upload-text {
            margin: 15px 0;
        }

        .upload-text p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }

        .upload-text strong {
            color: #012640;
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .file-input {
            display: none;
        }

        .btn-upload {
            background: linear-gradient(135deg, #0A2986 0%, #512A87 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(10, 41, 134, 0.3);
        }

        .file-name {
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            border-radius: 4px;
            display: none;
            color: #2e7d32;
            font-size: 14px;
        }

        .file-name.show {
            display: block;
        }

        .info-box {
            background-color: #f5f7fb;
            border-left: 4px solid #0A2986;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
            line-height: 1.6;
        }

        .info-box strong {
            color: #012640;
            display: block;
            margin-bottom: 8px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .info-box li {
            margin: 5px 0;
        }

        .alert {
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 20px;
        }

        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px 20px;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 12px 20px;
        }

        .import-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .stat-box {
            background-color: #f5f7fb;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border-top: 4px solid #0A2986;
        }

        .stat-box.errors {
            border-top-color: #DD513A;
        }

        .stat-box h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
        }

        .stat-box .number {
            font-size: 24px;
            font-weight: 700;
            color: #012640;
        }

        .stat-box.errors .number {
            color: #DD513A;
        }

        .errors-list {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        .errors-list ul {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #856404;
        }

        .errors-list li {
            margin: 5px 0;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #0A2986;
            color: white;
        }

        .btn-primary:hover {
            background-color: #082057;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>

    <div class="import-wrapper">
        <div class="import-card">
            <div class="import-header">
                <h2><i class="bi bi-upload"></i> Importar CSV</h2>
                <p>Carga un archivo con datos de Registro Diario y Prospectos Comerciales</p>
            </div>

            <div class="import-body">
                @if ($errors->any())
                    <div class="alert alert-error">
                        <strong>Error:</strong>
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        <strong>Error:</strong>
                        {{ session('error') }}
                    </div>

                    @if (session('import_result'))
                        @php
                            $result = session('import_result');
                        @endphp

                        @if (!empty($result['daily_tracking']['errors']))
                            <div class="errors-list">
                                <strong>Errores en Registro Diario CRM:</strong>
                                <ul>
                                    @foreach (array_slice($result['daily_tracking']['errors'], 0, 10) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    @if (count($result['daily_tracking']['errors']) > 10)
                                        <li>... y {{ count($result['daily_tracking']['errors']) - 10 }} errores más</li>
                                    @endif
                                </ul>
                            </div>
                        @endif

                        @if (!empty($result['commercial_prospects']['errors']))
                            <div class="errors-list">
                                <strong>Errores en Prospectos Comerciales:</strong>
                                <ul>
                                    @foreach (array_slice($result['commercial_prospects']['errors'], 0, 10) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    @if (count($result['commercial_prospects']['errors']) > 10)
                                        <li>... y {{ count($result['commercial_prospects']['errors']) - 10 }} errores más</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    @endif
                @endif

                @if (session('success') && session('import_result'))
                    <div class="alert alert-success">
                        <strong>¡Importación completada exitosamente!</strong>
                        <p>{{ session('success') }}</p>
                    </div>

                    @php
                        $result = session('import_result');
                    @endphp

                    <div class="import-stats">
                        <div class="stat-box">
                            <h4>Registro Diario CRM</h4>
                            <div class="number">{{ $result['daily_tracking']['inserted'] }}</div>
                            <small>registros insertados</small>
                        </div>
                        <div class="stat-box">
                            <h4>Prospectos Comerciales</h4>
                            <div class="number">{{ $result['commercial_prospects']['inserted'] }}</div>
                            <small>registros insertados</small>
                        </div>
                    </div>

                    @if (!empty($result['daily_tracking']['errors']) || !empty($result['commercial_prospects']['errors']))
                        @php
                            $totalErrors = count($result['daily_tracking']['errors']) + count($result['commercial_prospects']['errors']);
                        @endphp

                        <div class="import-stats" style="margin-top: 15px;">
                            <div class="stat-box errors">
                                <h4>Total de Errores</h4>
                                <div class="number">{{ $totalErrors }}</div>
                                <small>Revisa los detalles abajo</small>
                            </div>
                        </div>

                        @if (!empty($result['daily_tracking']['errors']))
                            <div class="errors-list">
                                <strong>Errores en Registro Diario CRM:</strong>
                                <ul>
                                    @foreach (array_slice($result['daily_tracking']['errors'], 0, 10) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    @if (count($result['daily_tracking']['errors']) > 10)
                                        <li>... y {{ count($result['daily_tracking']['errors']) - 10 }} errores más</li>
                                    @endif
                                </ul>
                            </div>
                        @endif

                        @if (!empty($result['commercial_prospects']['errors']))
                            <div class="errors-list">
                                <strong>Errores en Prospectos Comerciales:</strong>
                                <ul>
                                    @foreach (array_slice($result['commercial_prospects']['errors'], 0, 10) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    @if (count($result['commercial_prospects']['errors']) > 10)
                                        <li>... y {{ count($result['commercial_prospects']['errors']) - 10 }} errores más</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    @endif

                    <div class="btn-group">
                        <a href="{{ route('crm.daily-tracking.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-right"></i> Ver Registros
                        </a>
                        <a href="{{ route('crm.daily-tracking.import-form') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-repeat"></i> Importar Otro Archivo
                        </a>
                    </div>
                @else
                    <div class="info-box">
                        <strong>📋 Instrucciones para la importación:</strong>
                        <ul>
                            <li><strong>Archivo:</strong> CSV con datos de seguimiento diario</li>
                            <li><strong>Formato:</strong> .csv, máximo 5MB</li>
                            <li><strong>Validación:</strong> Fechas, números y campos obligatorios son validados automáticamente</li>
                            <li><strong>Duplicados:</strong> Se actualiza si el registro ya existe</li>
                        </ul>
                    </div>

                    <form action="{{ route('crm.daily-tracking.import-excel') }}" method="POST" enctype="multipart/form-data"
                        id="uploadForm" class="mt-3">
                        @csrf

                        <div class="upload-zone" id="uploadZone">
                            <div class="upload-icon">
                                <i class="bi bi-cloud-upload"></i>
                            </div>
                            <div class="upload-text">
                                <strong>Arrastra tu archivo aquí o haz clic</strong>
                                <p>Soporta formatos: CSV (.csv)</p>
                                <p style="font-size: 12px; color: #999;">Tamaño máximo: 5MB</p>
                            </div>
                            <input type="file" name="excel_file" id="fileInput" class="file-input" accept=".csv,text/csv"
                                required>
                            <button type="button" class="btn-upload">Seleccionar Archivo</button>
                            <div class="file-name" id="fileName"></div>
                            <div class="text-danger mt-2" id="fileError" style="display: none; font-size: 13px;">
                                Por favor, selecciona un archivo CSV antes de importar.
                            </div>
                        </div>

                        <div class="btn-group" style="justify-content: flex-end;">
                            <a href="{{ route('crm.daily-tracking.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="bi bi-upload"></i> Importar
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const fileNameDisplay = document.getElementById('fileName');
        const fileError = document.getElementById('fileError');
        const submitBtn = document.getElementById('submitBtn');
        const uploadBtn = document.querySelector('.btn-upload');
        const uploadForm = document.getElementById('uploadForm');

        function updateSelectedFile(file) {
            if (!file) {
                fileNameDisplay.textContent = '';
                fileNameDisplay.classList.remove('show');
                fileError.style.display = 'none';
                submitBtn.disabled = true;
                return;
            }

            fileNameDisplay.textContent = '✓ ' + file.name;
            fileNameDisplay.classList.add('show');
            fileError.style.display = 'none';
            submitBtn.disabled = false;
        }

        // Mostrar input file cuando se hace clic
        uploadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            fileInput.click();
        });

        // Seleccionar archivo
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            updateSelectedFile(file);
        });

        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                fileInput.files = dt.files;
                updateSelectedFile(files[0]);
            }
        });

        uploadForm.addEventListener('submit', (e) => {
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                submitBtn.disabled = true;
                fileError.style.display = 'block';
            }
        });
    </script>
@endsection
