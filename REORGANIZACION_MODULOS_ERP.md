# 📊 REORGANIZACIÓN DE MÓDULOS - ERP SISCO_ZONDA

**Fecha:** 6 de abril de 2026  
**Estado:** Propuesta de Refactoría  
**Impacto:** Reorganización de código (sin cambios en BD)

---

## 📋 Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Módulos Propuestos](#módulos-propuestos)
3. [Estructura Detallada](#estructura-detallada)
4. [Cambios Necesarios](#cambios-necesarios)
5. [Mapeo de Migración](#mapeo-de-migración)
6. [Pasos de Implementación](#pasos-de-implementación)

---

## 🎯 Visión General

El ERP SISCO_ZONDA es un sistema completo con 130+ tablas. La propuesta es reorganizar el código PHP en **10 módulos temáticos coherentes** que reflejen mejor la arquitectura de negocio, manteniendo intacta la base de datos.

### Principios de la Reorganización

- ✅ **Mantener nombres de tablas BD**: Cero cambios en base de datos
- ✅ **Agrupar por dominio**: Cada módulo cubre un área de negocio específica
- ✅ **Mejorar mantenibilidad**: Código más organizado y fácil de encontrar
- ✅ **Facilitar escalabilidad**: Nuevas características por módulo
- ✅ **Preservar funcionalidad**: Sin cambios en URLs públicas

---

## 📦 Módulos Propuestos

### 1️⃣ CRM & VENTAS

**Propósito:** Gestión de relaciones con clientes y oportunidades de venta

| Aspecto | Detalles |
|---|---|
| **Controladores** | `CRMController`, `CustomerController`, `LeadController`, `QuoteController`, `TrackingController` |
| **Modelos** | `Customer`, `Lead`, `Quote`, `QuoteHistory`, `OpportunityArea`, `Tracking`, `Recommendation` |
| **Rutas** | `/crm/*`, `/customers`, `/leads`, `/customers/leads` |
| **Tablas BD** | `customer`, `lead`, `quote`, `tracking`, `opportunity_area` |
| **Características** | Dashboard CRM, calendario de agenda, cotizaciones, seguimiento de oportunidades |

---

### 2️⃣ SERVICIOS & OPERACIONES

**Propósito:** Gestión de órdenes de servicio, asignación de técnicos y ejecución operativa

| Aspecto | Detalles |
|---|---|
| **Controladores** | `OrderController`, `ServiceController`, `ScheduleController`, `OperationsController` |
| **Modelos** | `Order`, `Service`, `ServiceType`, `ServiceTracking`, `OrderTechnician`, `OrderService`, `OrderFrequency` |
| **Rutas** | `/orders`, `/planning/schedule`, `/operations`, `/services` |
| **Tablas BD** | `order`, `service`, `service_type`, `order_technician`, `service_tracking` |
| **Características** | Creación/edición de órdenes, asignación de técnicos, calendario de servicio, reportes operativos |

---

### 3️⃣ CONTROL DE CALIDAD

**Propósito:** Inspecciones, auditorías y garantía de calidad en servicios

| Aspecto | Detalles |
|---|---|
| **Controladores** | `QualityController`, `ControlPointController`, `QuestionController` |
| **Modelos** | `ControlPoint`, `Question`, `QuestionOption`, `ControlPointProduct`, `ControlPointPest`, `ControlPointQuestion` |
| **Rutas** | `/quality/customers`, `/quality/tracing`, `/quality/customer/{id}` |
| **Tablas BD** | `control_point`, `question`, `question_option`, `control_point_product` |
| **Características** | Puntos de control, auditoría de servicios, verificación de procedimientos |

---

### 4️⃣ INSTALACIONES & MONITOREO

**Propósito:** Gestión de infraestructuras, planos de ubicación y dispositivos IoT

| Aspecto | Detalles |
|---|---|
| **Controladores** | `FloorPlansController`, `ApplicationAreasController`, `DeviceController` |
| **Modelos** | `FloorPlans`, `FloorplanVersion`, `Floortype`, `ApplicationArea`, `Device`, `DeviceStates`, `DevicePest`, `DeviceProduct` |
| **Rutas** | `/customers/show/sede/{id}/floorplans`, `/customers/areas`, `/devices` |
| **Tablas BD** | `floorplans`, `floorplan_version`, `application_areas`, `device` |
| **Características** | Gestión de planos de pisos, puntos de aplicación, monitoreo de dispositivos GPS |

---

### 5️⃣ PRODUCTOS & CATÁLOGOS

**Propósito:** Gestión de catálogos: productos, plagas, biocidas y formatos

| Aspecto | Detalles |
|---|---|
| **Controladores** | `ProductController`, `PestController`, `BiocideController` (nuevo), `QuestionController` |
| **Modelos** | `ProductCatalog`, `PestCatalog`, `Biocide`, `Presentation`, `ToxicityCategories`, `PestService`, `ProductUnit`, `Purpose`, `Consumption` |
| **Rutas** | `/products`, `/pests`, `/biocides`, `/products/search` |
| **Tablas BD** | `product_catalog`, `pest_catalog`, `biocide`, `presentation`, `toxicity_categories` |
| **Características** | Catálogo de productos químicos, clasificación de plagas, datos toxicológicos |

---

### 6️⃣ INVENTARIO & ALMACÉN

**Propósito:** Control de stock, movimientos, lotes y consumo de materiales

| Aspecto | Detalles |
|---|---|
| **Controladores** | `StockController`, `LotController`, `ConsumptionController` |
| **Modelos** | `Warehouse`, `WarehouseProduct`, `WarehouseMovement`, `Lot`, `MovementType`, `MovementProduct`, `Consumption`, `ConsumptionSupply`, `ProductInput` |
| **Rutas** | `/stock`, `/lot`, `/stock/analytics/charts`, `/warehouse` |
| **Tablas BD** | `warehouse`, `lot`, `warehouse_movement`, `movement_type`, `consumption` |
| **Características** | Gestión de almacenes, seguimiento de lotes, movimientos de entrada/salida, análisis de consumo |

---

### 7️⃣ CONTRATOS & ACUERDOS

**Propósito:** Gestión de contratos de servicio, técnicos y planes de rotación

| Aspecto | Detalles |
|---|---|
| **Controladores** | `ContractController`, `RotationPlanController` |
| **Modelos** | `Contract`, `ContractType`, `ContractService`, `ContractTechnician`, `Contract_File`, `RotationPlan`, `RotationPlanChanges`, `RotationPlanProduct` |
| **Rutas** | `/contracts` (desde customers), `/rotation-plans`, `/contracts/rotation` |
| **Tablas BD** | `contract`, `contract_type`, `contract_service`, `contract_technician`, `rotation_plan` |
| **Características** | CRUD de contratos, asignación de técnicos, planes de rotación de productos |

---

### 8️⃣ FACTURACIÓN & PAGOS

**Propósito:** Emisión de facturas, gestión de pagos y nómina

| Aspecto | Detalles |
|---|---|
| **Controladores** | `InvoiceController`, `PaymentController`, `CreditNoteController`, `PayrollController`, `TimbradoController` |
| **Modelos** | `Invoice`, `InvoiceItem`, `InvoicePayment`, `Payment`, `PaymentItem`, `PaymentReminder`, `CreditNote`, `CreditNoteItem`, `CreditLine`, `Payroll`, `PayrollPerception`, `PayrollDeduction`, `PayrollOtherPayment`, `CfdiUsage` |
| **Rutas** | `/invoices`, `/payments`, `/credit-notes`, `/payroll` |
| **Tablas BD** | `invoice`, `invoice_items`, `payment`, `payroll`, `credit_note` |
| **Características** | Facturación con CFDI, gestión de cobros, notas de crédito, nómina de empleados |

---

### 9️⃣ COMPRAS & ADMINISTRACIÓN

**Propósito:** Requisiciones de compra, gestión de proveedores y configuración administrativa

| Aspecto | Detalles |
|---|---|
| **Controladores** | `PurchaseRequisitionController`, `SupplierController`, `ConfigurationController`, `UserController`, `BranchController`, `ZoneController` |
| **Modelos** | `PurchaseRequisition`, `PurchaseCustomer`, `Supplier`, `SupplierCategory`, `Administrative`, `WorkDepartment`, `Branch`, `Zone`, `ZoneType`, `Company`, `ComercialZone`, `DirectoryUser` |
| **Rutas** | `/purchase-requisitions`, `/suppliers`, `/configuration`, `/users`, `/branches`, `/zones` |
| **Tablas BD** | `purchase_requisition`, `supplier`, `administrative`, `branch`, `zone` |
| **Características** | Requisiciones de compra, catálogo de proveedores, datos administrativos |

---

### 🔟 DIRECTORIOS & PORTALES

**Propósito:** Gestión de usuarios, permisos y portales de clientes/técnicos

| Aspecto | Detalles |
|---|---|
| **Controladores** | `ClientController`, `ProfileController`, `ReportController`, `GoogleDriveController` |
| **Modelos** | `User`, `UserType`, `UserFile`, `UserCustomer`, `UserLocation`, `DirectoryManagement`, `DirectoryPermission`, `DirectoryUser`, `SimpleRole`, `EvidencePhoto` |
| **Rutas** | `/client`, `/users`, `/profile`, `/report`, `/locations/dashboard` |
| **Tablas BD** | `user`, `directory_management`, `directory_permissions`, `user_customer` |
| **Características** | Portal de clientes, portales de técnicos, gestión de permisos, firma digital |

---

## 🏗️ Estructura Detallada

### Estructura de Directorios Actual

```
app/
├── Models/                          # 160+ modelos sin organización
│   ├── User.php
│   ├── Customer.php
│   ├── Order.php
│   └── ... (todos en raíz)
├── Http/Controllers/
│   ├── Controller.php
│   ├── AppController.php
│   ├── CustomerController.php
│   ├── OrderController.php
│   ├── Api/
│   │   └── LocationController.php
│   └── Auth/
│       ├── AuthenticatedSessionController.php
│       └── ... (9 controllers de auth)
└── ... (otros directorios)
```

### Estructura Propuesta

```
app/
├── Models/
│   ├── CRM/
│   │   ├── Customer.php
│   │   ├── Lead.php
│   │   ├── Quote.php
│   │   ├── QuoteHistory.php
│   │   ├── Tracking.php
│   │   └── OpportunityArea.php
│   ├── Operations/
│   │   ├── Order.php
│   │   ├── Service.php
│   │   ├── OrderTechnician.php
│   │   ├── ServiceTracking.php
│   │   └── OrderFrequency.php
│   ├── Quality/
│   │   ├── ControlPoint.php
│   │   ├── Question.php
│   │   ├── QuestionOption.php
│   │   ├── ControlPointProduct.php
│   │   └── ControlPointPest.php
│   ├── Installations/
│   │   ├── FloorPlans.php
│   │   ├── FloorplanVersion.php
│   │   ├── ApplicationArea.php
│   │   ├── Device.php
│   │   └── DeviceStates.php
│   ├── Products/
│   │   ├── ProductCatalog.php
│   │   ├── PestCatalog.php
│   │   ├── Biocide.php
│   │   ├── Presentation.php
│   │   └── ToxicityCategories.php
│   ├── Inventory/
│   │   ├── Warehouse.php
│   │   ├── WarehouseProduct.php
│   │   ├── Lot.php
│   │   ├── WarehouseMovement.php
│   │   └── MovementType.php
│   ├── Contracts/
│   │   ├── Contract.php
│   │   ├── ContractService.php
│   │   ├── RotationPlan.php
│   │   └── RotationPlanChanges.php
│   ├── Billing/
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── Payment.php
│   │   ├── CreditNote.php
│   │   ├── Payroll.php
│   │   └── CfdiUsage.php
│   ├── Admin/
│   │   ├── User.php
│   │   ├── PurchaseRequisition.php
│   │   ├── Supplier.php
│   │   ├── Branch.php
│   │   ├── Zone.php
│   │   └── Administrative.php
│   └── System/
│       ├── Status.php
│       ├── TaxRegime.php
│       ├── Metric.php
│       └── DatabaseLog.php
├── Http/Controllers/
│   ├── Controller.php
│   ├── CRM/
│   │   ├── CRMController.php
│   │   ├── CustomerController.php
│   │   ├── LeadController.php
│   │   ├── QuoteController.php
│   │   └── TrackingController.php
│   ├── Operations/
│   │   ├── OrderController.php
│   │   ├── ServiceController.php
│   │   ├── ScheduleController.php
│   │   └── OperationsController.php
│   ├── Quality/
│   │   ├── QualityController.php
│   │   ├── ControlPointController.php
│   │   └── QuestionController.php
│   ├── Installations/
│   │   ├── FloorPlansController.php
│   │   ├── ApplicationAreasController.php
│   │   └── DeviceController.php
│   ├── Products/
│   │   ├── ProductController.php
│   │   ├── PestController.php
│   │   └── BiocideController.php
│   ├── Inventory/
│   │   ├── StockController.php
│   │   ├── LotController.php
│   │   └── ConsumptionController.php
│   ├── Contracts/
│   │   ├── ContractController.php
│   │   └── RotationPlanController.php
│   ├── Billing/
│   │   ├── InvoiceController.php
│   │   ├── PaymentController.php
│   │   ├── CreditNoteController.php
│   │   ├── PayrollController.php
│   │   └── TimbradoController.php
│   ├── Admin/
│   │   ├── AdminController.php
│   │   ├── UserController.php
│   │   ├── SupplierController.php
│   │   ├── PurchaseRequisitionController.php
│   │   └── ConfigurationController.php
│   ├── Auth/
│   │   ├── AuthenticatedSessionController.php
│   │   ├── ConfirmablePasswordController.php
│   │   └── ... (9 controllers)
│   └── Api/
│       └── LocationController.php
├── Services/
│   ├── CRM/
│   │   ├── CustomerService.php
│   │   └── QuoteService.php
│   ├── Operations/
│   │   ├── OrderService.php
│   │   └── ScheduleService.php
│   ├── ... (servicios por módulo)
│   └── System/
│       └── LogService.php
├── Http/Requests/
│   ├── CRM/
│   │   ├── StoreCustomerRequest.php
│   │   └── UpdateCustomerRequest.php
│   ├── Operations/
│   │   ├── StoreOrderRequest.php
│   │   └── UpdateOrderRequest.php
│   └── ... (validaciones por módulo)
└── ... (otros directorios sin cambios)
```

---

## 🔧 Cambios Necesarios

### 1. Estructura de Directorios

| Tarea | Acción | Prioridad |
|---|---|---|
| Crear subdirectorios | `mkdir -p app/Models/{CRM,Operations,Quality,Installations,Products,Inventory,Contracts,Billing,Admin,System}` | 🔴 ALTA |
| Crear controladores | `mkdir -p app/Http/Controllers/{CRM,Operations,Quality,Installations,Products,Inventory,Contracts,Billing,Admin}` | 🔴 ALTA |
| Crear servicios | `mkdir -p app/Services/{CRM,Operations,Quality,Installations,Products,Inventory,Contracts,Billing,Admin,System}` | 🟡 MEDIA |
| Crear requests | `mkdir -p app/Http/Requests/{CRM,Operations,Quality,Installations,Products,Inventory,Contracts,Billing,Admin}` | 🟡 MEDIA |

### 2. Namespaces

**Antes:**
```php
namespace App\Models;
namespace App\Http\Controllers;
```

**Después:**
```php
namespace App\Models\CRM;
namespace App\Http\Controllers\CRM;
namespace App\Services\CRM;
namespace App\Http\Requests\Operations;
```

### 3. Imports/Requires

Actualizar todas las referencias a modelos:

**Antes:**
```php
use App\Models\Customer;
use App\Models\Order;
```

**Después:**
```php
use App\Models\CRM\Customer;
use App\Models\Operations\Order;
```

### 4. Rutas (Mínimos cambios)

```php
// routes/web.php - No cambian los prefijos, solo los comentarios
Route::middleware(['auth', 'integral'])->group(function () {
    // CRM & VENTAS
    Route::prefix('crm')->group(function () {
        // No cambian las rutas
    });
    
    // SERVICIOS & OPERACIONES
    Route::prefix('orders')->group(function () {
        // No cambian las rutas
    });
    // ... resto igual
});
```

### 5. Tablas de Base de Datos

✅ **NO CAMBIAR** - Mantienen nombres actuales

### 6. Artisan Commands

Crear archivos de migración para la refactoría (no aplicables, solo code):

```php
// No hay cambios en database, solo en PHP
```

---

## 🗺️ Mapeo de Migración

### Modelos CRM & VENTAS

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\Customer` | `App\Models\CRM\Customer` |
| `App\Models\Lead` | `App\Models\CRM\Lead` |
| `App\Models\Quote` | `App\Models\CRM\Quote` |
| `App\Models\QuoteHistory` | `App\Models\CRM\QuoteHistory` |
| `App\Models\Tracking` | `App\Models\CRM\Tracking` |
| `App\Models\OpportunityArea` | `App\Models\CRM\OpportunityArea` |
| `App\Models\Recommendation` | `App\Models\CRM\Recommendation` |

### Modelos SERVICIOS & OPERACIONES

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\Order` | `App\Models\Operations\Order` |
| `App\Models\Service` | `App\Models\Operations\Service` |
| `App\Models\ServiceType` | `App\Models\Operations\ServiceType` |
| `App\Models\OrderTechnician` | `App\Models\Operations\OrderTechnician` |
| `App\Models\ServiceTracking` | `App\Models\Operations\ServiceTracking` |
| `App\Models\OrderFrequency` | `App\Models\Operations\OrderFrequency` |

### Modelos CONTROL DE CALIDAD

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\ControlPoint` | `App\Models\Quality\ControlPoint` |
| `App\Models\Question` | `App\Models\Quality\Question` |
| `App\Models\QuestionOption` | `App\Models\Quality\QuestionOption` |
| `App\Models\ControlPointProduct` | `App\Models\Quality\ControlPointProduct` |
| `App\Models\ControlPointPest` | `App\Models\Quality\ControlPointPest` |

### Modelos INSTALACIONES & MONITOREO

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\FloorPlans` | `App\Models\Installations\FloorPlans` |
| `App\Models\FloorplanVersion` | `App\Models\Installations\FloorplanVersion` |
| `App\Models\ApplicationArea` | `App\Models\Installations\ApplicationArea` |
| `App\Models\Device` | `App\Models\Installations\Device` |
| `App\Models\DeviceStates` | `App\Models\Installations\DeviceStates` |

### Modelos PRODUCTOS & CATÁLOGOS

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\ProductCatalog` | `App\Models\Products\ProductCatalog` |
| `App\Models\PestCatalog` | `App\Models\Products\PestCatalog` |
| `App\Models\Biocide` | `App\Models\Products\Biocide` |
| `App\Models\Presentation` | `App\Models\Products\Presentation` |
| `App\Models\ToxicityCategories` | `App\Models\Products\ToxicityCategories` |

### Modelos INVENTARIO & ALMACÉN

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\Warehouse` | `App\Models\Inventory\Warehouse` |
| `App\Models\WarehouseProduct` | `App\Models\Inventory\WarehouseProduct` |
| `App\Models\Lot` | `App\Models\Inventory\Lot` |
| `App\Models\WarehouseMovement` | `App\Models\Inventory\WarehouseMovement` |
| `App\Models\MovementType` | `App\Models\Inventory\MovementType` |

### Modelos CONTRATOS & ACUERDOS

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\Contract` | `App\Models\Contracts\Contract` |
| `App\Models\ContractService` | `App\Models\Contracts\ContractService` |
| `App\Models\ContractTechnician` | `App\Models\Contracts\ContractTechnician` |
| `App\Models\RotationPlan` | `App\Models\Contracts\RotationPlan` |
| `App\Models\RotationPlanChanges` | `App\Models\Contracts\RotationPlanChanges` |

### Modelos FACTURACIÓN & PAGOS

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\Invoice` | `App\Models\Billing\Invoice` |
| `App\Models\InvoiceItem` | `App\Models\Billing\InvoiceItem` |
| `App\Models\Payment` | `App\Models\Billing\Payment` |
| `App\Models\CreditNote` | `App\Models\Billing\CreditNote` |
| `App\Models\Payroll` | `App\Models\Billing\Payroll` |
| `App\Models\CfdiUsage` | `App\Models\Billing\CfdiUsage` |

### Modelos COMPRAS & ADMINISTRACIÓN

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\User` | `App\Models\Admin\User` |
| `App\Models\PurchaseRequisition` | `App\Models\Admin\PurchaseRequisition` |
| `App\Models\Supplier` | `App\Models\Admin\Supplier` |
| `App\Models\Branch` | `App\Models\Admin\Branch` |
| `App\Models\Zone` | `App\Models\Admin\Zone` |
| `App\Models\Administrative` | `App\Models\Admin\Administrative` |

### Modelos DIRECTORIOS & PORTALES

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\User` | `App\Models\Admin\User` |
| `App\Models\DirectoryManagement` | `App\Models\Admin\DirectoryManagement` |
| `App\Models\DirectoryPermission` | `App\Models\Admin\DirectoryPermission` |
| `App\Models\UserCustomer` | `App\Models\Admin\UserCustomer` |

### Modelos SISTEMA

| Modelo Actual | Nuevo Namespace |
|---|---|
| `App\Models\Status` | `App\Models\System\Status` |
| `App\Models\TaxRegime` | `App\Models\System\TaxRegime` |
| `App\Models\DatabaseLog` | `App\Models\System\DatabaseLog` |
| `App\Models\Metric` | `App\Models\System\Metric` |

---

## 📈 Pasos de Implementación

### Fase 1: Preparación (Día 1)

- [ ] Crear estructura de directorios
- [ ] Backup de `app/Models` y `app/Http/Controllers`
- [ ] Crear rama en Git para la refactoría
- [ ] Documentar cambios en git

```bash
# Crear directorios
mkdir -p app/Models/{CRM,Operations,Quality,Installations,Products,Inventory,Contracts,Billing,Admin,System}
mkdir -p app/Http/Controllers/{CRM,Operations,Quality,Installations,Products,Inventory,Contracts,Billing,Admin}
mkdir -p app/Services/{CRM,Operations,Quality,Installations,Products,Inventory,Contracts,Billing,Admin,System}
mkdir -p app/Http/Requests/{CRM,Operations,Quality,Installations,Products,Inventory,Contracts,Billing,Admin}

# Crear rama Git
git checkout -b refactor/module-reorganization
git add -A
git commit -m "Estructura de directorios para reorganización de módulos"
```

### Fase 2: Migración de Modelos (Días 2-3)

1. **Mover archivos de modelos**
   ```bash
   # CRM Models
   mv app/Models/Customer.php app/Models/CRM/
   mv app/Models/Lead.php app/Models/CRM/
   # ... repetir para todos los modelos
   ```

2. **Actualizar namespaces en cada modelo**
   ```php
   // Antes
   namespace App\Models;
   
   // Después
   namespace App\Models\CRM;
   use App\Models\Operations\Order;
   ```

3. **Verificar imports en relaciones**
   ```php
   public function orders()
   {
       return $this->hasMany(Order::class); // Revisar que Order esté importado correctamente
   }
   ```

### Fase 3: Migración de Controladores (Días 4-5)

1. **Mover archivos de controladores**
   ```bash
   # CRM Controllers
   mv app/Http/Controllers/CRMController.php app/Http/Controllers/CRM/
   mv app/Http/Controllers/CustomerController.php app/Http/Controllers/CRM/
   # ... repetir para todos los controladores
   ```

2. **Actualizar namespaces**
   ```php
   // Antes
   namespace App\Http\Controllers;
   
   // Después
   namespace App\Http\Controllers\CRM;
   ```

3. **Actualizar imports de modelos**
   ```php
   // Antes
   use App\Models\Customer;
   
   // Después
   use App\Models\CRM\Customer;
   ```

### Fase 4: Actualización de Rutas (Día 6)

```php
// routes/web.php
use App\Http\Controllers\CRM\CRMController;
use App\Http\Controllers\CRM\CustomerController;
use App\Http\Controllers\Operations\OrderController;
// ... importar todos los controladores del nuevo namespace
```

### Fase 5: Servicios y Requests (Día 7)

1. **Crear clases Service por módulo**
   ```php
   // app/Services/CRM/CustomerService.php
   namespace App\Services\CRM;
   use App\Models\CRM\Customer;
   ```

2. **Crear clases Request por módulo**
   ```php
   // app/Http/Requests/CRM/StoreCustomerRequest.php
   namespace App\Http\Requests\CRM;
   ```

### Fase 6: Testing y Validación (Días 8-9)

- [ ] Ejecutar test suite
- [ ] Verificar migraciones de BD
- [ ] Probar rutas en navegador
- [ ] Revisar logs de error

```bash
php artisan migrate --force
php artisan test
npm run build
```

### Fase 7: Deploy (Día 10)

- [ ] Merge a main/develop
- [ ] Deploy a staging
- [ ] Testing en staging
- [ ] Deploy a producción
- [ ] Monitoreo post-deploy

---

## 📝 Checklist de Validación

### Por Módulo

- [ ] **CRM & VENTAS**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Servicios & Operaciones**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Control de Calidad**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Instalaciones & Monitoreo**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Productos & Catálogos**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Inventario & Almacén**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Contratos & Acuerdos**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Facturación & Pagos**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Compras & Administración**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan
- [ ] **Directorios & Portales**: Modelos movidos, Controladores movidos, Rutas funcionan, Tests pasan

### Global

- [ ] No hay errores de namespace
- [ ] Todas las rutas funcionan
- [ ] Tests pasan 100%
- [ ] No hay warnings en logs
- [ ] BD íntegra (sin cambios en tablas)
- [ ] Documentación actualizada
- [ ] Code review aprobado

---

## ⚠️ Riesgos y Mitigación

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| Errores de namespace | Alta | Alta | Grep/Find & Replace automático |
| Breaks en imports externos | Media | Alta | Testing exhaustivo |
| Migraciones fallidas | Baja | Alta | Backup y rollback plan |
| Performance issues | Baja | Media | Profiling después de cambios |
| Datos corruptos | Muy baja | Crítico | NO TOCAR BD, solo PHP |

---

## 📚 Recursos Adicionales

- **Laravel Namespace Documentation**: https://laravel.com/docs/11.x/structure
- **PSR-4 Autoloading**: https://www.php-fig.org/psr/psr-4/
- **Git Workflow**: Usar feature branches con naming convention `refactor/module-*`

---

## 👥 Responsabilidades

| Rol | Tarea |
|---|---|
| **Desarrollador Backend** | Migración de modelos y controladores |
| **QA Engineer** | Testing y validación de funcionalidades |
| **DevOps** | Despliegue y monitoreo |
| **Tech Lead** | Revisión de código y aprobación |

---

## 📅 Timeline Estimado

| Fase | Duración | Inicio |
|---|---|---|
| Preparación | 1 día | 7 de abril |
| Migración de Modelos | 2 días | 8 de abril |
| Migración de Controladores | 2 días | 10 de abril |
| Actualización de Rutas | 1 día | 12 de abril |
| Servicios y Requests | 1 día | 13 de abril |
| Testing | 2 días | 14 de abril |
| Deploy | 1 día | 16 de abril |
| **TOTAL** | **~10 días** | |

---

## ✅ Conclusión

Esta reorganización:

✅ Mejora significativamente la **legibilidad y mantenibilidad del código**  
✅ Facilita la **escalabilidad y agregación de nuevas funcionalidades**  
✅ **No afecta la base de datos** (cero cambios en tablas)  
✅ **Mantiene todas las rutas públicas** idénticas  
✅ Permite **equipos paralelos** trabajar en módulos separados  
✅ Reduce el **acoplamiento entre componentes**  

**Recomendación:** Proceder con la refactoría de forma gradual, módulo por módulo.
