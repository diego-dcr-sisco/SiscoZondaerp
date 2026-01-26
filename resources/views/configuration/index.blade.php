@extends('layouts.app')
@section('content')
    <div class="container-fluid config-container p-0">
        <!-- Encabezado minimalista -->
        <div class="config-header px-4 py-4">
            <h1 class="fw-light mb-1">Panel de Configuración</h1>
            <p class="text-muted">Gestiona todas las opciones de tu aplicación</p>
        </div>
        <div class="config-grid p-4">
            <div class="row g-4">

                <!-- Tarjeta Reportes -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="config-item">
                        <div class="config-icon-wrapper">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div class="config-content">
                            <h3>Reportes</h3>
                            <p>Apariencia de los reportes del sistema</p>
                            <a href="{{ route('config.appearance') }}" class="config-link">
                                Gestionar <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Sistema -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="config-item">
                        <div class="config-icon-wrapper">
                            <i class="bi bi-gear-fill"></i>
                        </div>
                        <div class="config-content">
                            <h3>Sistema</h3>
                            <p>Configura aspectos generales del sistema</p>
                            <a href="#" class="config-link">
                                Gestionar <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Usuarios -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="config-item">
                        <div class="config-icon-wrapper">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="config-content">
                            <h3>Usuarios</h3>
                            <p>Gestiona usuarios y permisos de acceso</p>
                            <a href="{{ route('user.index') }}" class="config-link">
                                Gestionar <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Roles y Permisos -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="config-item">
                        <div class="config-icon-wrapper">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div class="config-content">
                            <h3>Roles y Permisos</h3>
                            <p>Controla roles y permisos del sistema</p>
                            <a href="#" class="config-link">
                                Gestionar <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta Categorías de Productos -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="config-item">
                        <div class="config-icon-wrapper">
                            <i class="bi bi-tags-fill"></i>
                        </div>
                        <div class="config-content">
                            <h3>Categorías de Productos</h3>
                            <p>Organiza tus productos por categorías</p>
                            <a href="{{ route('pest.index') }}" class="config-link">
                                Gestionar <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div> 
                
                <!-- Tarjeta Categorías de Plagas -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="config-item">
                        <div class="config-icon-wrapper">
                            <i class="bi bi-bug-fill"></i>
                        </div>
                        <div class="config-content">
                            <h3>Categorías de Plagas</h3>
                            <p>Clasifica y gestiona tipos de plagas</p>
                            <a href="#" class="config-link">
                                Gestionar <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>

    <style>
        .config-container {
            background-color: #fafbfc;
            min-height: 100vh;
        }
        
        .config-header {
            background: white;
            border-bottom: 1px solid #eaecef;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .config-header h1 {
            font-size: 1.8rem;
            color: #24292e;
        }
        
        .config-grid {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .config-item {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid #e1e4e8;
        }
        
        .config-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: #d1d5da;
        }
        
        .config-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background-color: #f6f8fa;
            color: #0366d6;
            font-size: 1.5rem;
        }
        
        .config-content h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #24292e;
        }
        
        .config-content p {
            color: #6a737d;
            margin-bottom: 20px;
            line-height: 1.5;
            flex-grow: 1;
        }
        
        .config-link {
            display: inline-flex;
            align-items: center;
            color: #0366d6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .config-link:hover {
            color: #0256b3;
        }
        
        .config-link i {
            margin-left: 4px;
            transition: transform 0.2s;
        }
        
        .config-link:hover i {
            transform: translateX(4px);
        }
        
        /* Colores específicos para cada ítem */
        .config-item:nth-child(1) .config-icon-wrapper {
            color: #cf222e;
            background-color: #ffebe9;
        }
        
        .config-item:nth-child(2) .config-icon-wrapper {
            color: #0a3069;
            background-color: #dff7ff;
        }
        
        .config-item:nth-child(3) .config-icon-wrapper {
            color: #8250df;
            background-color: #fbefff;
        }
        
        .config-item:nth-child(4) .config-icon-wrapper {
            color: #bf3989;
            background-color: #ffeff7;
        }
        
        .config-item:nth-child(5) .config-icon-wrapper {
            color: #116329;
            background-color: #dafbe1;
        }
        
        .config-item:nth-child(6) .config-icon-wrapper {
            color: #953800;
            background-color: #fff1e5;
        }
        
        @media (max-width: 768px) {
            .config-grid {
                padding: 16px;
            }
            
            .config-item {
                padding: 20px;
            }
        }
    </style>
@endsection