<?php
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ControlPointController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\FloorPlansController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PestController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CRMController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GraphicController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\RotationPlanController;
use App\Http\Controllers\OpportunityAreaController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ApplicationAreasController;
use App\Http\Controllers\ComercialZoneController;
use App\Http\Controllers\ConsumptionController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\TimbradoController;

use Illuminate\Support\Facades\Route;

/*Route::get('/', function () {
    return view('/auth/login');
});*/

/*Route::middleware(['auth', 'single.session'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});*/



Route::middleware(['auth', 'single.session'])->group(function () {
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', [PagesController::class, 'dashboard'])->name('dashboard');
    Route::get('/RRHH/{section}', [PagesController::class, 'rrhh'])->name('rrhh');
    Route::get('/dashboard/stock', [PagesController::class, 'stock'])->name('dashboard.stock.');
});
// DASHBOARD

// CRM
/*Route::prefix('crm')->middleware(['auth', 'single.session', 'can:integral'])->group(function () {
    Route::get('/schedule', [PagesController::class, 'planning'])->name('planning.schedule');
    Route::post('/schedule/update', [PagesController::class, 'updateSchedule'])->name('planning.schedule.update');
    Route::get('/activities', [PagesController::class, 'planning'])->name('planning.activities');
    Route::post('/filter', [PagesController::class, 'filterPlanning'])->name('planning.filter');
});*/
// CONFIGURACION
Route::prefix('configuration')
    ->name('config.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/', [ConfigurationController::class, 'index'])->name('index');
        Route::get('/appearance', [ConfigurationController::class, 'appearance'])->name('appearance');
        Route::put('/appearance/update', [ConfigurationController::class, 'updateAppearance'])->name('appearance.update');
    });


// PLANEACION
Route::prefix('planning')->name('planning.')->middleware(['auth', 'single.session', 'can:integral'])->group(function () {
    Route::get('/activities', [PagesController::class, 'activities'])->name('activities');
    Route::get('/schedule', [PagesController::class, 'schedule'])->name('schedule');
    Route::post('/schedule/update', [PagesController::class, 'updateSchedule'])->name('schedule.update');
    Route::post('/filter', [PagesController::class, 'filterPlanning'])->name('  filter');

    Route::post('/update-assignments', [PagesController::class, 'updateAssignments'])
        ->name('updateAssignments');

    // Rutas para el drag and drop del calendario
    Route::post('/update-event-date', [PagesController::class, 'updateEventDate'])
        ->name('updateEventDate');

    Route::post('/update-event-duration', [PagesController::class, 'updateEventDuration'])
        ->name('updateEventDuration');

    Route::post('/update-order', [PagesController::class, 'updateOrder'])
        ->name('update-order');

});

// CALIDAD
Route::prefix('quality')
    ->name('quality.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/customers', [QualityController::class, 'customers'])->name('customers');
        Route::get('/tracing', [QualityController::class, 'tracing'])->name('tracing');

        // rutas de relacion calidad-clientes
        Route::post('/permission/store', [QualityController::class, 'storePermission'])->name('permission.store');
        Route::get('/control/destroy/{id}/{matrixId}', [QualityController::class, 'destroyRelation'])->name('control.destroy');
        Route::get('/search', [QualityController::class, 'search'])->name('search');
        Route::get('/customer/{id}', [QualityController::class, 'customer'])->name('customer');
        Route::delete('/customer/{customerId}/{matrixId}/destroy', [QualityController::class, 'destroyRelation'])->name('customer.destroyRelation');

        // rutas de graficas de calidad
        Route::get('/customer/{id}/analytics', [QualityController::class, 'analytics'])->name('analytics');
        Route::get('/customer/{customer}/analytics/device-consumption', [QualityController::class, 'deviceConsumptionTable'])->name('deviceConsumptionTable');

        // rutas de ordenes de servicio
        Route::get('/customer/{id}/orders/search', [QualityController::class, 'searchOrders'])->name('orders.search');
        Route::post('/customer/{id}/orders/update', [QualityController::class, 'updateOrderTechnicians'])->name('orders.update');
        Route::get('/customer/{id}/contracts', [QualityController::class, 'contracts'])->name('contracts');

        // rutas de gestion de zonas
        Route::get('/customer/{id}/opportunity-area', [QualityController::class, 'opportunityAreas'])->name('opportunity-area');
        Route::post('/customer/{id}/technician/update', [QualityController::class, 'updateTechnician'])->name('update.technician');
        Route::post('/customer/{id}/ajax/technician/search', [QualityController::class, 'getTechniciansByDate'])->name('ajax.search.technicians');
        Route::get('/customer/{id}/floorplans', [CustomerController::class, 'showSedeFloorplans'])->name('floorplans');
        Route::get('/customer/{id}/application-areas', [QualityController::class, 'zones'])->name('application-areas');
        Route::get('/customer/zone/{id}/edit', [QualityController::class, 'editZone'])->name('zone.edit');
        Route::post('/customer/zone/{id}/update', [QualityController::class, 'updateZone'])->name('zone.update');
        Route::get('/customer/{id}/devices', [QualityController::class, 'devices'])->name('devices');
        //Route::get('/control', [PagesController::class, 'qualityControl'])->name('control');
        //Route::get('/control/destroy/{customerId}', [PagesController::class, 'qualityControlDestroy'])->name('control.destroy');
        //Route::get('/orders/{status}', [PagesController::class, 'qualityOrders'])->name('orders');
        //Route::get('/customer/{customerId}/{section}/{status}', [PagesController::class, 'qualityGeneralByCustomer'])->name('customer.details.general');
        //Ruta para archivos de la sede
        Route::get('/customer/{id}/files', [QualityController::class, 'showFiles'])->name('files');
        // Rutas para planes de rotación
        Route::get('/customer/{id}/rotation-plans', [QualityController::class, 'rotationPlans'])->name('rotation-plan.index');
        Route::get('/customer/{id}/rotation-plans/search', [QualityController::class, 'searchRotationPlans'])->name('rotation-plan.search');
        Route::get('/customer/{id}/rotation-plans/create', [QualityController::class, 'createRotationPlan'])->name('rotation-plan.create');
        //Rutas para filtrado de ordenes de servicio
        Route::get('/quality/customer/{id}/filter-orders', [QualityController::class, 'filterOrders'])->name('filter.orders');

        // Ruta para el filtrado AJAX (misma URL pero diferente manejo)
        Route::get('/customer/{id}/analytics/filter-device-consumption', [QualityController::class, 'deviceConsumptionPrueba'])
            ->name('analytics.filterDeviceConsumption');

    });

// CRM
Route::prefix('crm')->name('crm.')->middleware(['auth', 'single.session', 'can:integral'])->group(function () {
    Route::get('/dashboard', [CRMController::class, 'index'])->name('dashboard');
    Route::get('/agenda/calendar', [CRMController::class, 'agenda'])->name('agenda');
    Route::get('/agenda/tracking', [CRMController::class, 'tracking'])->name('tracking');
    Route::get('/agenda/quotation', [CRMController::class, 'quotation'])->name('quotation');

    Route::get('/tracking/services/{customerId}', [CRMController::class, 'servicesByCustomer'])->name('tracking.services');
    Route::post('/tracking/customer', [CRMController::class, 'trackingByCustomer'])->name('tracking.customer');
    Route::get('/tracking/create/{customerId}/{serviceId}', [CRMController::class, 'createTracking'])->name('tracking.create');
    Route::get('/tracking/create/{customerId}/order/{orderId}', [CRMController::class, 'createTrackingOrder'])->name('tracking.create.order');
    Route::post('/tracking/store', [CRMController::class, 'storeTracking'])->name('tracking.store');
    Route::get('/tracking/edit/{id}', [CRMController::class, 'editTracking'])->name('tracking.edit');
    Route::post('/tracking/update/{id}', [CRMController::class, 'updateTracking'])->name('tracking.update');
    Route::get('/tracking/cancel/{id}', [CRMController::class, 'cancelTracking'])->name('tracking.cancel');
    Route::get('/tracking/destroy/{id}', [CRMController::class, 'destroyTracking'])->name('tracking.destroy');
    Route::get('/tracking/auto/{id}', [CRMController::class, 'autoTracking'])->name('tracking.auto');
    Route::get('/tracking/complete/{id}', [CRMController::class, 'completeTracking'])->name('tracking.complete');
    Route::get('/tracking/search', [CRMController::class, 'searchTracking'])->name('tracking.search');
    Route::get('/tracking/history', [CRMController::class, 'historyTracking'])->name('tracking.history');

    Route::get('/trackings/pending', [CRMController::class, 'getTrackings'])->name('tracking.pending');
    Route::post('/trackings/set', [CRMController::class, 'setTracking'])->name('tracking.set');
});



// Inventario/Almance
Route::prefix('stock')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->name('stock.')
    ->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::post('/store', [StockController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [StockController::class, 'edit'])->name('edit');
        Route::put('/update/{id}/', [StockController::class, 'update'])->name('update');
        Route::get('/show/{id}', [StockController::class, 'show'])->name('show');
        Route::delete('/destroy/{id}', [StockController::class, 'destroy'])->name('destroy');
        Route::post('/input', [StockController::class, 'storeMovement'])->name('input');
        // Entradas de almacen 
        Route::get('/entry{id}', [StockController::class, 'entry'])->name('entry');
        Route::put('/entry/store', [StockController::class, 'storeInMovement'])->name('entry.store');
        // Salidas de almacen
        Route::get('/exits/{id}', [StockController::class, 'exits'])->name('exits');
        Route::put('/exit/store', [StockController::class, 'storeOutMovement'])->name('exit.store');
        // movimientos 
        Route::get('/movements', [StockController::class, 'movementsAll'])->name('movements.all');
        Route::get('/movements/warehouse/{id}', [StockController::class, 'movementsWarehouse'])->name('movements.warehouse');
        Route::get('/movements/orders', [StockController::class, 'movementsOrders'])->name('movements.orders');

        Route::get('/movement/{id}', [StockController::class, 'wMovement'])->name('movement');
        Route::get('/movement/search/{id}', [StockController::class, 'searchMovements'])->name('movement.search');
        Route::post('/movement/update/{id}', [StockController::class, 'updateMovement'])->name('movement.update');

        Route::get('/stock/{id}', [StockController::class, 'stock'])->name('stock');
        Route::get('/destroy/movement/{id}', [StockController::class, 'destroyMovement'])->name('destroy.movement');
        Route::get('/movement/product/timeline/{id}', [StockController::class, 'movementTimeline'])->name('movementTimeline');
        Route::put('/movements/{id}/revert', [StockController::class, 'revertMovement'])->name('revertMovement');

        // Voucher routes
        Route::get('/voucher/preview/{id}', [StockController::class, 'voucherPreview'])->name('voucherPreview');
        Route::get('/voucher/download/{id}', [StockController::class, 'downloadVoucher'])->name('downloadVoucher');
        Route::get('/voucher/pdf-preview/{id}', [StockController::class, 'voucherPdfPreview'])->name('voucherPdfPreview');

        Route::get('/export-movements', [StockController::class, 'exportMovements'])->name('exportMovements');
        // Route::get('/orders-products', [StockController::class, 'showWarehouseProductOrder'])->name('product.orders');
        // Productos (stock)
        Route::get('/show/products/{id}', [StockController::class, 'showProducts'])->name('showProducts');
        Route::get('/stock/export/{id}', [StockController::class, 'exportStock'])->name('exportStock');
        // Almacen de indirectos
        Route::get('/indirect/{id}', [StockController::class, 'indirectWarehouse'])->name('indirect');
        Route::post('/indirect/store/{id}', [StockController::class, 'storeIndirectProduct'])->name('indirect.store');
        Route::put('/indirect/update/{id}', [StockController::class, 'updateIndirectProduct'])->name('indirect.update');
        Route::delete('/indirect/destroy/{id}', [StockController::class, 'destroyIndirectProduct'])->name('indirect.destroy');

        // Graficas y estadistica
        Route::get('/analytics', [StockController::class, 'analytics'])->name('analytics');

        // Exportar stock a excel 
        Route::post('/export', [StockController::class, 'exportExcelStock'])->name('export');

        // Voucher routes
        Route::get('/voucher/preview/{id}', [StockController::class, 'voucherPreview'])->name('voucherPreview');
        Route::get('/voucher/download/{id}', [StockController::class, 'downloadVoucher'])->name('downloadVoucher');
        Route::get('/voucher/pdf-preview/{id}', [StockController::class, 'voucherPdfPreview'])->name('voucherPdfPreview');
    });

Route::prefix('stock/analytics/charts')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->name('stock.analytics.charts.')
    ->group(function () {
        Route::get('/product-use', [GraphicController::class, 'datasetProductUse'])->name('productuse.dataset');
        Route::get('/product-use/update', [GraphicController::class, 'refreshProductUse'])->name('productuse.update');

        Route::get('/stock-movements', [GraphicController::class, 'datasetStockMovements'])->name('stockmovements.dataset');
        Route::get('/stock-movements/update', [GraphicController::class, 'refreshStockMovements'])->name('stockmovements.update');

        // Nuevas gráficas de almacén
        Route::get('/inventory-by-warehouse', [GraphicController::class, 'datasetInventoryByWarehouse'])->name('inventory.dataset');
        Route::get('/low-stock-products', [GraphicController::class, 'datasetLowStockProducts'])->name('lowstock.dataset');
        Route::get('/product-rotation', [GraphicController::class, 'datasetProductRotation'])->name('rotation.dataset');
        Route::get('/most-used-products', [GraphicController::class, 'datasetMostUsedProductsByMonth'])->name('mostused.dataset');
    });

Route::prefix('inventory')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () { });

//lot
Route::prefix('lot')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->name('lot.')
    ->group(function () {
        Route::get('/index', [LotController::class, 'index'])->name('index');
        Route::get('/create', [LotController::class, 'create'])->name('create');
        Route::post('/store', [LotController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [LotController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [LotController::class, 'update'])->name('update');
        Route::get('/show/{id}', [LotController::class, 'show'])->name('show');
        Route::get('/destroy/{id}', [LotController::class, 'destroy'])->name('destroy');
        Route::get('/search', [LotController::class, 'search'])->name('search');

        Route::post('/products/search', [LotController::class, 'searchProducts'])
            ->name('products.search');
        //Ruta para trazabilidad del lote
        Route::get('/traceability/{id}', [LotController::class, 'getTraceability'])->name('traceability');
    });

Route::prefix('CRM/chart')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->name('crm.chart.')
    ->group(function () {
        // New Customers
        Route::get('/customers', [GraphicController::class, 'newCustomersDataset'])->name('customers');
        Route::get('/customers/update', [GraphicController::class, 'refreshNewCustomers'])->name('customers.refresh');
        Route::get('/customersByYear', [GraphicController::class, 'newCustomersByYear'])->name('customersByYear');
        Route::get('customersByYear/update', [GraphicController::class, 'refreshNewCustomersByYear'])->name('customersByYear.refresh');
        Route::get('/leads', [GraphicController::class, 'leadsDataset'])->name('leads');
        Route::get('/leads/update', [GraphicController::class, 'refreshLeadsDataset'])->name('leads.refresh');
        Route::get('/monthlyServices', [GraphicController::class, 'monthlyServicesDataset'])->name('monthlyServices');
        Route::get('/monthlyServices/update', [GraphicController::class, 'refreshMonthlyServices'])->name('monthlyServices.refresh');
        Route::get('/serviceOrders', [GraphicController::class, 'serviceOrdersDataset'])->name('serviceOrders');
        Route::get('/serviceOrders/update', [GraphicController::class, 'refreshServiceOrders'])->name('serviceOrders.refresh');

        Route::get('/crm/chart/leads', [GraphicController::class, 'leadsByServiceType'])->name('chartLeads');

        Route::get('/total-customers', [GraphicController::class, 'index'])->name('totalCustomers');
        Route::get('/new-leads-by-month', [GraphicController::class, 'newLeadsByMonth'])->name('newLeadsByMonth');
        // Scheduled Orders
        Route::get('/orders', [GraphicController::class, 'ordersDataset'])->name('orders');
        Route::get('/orders/update', [GraphicController::class, 'refreshOrders'])->name('orders.refresh');

        // Order Types
        Route::get('/ordertypes/{service_type}', [GraphicController::class, 'orderTypesDataset'])->name('ordertypes');
        Route::get('/ordertypes/{service_type}/update', [GraphicController::class, 'refreshOrderTypes'])->name('ordertypes.refresh');

        // views
        Route::get('/dashboard', [GraphicController::class, 'index'])->name('dashboard');
    }); // CRM CHARTS
Route::get('/CRM/chart/customers-by-category', [GraphicController::class, 'customersByCategory'])
    ->name('crm.chart.customersByCategory');


// Client System
/*Route::prefix('clients')
    ->middleware(['auth', 'single.session'])
    ->name('client.')
    ->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/reports/{section}', [ClientController::class, 'reports'])->name('reports.index');

        Route::post('/reports/search', [ClientController::class, 'searchReport'])->name('report.search');

        Route::get('/mip/{path}', [ClientController::class, 'mip'])->where('path', '.*')->name('mip.index');

        Route::get('/file/download/{path}', [ClientController::class, 'downloadFile'])->where('path', '.*')->name('file.download');
    });*/

Route::prefix('clients')
    ->middleware(['auth', 'single.session'])
    ->name('client.')
    ->group(function () {

        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/system/{path}', [ClientController::class, 'directories'])->where('path', '.*')->name('system.index');

        Route::post('/directory/store', [ClientController::class, 'storeDirectory'])->name('directory.store');
        Route::post('/file/store', [ClientController::class, 'storeFile'])->name('file.store');
        Route::post('/directory/update', [ClientController::class, 'updateDirectory'])->name('directory.update');
        Route::post('/file/update', [ClientController::class, 'updateFile'])->name('file.update');
        Route::post('/directory/permissions', [ClientController::class, 'permissions'])->name('directory.permissions');
        Route::get('/directory/mip/{path}', [ClientController::class, 'createMip'])->where('path', '.*')->name('directory.mip');

        Route::get('/directory/mgmt/{path}', [ClientController::class, 'managementDirectory'])->where('path', '.*')->name('directory.mgmt');

        Route::get('/directory/destroy/{path}', [ClientController::class, 'destroyDirectory'])->where('path', '.*')->name('directory.destroy');
        Route::get('/file/destroy/{path}', [ClientController::class, 'destroyFile'])->where('path', '.*')->name('file.destroy');

        Route::post('/reports/signature/store', [ClientController::class, 'storeSignature'])->name('report.signature.store');
        Route::get('/report/search/backup', [ClientController::class, 'searchBackupReport'])->name('report.search.backup');

        Route::post('/directory/search', [ClientController::class, 'searchDirectories'])->name('directory.search');
        Route::post('/directory/copy', [ClientController::class, 'copyDirectories'])->name('directory.copy');
        Route::post('/directory/move', [ClientController::class, 'moveDirectories'])->name('directory.move');
        //Ruta de prueba para manejar el arbol de directorios
        Route::get('/directory/list', [ClientController::class, 'listDirectories'])->name('directory.list');

        // Ruta para el filtrado de reportes
        Route::get('/reports', [ClientController::class, 'reports'])->name('reports');
        Route::get('/reports/{section}', [ClientController::class, 'reports'])->name('reports.index');
        Route::get('/file/download/{path}', [ClientController::class, 'downloadFile'])->where('path', '.*')->name('file.download');
    });


Route::prefix('report')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/customer/export/{va}', [ReportController::class, 'indexc'])->name('customersexport.index');
        Route::get('/export/{va}', [ReportController::class, 'index'])->name('reportServs.index');
        Route::post('/export/{va}', [ReportController::class, 'create'])->name('reportServs.create');
        Route::post('/customer/export/{va}', [ReportController::class, 'create_customer_report'])->name('reportcustomer.create');
    });

// USUARIOS ✔
Route::prefix('users')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->name('user.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::get('/create/client', [UserController::class, 'createClient'])->name('create.client');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::post('/store/client', [UserController::class, 'storeClient'])->name('store.client');
        Route::get('/show/{id}/{section}', [UserController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
        Route::get('/edit/{id}/client', [UserController::class, 'editClient'])->name('edit.client');
        Route::post('/update/{id}', [UserController::class, 'update'])->name('update');
        Route::get('/destroy/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('/restore/{id}', [UserController::class, 'active'])->name('restore');
        Route::get('/search', [UserController::class, 'search'])->name('search');
        Route::post('/file/upload/{userId}', [UserController::class, 'uploadFile'])->name('file.upload');
        Route::post('/file/uploadNew/{userId}', [UserController::class, 'uploadFileByName'])->name('file.uploadByName');//subir desde crear
        Route::get('/file/destroy/{fileId}', [UserController::class, 'destroyFile'])->name('file.destroy');
        Route::get('/file/download/{id}', [UserController::class, 'downloadFile'])->name('file.download');
        Route::get('/export', [UserController::class, 'export'])->name('export');

        Route::post('/directories', [UserController::class, 'directories'])->name('directories');
        Route::post('/search/sedes', [UserController::class, 'searchSedes'])->name('search.sedes');
    });

// CLIENTES
Route::prefix('customers')
    ->name('customer.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/sedes', [CustomerController::class, 'indexSedes'])->name('index.sedes');
        Route::get('/leads', [CustomerController::class, 'indexLeads'])->name('index.leads');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::get('/create/sede/{matrix}', [CustomerController::class, 'createSede'])->name('create.sede');
        Route::get('/create/lead', [CustomerController::class, 'createLead'])->name('create.lead');

        Route::post('/store', [CustomerController::class, 'store'])->name('store');
        Route::post('/store/sede', [CustomerController::class, 'storeSede'])->name('store.sede');
        Route::post('/store/lead', [CustomerController::class, 'storeLead'])->name('store.lead');

        Route::get('/edit/{id}', [CustomerController::class, 'edit'])->name('edit');
        Route::get('/edit/sede/{id}', [CustomerController::class, 'editSede'])->name('edit.sede');
        Route::get('/edit/lead/{id}', [CustomerController::class, 'editLead'])->name('edit.lead');

        Route::get('/show/sede/{matrix}', [CustomerController::class, 'showSede'])->name('show.sede');
        Route::get('/show/sede/{id}/files', [CustomerController::class, 'showSedeFiles'])->name('show.sede.files');
        Route::get('/show/sede/{id}/floorplans', [CustomerController::class, 'showSedeFloorplans'])->name('show.sede.floorplans');
        Route::get('/show/sede/{id}/portal', [CustomerController::class, 'showSedePortal'])->name('show.sede.portal');
        Route::get('/show/sede/{id}/areas', [CustomerController::class, 'showSedeAreas'])->name('show.sede.areas');

        Route::post('/update/{id}', [CustomerController::class, 'update'])->name('update');
        Route::post('/update/lead/{id}', [CustomerController::class, 'updateLead'])->name('update.lead');

        Route::post('/file/upload/{customerId}', [CustomerController::class, 'uploadFile'])->name('file.upload');

        Route::get('/destroy/{id}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::get('/destroy/lead/{id}', [CustomerController::class, 'destroyLead'])->name('destroy.lead');
        Route::get('/destroy/file/{id}', [CustomerController::class, 'destroyFile'])->name('destroy.file');

        Route::get('/search', [CustomerController::class, 'search'])->name('search');
        Route::get('/file/download/{id}', [CustomerController::class, 'downloadFile'])->name('file.download');

        Route::post('/autocomplete', [CustomerController::class, 'search_lead'])->name('autocomplete');
        Route::get('/convert/{id}', [CustomerController::class, 'convert'])->name('convert');
        Route::get('/tracking/{id}', [CustomerController::class, 'tracking'])->name('tracking');
        Route::get('/filter/{type}', [CustomerController::class, 'filterCustomer'])->name('filter');

        Route::post('reference/store/{customerId}', [CustomerController::class, 'storeReference'])->name('reference.store');
        Route::post('reference/update/{id}', [CustomerController::class, 'updateReference'])->name('reference.update');
        Route::get('reference/destroy/{id}', [CustomerController::class, 'destroyReference'])->name('reference.destroy');

        Route::get('/{id}/quotes/{class}', [QuoteController::class, 'index'])->name('quote');
        Route::post('/quote/store', [QuoteController::class, 'store'])->name('quote.store');
        Route::get('/quote/edit/{id}', [QuoteController::class, 'edit'])->name('quote.edit');
        Route::post('/quote/update/{id}', [QuoteController::class, 'update'])->name('quote.update');
        Route::get('/quote/destroy/{id}', [QuoteController::class, 'destroy'])->name('quote.destroy');
        Route::get('/quote/download/{id}', [QuoteController::class, 'download'])->name('quote.download');
        Route::get('/quote/search', [QuoteController::class, 'search'])->name('quote.search');
    });

// Leads, Clientes potenciales    
Route::prefix('leads')->group(function () {
    Route::get('/', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/import', [LeadController::class, 'importForm'])->name('leads.import.form');
    Route::post('/import', [LeadController::class, 'import'])->name('leads.import');
})->middleware('can:write_customer');

//RUTAS PARA PLANOS
Route::prefix('floorplans')
    ->name('floorplan.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/{id}', [FloorplansController::class, 'index'])->name('index');
        Route::get('/create/{id}', [FloorplansController::class, 'create'])->name('create');
        Route::post('/store/{customerId}', [FloorplansController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [FloorplansController::class, 'edit'])->name(name: 'edit');
        Route::get('/devices/{id}/{version}', [FloorplansController::class, 'editDevices'])->name('devices');

        //Route::post('/search/devices/{id}', [FloorplansController::class, 'searchDevicesbyVersion'])->name('search.devices');
    
        Route::get('/print/{id}', [FloorplansController::class, 'print'])->name('print');
        Route::post('/print/version', [FloorplansController::class, 'printVersion'])->name('print.version');

        Route::get('/QR/{id}', [FloorPlansController::class, 'getQR'])->name('qr');
        Route::post('/update/{id}', [FloorplansController::class, 'update'])->name('update');
        Route::post('/update/devices/{id}', [FloorplansController::class, 'updateDevices'])->name('update.devices');
        Route::post('/update/version/{id}', [FloorplansController::class, 'updateVersion'])->name('update.version');

        Route::post('/search/devices/{id}', [FloorPlansController::class, 'searchDevices'])->name('search.devices');
        Route::post('/search/print/{id}', [FloorPlansController::class, 'searchPrint'])->name('search.print');
        Route::post('/search/qr/{id}', [FloorPlansController::class, 'searchQRs'])->name('search.qr');

        Route::get('/delete/{id}', [FloorplansController::class, 'delete'])->name('delete');

        Route::get('/floorplans/show/{path}', [FloorPlansController::class, 'getImage'])->where('path', '.*')->name('image.show');
        Route::post('/floorplan/{id}/search/version', [FloorPlansController::class, 'searchDevicesbyVersion'])->name('search.device.version');
    });

// SERVICIOS
Route::prefix('services')
    ->name('service.')
    ->middleware(['auth', 'single.session', 'can:integral']) // Aplica el middleware de autenticación
    ->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('/create', [ServiceController::class, 'create'])->name('create');
        Route::post('/store', [ServiceController::class, 'store'])->name('store');
        Route::get('/show/{id}/{section}', [ServiceController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [ServiceController::class, 'edit'])->name('edit');
        Route::get('/edit/{id}/pests', [ServiceController::class, 'editPests'])->name('edit.pests');
        Route::get('/edit/{id}/appMethods', [ServiceController::class, 'editAppMethods'])->name('edit.appMethods');
        Route::get('/edit/{id}/products', [ServiceController::class, 'editProducts'])->name('edit.products');

        Route::post('/update/{id}', [ServiceController::class, 'update'])->name('update');
        Route::get('/destroy/{id}', [ServiceController::class, 'destroy'])->name('destroy');

        Route::get('/search', [ServiceController::class, 'search'])->name('search');
    });

// ORDENES DE SERVICIO
Route::prefix('orders')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->name('order.')
    ->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/store', [OrderController::class, 'store'])->name('store');
        Route::get('/search', [OrderController::class, 'search'])->name('search');
        Route::get('/filter', [OrderController::class, 'filter'])->name('filter');

        Route::post('/store/signature', [OrderController::class, 'storeSignature'])->name('signature.store');
        // Route::get('/search')
        Route::get('/show/{id}/{section}', [OrderController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [OrderController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [OrderController::class, 'update'])->name('update');
        Route::get('/destroy/{id}', [OrderController::class, 'destroy'])->name('destroy');

        Route::post('/search/customer', [OrderController::class, 'searchCustomer'])->name('search.customer');
        Route::post('/search/service/{type}', [OrderController::class, 'searchService'])->name('search.service');

        Route::post('/search/technicians', [OrderController::class, 'getTechniciansInRange'])->name('search.technician');
        Route::post('/assign/technicians', [OrderController::class, 'assignTechnicians'])->name('assign.technicians');
    });

Route::prefix('trackings')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->name('tracking.')
    ->group(function () {
        Route::get('/', [CRMController::class, 'tracking'])->name('tracking');

        Route::get('/services/{customerId}', [CRMController::class, 'servicesByCustomer'])->name('services');
        Route::post('/customer', [CRMController::class, 'trackingByCustomer'])->name('customer');

        Route::get('/create/order/{id}', [TrackingController::class, 'create'])->name('create.order');
        Route::post('/handle', [TrackingController::class, 'handle'])->name('handle');
        Route::post('/update', [TrackingController::class, 'update'])->name('update');
        Route::post('/update/status', [TrackingController::class, 'updateStatus'])->name('update.status');
        Route::post('/destroy', [TrackingController::class, 'destroy'])->name('destroy');


        Route::get('/edit/{id}', [CRMController::class, 'editTracking'])->name('edit');
        Route::get('/cancel/{id}', [CRMController::class, 'cancelTracking'])->name('cancel');
        Route::get('/auto/{id}', [CRMController::class, 'autoTracking'])->name('auto');
        Route::get('/complete/{id}', [CRMController::class, 'completeTracking'])->name('complete');
        Route::get('/search', [CRMController::class, 'searchTracking'])->name('search');
        Route::get('/history', [CRMController::class, 'historyTracking'])->name('history');

        Route::get('/pending', [CRMController::class, 'getTrackings'])->name('pending');
        Route::post('/set', [CRMController::class, 'setTracking'])->name('set');
    });


// PRODUCTOS
Route::prefix('products')
    ->name('product.')
    ->middleware(['auth', 'single.session', 'can:integral']) // Aplica el middleware de autenticación
    ->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');

        Route::post('/store', [ProductController::class, 'store'])->name('store');
        Route::post('/store/file/{id}', [ProductController::class, 'storeFile'])->name('store.file');

        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::get('/show/{id}', [ProductController::class, 'show'])->name('show');


        Route::get('/edit/{id}', [ProductController::class, 'edit'])->name('edit');
        Route::get('/edit/{id}/appMethods', [ProductController::class, 'editAppMethods'])->name('edit.appMethods');
        Route::get('/edit/{id}/pests', [ProductController::class, 'editPests'])->name('edit.pests');
        Route::get('/edit/{id}/files', [ProductController::class, 'editFiles'])->name('edit.files');
        Route::get('/edit/{id}/inputs', [ProductController::class, 'editInputs'])->name('edit.inputs');
        Route::get('/edit/{id}/treatment', [ProductController::class, 'editTreatments'])->name('edit.treatment');
        Route::get('/edit/{id}/movements', [ProductController::class, 'editMovements'])->name('edit.movements');

        Route::get('/download/file/{id}', [ProductController::class, 'downloadFile'])->name('download.file');
        Route::get('/destroy/file/{id}', [ProductController::class, 'destroyFile'])->name('destroy.file');

        Route::post('/update/{id}', [ProductController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [ProductController::class, 'destroy'])->name('destroy');
        Route::get('/search', [ProductController::class, 'search'])->name('search');

        Route::post('/input/{id}', [ProductController::class, 'input'])->name('input');
        Route::get('/input/destroy/{id}', [ProductController::class, 'destroyInput'])->name('input.destroy');
        Route::post('/file/upload/{id}', [ProductController::class, 'storeFile'])->name('file.upload');
    });


Route::middleware(['auth', 'single.session'])
    ->group(function () {
        Route::post('/saveIndex', [PagesController::class, 'setIndexEdit'])->name('page.index.set');
    });

//RUTAS PARA LA REFERENCIAS DEL CLIENTE
Route::prefix('customer/reference')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('create/{id}/{type}', [CustomerController::class, 'createReference'])->name('reference.create');
        Route::get('/edit/{id}/{type}', [CustomerController::class, 'editReference'])->name('reference.edit');
        Route::post('/{ref}/{type}', [CustomerController::class, 'updateReference'])->name('customer.Referenceupdate');
        Route::get('/show/{id}/{type}', [CustomerController::class, 'showReference'])->name('reference.show');
    });

// RUTAS PARA LAS AREAS DEL CLIENTE
Route::prefix('area')
    ->name('area.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::post('/store/{customerId}', [ApplicationAreasController::class, 'store'])->name('store');
        Route::post('/update', [ApplicationAreasController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [ApplicationAreasController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'single.session'])
    ->group(function () {
        Route::post('/floorplan/print/QR/{id}', [FloorplansController::class, 'printQR'])->name('floorplan.qr.print');
        Route::get('/floorplans/show/{path}', [FloorPlansController::class, 'getImage'])->where('path', '.*')->name('image.show');
        Route::get('/floorplan/print/massiveQR/{id}', [FloorplansController::class, 'printMassiveQR'])->name('floorplan.massiveqr.print');
    });

//PLAGAS
Route::prefix('pests')
    ->name('pest.')
    ->middleware(['auth', 'single.session', 'can:integral']) // Aplica el middleware de autenticación
    ->group(function () {
        Route::get('/', [PestController::class, 'index'])->name('index');
        Route::get('/create', [PestController::class, 'create'])->name('create');
        Route::post('/store', [PestController::class, 'store'])->name('store');
        Route::get('/show/{id}', [PestController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [PestController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [PestController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [PestController::class, 'destroy'])->name('destroy');
        Route::get('/search', [PestController::class, 'search'])->name('search');
    });

// PUNTOS DE CONTROL
Route::prefix('controlpoints')
    ->name('point.')
    ->middleware(['auth', 'single.session', 'can:integral']) // Aplica el middleware de autenticación
    ->group(function () {
        Route::get('/', [ControlPointController::class, 'index'])->name('index');
        Route::get('/create', [ControlPointController::class, 'create'])->name('create');
        Route::get('/show/{id}', [ControlPointController::class, 'show'])->name('show');
        Route::post('/store', [ControlPointController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [ControlPointController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [ControlPointController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [ControlPointController::class, 'destroy'])->name('destroy');
        Route::get('/search', [ControlPointController::class, 'search'])->name('search');
    });

// SUCURSALES
Route::prefix('branches')
    ->name('branch.')
    ->middleware(['auth', 'single.session', 'can:integral']) // Aplica el middleware de autenticación
    ->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/store', [BranchController::class, 'store'])->name('store');
        Route::get('/show/{id}', [BranchController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [BranchController::class, 'edit'])->name('edit');
        Route::get('/edit/{id}/contact', [BranchController::class, 'editContact'])->name('edit.contact');
        Route::post('/update/{id}', [BranchController::class, 'update'])->name('update');
        Route::get('/destroy/{id}', [BranchController::class, 'destroy'])->name('destroy');
        Route::get('restore/{id}', [BranchController::class, 'active'])->name('restore');
        Route::get('/search', [BranchController::class, 'search'])->name('search');
    });


//RUTAS PARA GENERAR UN REPORTE
Route::prefix('report')
    ->name('report.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/review/{id}', [ReportController::class, 'create'])->name('review');
        Route::post('/autoreview/{orderId}', [ReportController::class, 'autoreview'])->name('autoreview');
        Route::post('/generate/{orderId}', [ReportController::class, 'generate'])->name('store');
        Route::get('/propagate/{orderId}/{serviceId}/{productId}', [ReportController::class, 'propagate'])->name('show');

        Route::post('/store/dom/{id}/{optionChooser}', [OrderController::class, 'createReports'])->name('create');
        Route::post('/search/product', [ReportController::class, 'searchProduct'])->name('search.product');
        Route::post('/set/product/{orderId}', [ReportController::class, 'setProduct'])->name('set.product');
        Route::get('/destroy/product/{dataId}', [ReportController::class, 'destroyProduct'])->name('destroy.product');

        Route::post('/set/incident/{orderId}', [ReportController::class, 'setIncident'])->name('set.incident');

        Route::post('/device', [ReportController::class, 'getDevices'])->name('device');
        Route::post('/device/bulk', [ReportController::class, 'bulkPrint'])->name('bulk');
        Route::get('/device/bulk/download/{timer}', [ReportController::class, 'downloadBulk'])->name('bulk.download');
        Route::get('/device/bulk/delete/{timer}', [ReportController::class, 'deleteBulk'])->name('bulk.delete');


        Route::post('/order/update', [ReportController::class, 'updateOrder'])->name('order.update');
        Route::post('/customer/update', [ReportController::class, 'updateCustomer'])->name('customer.update');
        Route::post('/description/update', [ReportController::class, 'updateDescription'])->name('description.update');
        Route::post('/notes/update', [ReportController::class, 'updateNotes'])->name('notes.update');

        Route::post('/search/devices', [ReportController::class, 'searchDevices'])->name('search.devices');
        Route::post('/devices/assign', [ReportController::class, 'assignDevices'])->name('assign.devices');

        Route::post('/evidence/store/{orderId}', [ReportController::class, 'storeEvidence'])->name('evidence.store');
    });

Route::prefix('report')
    ->name('report.')
    ->middleware(['auth', 'single.session'])
    ->group(function () {
        Route::get('/print/{orderId}', [ReportController::class, 'print'])->name('print');
    });



// Daily Program
Route::name('dailyprogram.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/dailyprogram', [ScheduleController::class, 'index'])->name('index');
        Route::post('/dailyprogram/date', [ScheduleController::class, 'get_dailyprogram'])->name('get');
    });


Route::get('/next-page', [PagesController::class, 'next_page'])->name('next-page');
Route::get('/prev-page', [PagesController::class, 'prev_page'])->name('prev-page');
Route::get('/nextpage', [PagesController::class, 'nextpage'])->name('nextpage');
Route::get('/prevpage', [PagesController::class, 'prevpage'])->name('prevpage');
Route::get('/nextpag', [PagesController::class, 'nextpag'])->name('nextpag');
Route::get('/prevpag', [PagesController::class, 'prevpag'])->name('prevpag');
Route::get('/nextpa', [PagesController::class, 'nextpa'])->name('nextpa');
Route::get('/prevpa', [PagesController::class, 'prevpa'])->name('prevpa');

//PREGUNTAS - PUNTO DE CONTROL
Route::prefix('question')
    ->name('question.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/create/{pointId}', [QuestionController::class, 'create'])->name('create');
        Route::post('/store/{pointId}', [QuestionController::class, 'store'])->name('store');
        Route::get('/edit/{question}', [QuestionController::class, 'edit'])->name('edit');
        Route::put('/update/{question}', [QuestionController::class, 'update'])->name('update');
        Route::get('/delete/{pointId}/{questionId}', [QuestionController::class, 'destroy'])->name('destroy');
        Route::post('/set/{pointId}', [QuestionController::class, 'set'])->name('set');
    });

//RUTAS PARA CALENDARIO
Route::prefix('contracts')
    ->name('contract.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/create', [ContractController::class, 'create'])->name('create');
        Route::post('/store', [ContractController::class, 'store'])->name('store');
        Route::get('/show/{id}/{section}', [ContractController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [ContractController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [ContractController::class, 'update'])->name('update');
        Route::get('/destroy/{id}', [ContractController::class, 'destroy'])->name('destroy');
        Route::get('/search', [ContractController::class, 'search'])->name('search');
        Route::get('/search/orders/{id}/{customerId}', [ContractController::class, 'searchOrders'])->name('search.orders');
        Route::get('/upload', [ContractController::class, ''])->name('upload');
        Route::get('/download/{id}/{file}', [ContractController::class, ''])->name('download');
        Route::get('/getSelectedTechnicians', [ContractController::class, 'getSelectedTechnicians'])->name('getTechnicans');
        Route::post('/update/technicians/{id}', [ContractController::class, 'updateTechnicians'])->name('update.technicians');
        Route::post('/file/{contractID}/{type}', [ContractController::class, 'store_file'])->name('file');
        Route::get('/index/{contractID}/', [ContractController::class, 'index_contract'])->name('indexS');
        Route::get('/file/download/{id}', [ContractController::class, 'contract_downolad'])->name('file.download');

        Route::get('/renew/{id}', [ContractController::class, 'renew'])->name('renew');
    });

Route::prefix('opportunity-areas')
    ->name('opportunity-area.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/', [OpportunityAreaController::class, 'index'])->name('index');
        Route::get('/create/{customerId}', [OpportunityAreaController::class, 'create'])->name('create');
        Route::post('/store', [OpportunityAreaController::class, 'store'])->name('store');
        Route::get('/show/{id}/{section}', [OpportunityAreaController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [OpportunityAreaController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [OpportunityAreaController::class, 'update'])->name('update');
        Route::get('/destroy/{id}', [OpportunityAreaController::class, 'destroy'])->name('destroy');
        Route::get('/search/{customerId}', [OpportunityAreaController::class, 'search'])->name('search');
        Route::post('/print/{customerId}', [OpportunityAreaController::class, 'print'])->name('print');
        Route::post('/upload/file/{customerId}/{type}', [OpportunityAreaController::class, 'uploadFile'])->name('upload.file');
        Route::get('/image/{id}/{type}', [OpportunityAreaController::class, 'getImage'])->where('filename', '.*')->name('image.show');
    });

// RUTAS PARA EL PLAN DE ROTACION

Route::prefix('rotationplan')
    ->name('rotation.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/{contractId}', [RotationPlanController::class, 'index'])->name('index');
        Route::get('/create/{contractId}', [RotationPlanController::class, 'create'])->name('create');
        Route::post('/store', [RotationPlanController::class, 'store'])->name('store');
        Route::get('/show/{id}', [RotationPlanController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [RotationPlanController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [RotationPlanController::class, 'update'])->name('update');
        Route::get('/destroy/{id}', [RotationPlanController::class, 'destroy'])->name('destroy');
        Route::get('/print/{id}', [RotationPlanController::class, 'print'])->name('print');
        Route::post('/changes/{id}', [RotationPlanController::class, 'changes'])->name('changes');
        Route::get('/destroy/change/{id}', [RotationPlanController::class, 'destroyChanges'])->name('destroy.change');

        Route::post('/ajax/search/product', [RotationPlanController::class, 'searchProduct'])->name('search.product');
        Route::post('/ajax/search/review', [RotationPlanController::class, 'searchReview'])->name('search.review');
    });

// Rutas para las requisiciones de compra
Route::prefix('purchase-requisition')
    ->name('purchase-requisition.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/', [PurchaseRequisitionController::class, 'dashboard'])->name('dashboard');
        Route::get('/purchases', [PurchaseRequisitionController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseRequisitionController::class, 'create'])->name('create');
        Route::post('/store', [PurchaseRequisitionController::class, 'store'])->name('store');
        Route::get('/show/{id}', [PurchaseRequisitionController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [PurchaseRequisitionController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [PurchaseRequisitionController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [PurchaseRequisitionController::class, 'destroy'])->name('destroy');
        Route::get('/search', [PurchaseRequisitionController::class, 'search'])->name('search');
        Route::get('/print/{id}', [PurchaseRequisitionController::class, 'print'])->name('print');
        Route::post('/upload/file/{id}', [PurchaseRequisitionController::class, 'uploadFile'])->name('upload.file');
        Route::get('/file/download/{id}', [PurchaseRequisitionController::class, 'downloadFile'])->name('file.download');
        Route::get('/quote/{id}', [PurchaseRequisitionController::class, 'quote'])->name('quote');
        Route::put('/quote/update/{id}', [PurchaseRequisitionController::class, 'updateQuote'])->name('quote.update');
        Route::put('/approve/{id}', [PurchaseRequisitionController::class, 'approve'])->name('approve');
        Route::put('/complete/{id}', [PurchaseRequisitionController::class, 'complete'])->name('complete');
        Route::put('/reject/{id}', [PurchaseRequisitionController::class, 'reject'])->name('reject');

        Route::get('/{id}/pdf', [PurchaseRequisitionController::class, 'generatePDF'])->name('pdf');
        Route::get('/exportExcel', [PurchaseRequisitionController::class, 'exportExcel'])->name('export');
    });

// Rutas para consumo de producto por planta
// Route::prefix('consumption')
//     ->name('consumption.')
//     ->middleware('auth')
//     ->group(function () {
//         Route::get('/', [PurchaseRequisitionController::class, 'indexConsumption'])->name('index');
//         Route::get('/create', [ProductController::class, 'createConsumption'])->name('create');
//         Route::post('/store', [ProductController::class, 'storeConsumption'])->name('store');           

//         // Rutas para consumos totales pasados 
//         Route::get('/consumptions', [ProductController::class, 'showConsumptions'])->name('show.past');
//         Route::get('/consumptions/filter', [ProductController::class, 'getPastConsumptions'])->name('past.filter');
//         Route::get('/filtered', [ProductController::class, 'showFilteredConsumptions'])->name('show.filtered');
//         Route::get('/total/export', [ProductController::class, 'exportTotalConsumption'])->name('total.export');

//         //Rutas para consumo de cliente especifico 
//         Route::get('/customer/search', [ProductController::class, 'searchCustomerConsumption'])->name('customer.search');

//         //rutas para consumo de producto 
//         Route::get('product/detail/{id}', [ProductController::class, 'showProductConsumptionDetail'])->name('product.detail');
//         Route::get('/product/{product_id}/export', [ProductController::class, 'exportProductConsumption'])->name('product.export');
//     });

// Rutas para zonas de clientes (customer_zones)
Route::prefix('comercial-zones')
    ->name('comercial-zones.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [ComercialZoneController::class, 'index'])->name('index');
        Route::get('/create', [ComercialZoneController::class, 'create'])->name('create');
        Route::post('/store', [ComercialZoneController::class, 'store'])->name('store');
        Route::get('/show/{id}', [ComercialZoneController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [ComercialZoneController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [ComercialZoneController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [ComercialZoneController::class, 'destroy'])->name('destroy');
        Route::get('/search', [ComercialZoneController::class, 'search'])->name('search');

        // AJAX routes
        Route::post('/get-zones-by-customer', [ComercialZoneController::class, 'getZonesByCustomer'])->name('get-zones-by-customer');
    });

// Rutas para consumos mensuales
Route::prefix('consumptions')
    ->name('consumptions.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/pre', [ConsumptionController::class, 'preIndex'])->name('pre-index');
        Route::get('/', [ConsumptionController::class, 'index'])->name('index');
        Route::get('/create', [ConsumptionController::class, 'create'])->name('create');
        Route::post('/store', [ConsumptionController::class, 'store'])->name('store');
        Route::get('/show/{id}', [ConsumptionController::class, 'show'])->name('show');
        Route::get('/show-grouped', [ConsumptionController::class, 'showGrouped'])->name('show-grouped');
        Route::get('/edit/{id}', [ConsumptionController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [ConsumptionController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [ConsumptionController::class, 'destroy'])->name('destroy');
        Route::delete('/destroy-group', [ConsumptionController::class, 'destroyGroup'])->name('destroy-group');
        Route::get('/export', [ConsumptionController::class, 'export'])->name('export');

        // Ruta para consumos totales pasados
        Route::get('/', [ConsumptionController::class, 'index'])->name('index');
        //Rutas para filtrado de consumos pasados  
        Route::get('/filter', [ConsumptionController::class, 'getConsumptionsFiltered'])->name('consumptions.filter');
        Route::get('/total/export', [ConsumptionController::class, 'exportTotalConsumption'])->name('total.export');


        Route::get('/create/order-based-rp', [ConsumptionController::class, 'createRp'])->name('create-order-based-rp');
        Route::get('/products-by-plan', [ConsumptionController::class, 'getProductsByPlan'])->name('products-by-plan');


        // Reportes y funciones especiales
        Route::get('/report-by-zone', [ConsumptionController::class, 'reportByZone'])->name('report-by-zone');
        Route::get('/download-file/{id}', [ConsumptionController::class, 'downloadFile'])->name('download-file');
        Route::post('/change-status/{id}', [ConsumptionController::class, 'changeStatus'])->name('change-status');

        // Rutas para surtir productos
        Route::get('/supply-grouped', [ConsumptionController::class, 'showSupplyGrouped'])->name('supply-grouped');
        Route::post('/update-supply', [ConsumptionController::class, 'updateSupply'])->name('update-supply');

        // AJAX routes
        Route::get('/customers-by-zone', [ConsumptionController::class, 'getCustomersByZone'])->name('customers-by-zone');
        Route::post('/export', [ConsumptionController::class, 'exportConsumptions'])->name('export');
    });

// Rutas para los provedores (supplier)
Route::prefix('suppliers')
    ->name('supplier.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/store', [SupplierController::class, 'store'])->name('store');
        Route::get('/show/{id}', [SupplierController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [SupplierController::class, 'destroy'])->name('destroy');
        Route::get('/search', [SupplierController::class, 'search'])->name('search');
    });

//Rutas de busqueda para AJAX
Route::prefix('ajax')
    ->name('ajax.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::post('/control-points', [OrderController::class, 'getControlPoints'])->name('devices.points');
        Route::post('/devices/{id}', [FloorPlansController::class, 'getDevicesVersion'])->name('devices');
        Route::post('/quality/search/orders/customer', [PagesController::class, 'getOrdersByCustomer'])->name('quality.search.customer');
        Route::post('/quality/search/orders/date', [QualityController::class, 'getOrdersByDate'])->name('quality.search.date');
        Route::post('/quality/search/orders/time', [QualityController::class, 'getOrdersByTime'])->name('quality.search.time');
        Route::post('/quality/search/orders/service', [QualityController::class, 'getOrdersByService'])->name('quality.search.service');
        Route::post('/quality/search/orders/status', [QualityController::class, 'getOrdersByStatus'])->name('quality.search.status');
        Route::post('/quality/search/orders/technician/{id}', [QualityController::class, 'searchOrdersTechnician'])->name('quality.search.date.technician');

        Route::get('/contract/service', [ContractController::class, 'getSelectData'])->name('contract.service');
    });

// Rutas para modulo de facturacion
Route::prefix('invoices')
    ->name('invoices.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/dashboard', [InvoiceController::class, 'dashboard'])->name('dashboard');
        // Factura
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/store', [InvoiceController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [InvoiceController::class, 'edit'])->name('edit');
        Route::get('/show/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/update', [InvoiceController::class, 'update'])->name('update');
        Route::post('/generate/{id}/{type}', [InvoiceController::class, 'generate'])->name('generate');
        Route::post('/{id}/{type}/confirm-concepts', [InvoiceController::class, 'confirmData'])->name('confirm.data');
        Route::post('/{id}/{type}/update-data', [InvoiceController::class, 'updateData'])->name('update.data');
        Route::get('/download/{id}', [InvoiceController::class, 'downloadInvoice'])->name('download');
        Route::get('/print/{id}', [InvoiceController::class, 'showPdf'])->name('show.pdf');
        // XML
        Route::post('/{id}/generate-xml', [InvoiceController::class, 'generateXml'])->name('generate.xml');
        Route::get('/xml/{id}', [InvoiceController::class, 'showXml'])->name('show.xml');
        //Route::get('/stamp/{id}', [InvoiceController::class, 'stampInvoice'])->name('stamp');
        // Enviar por correo
        Route::post('/send/{id}', [InvoiceController::class, 'sendInvoice'])->name('send.email');
        // Cancelar factura
        Route::post('/cancel/{id}', [InvoiceController::class, 'cancel'])->name('cancel');

        // Contribuyentes
        Route::get('/customers', [CustomerController::class, 'getInvoiceCustomers'])->name('customers');
        Route::get('/customer/create', [CustomerController::class, 'createInvoiceCustomer'])->name('customer.create');
        Route::post('/customer/store', [CustomerController::class, 'storeInvoiceCustomer'])->name('customer.store');
        Route::get('/customer/edit/{id}', [CustomerController::class, 'editInvoiceCustomer'])->name('customer.edit');
        Route::put('/customer/update/{id}', [CustomerController::class, 'updateInvoiceCustomer'])->name('customer.update');
        Route::delete('/customer/destroy/{id}', [CustomerController::class, 'destroyTaxCustomer'])->name('customer.destroy');
        Route::get('/customer/{id}', [InvoiceController::class, 'showCustomerInvoices'])->name('customer.show');

        // Ruta para el calendario de pagos
        Route::get('/events', [InvoiceController::class, 'getEvents'])->name('events');

        Route::get('/concepts', [InvoiceController::class, 'getConcepts'])->name('concepts');
        Route::post('/concept/store', [InvoiceController::class, 'storeConcept'])->name('concept.store');
        Route::post('/concept/update', [InvoiceController::class, 'updateConcept'])->name('concept.update');

        // Timbrado Facturama
        Route::prefix('stamp')->name('stamp.')->group(function () {
            Route::controller(InvoiceController::class)->group(function () {
                Route::get('/invoice/{id}', [InvoiceController::class, 'stampInvoice'])->name('invoice');
                Route::get('/credit-note/{id}', [InvoiceController::class, 'stampCreditNote'])->name('credit-note');
                Route::get('/payment/{id}', [InvoiceController::class, 'stampPayment'])->name('payment');
            });
        });

        Route::prefix('download')->name('download.')->group(function () {
            Route::controller(InvoiceController::class)->group(function () {
                Route::get('/invoice/{id}', 'downloadInvoice')->name('zip-invoice');
                Route::get('/credit-note/{id}', 'downloadCreditNote')->name('zip-credit-note');
                Route::get('/payment/{id}', 'downloadPayment')->name('zip-payment');
            });
        });

        // Nomina
        Route::get('/paysheet/{id}', [InvoiceController::class, 'payroll'])->name('payroll');


        // Notas de pago
        Route::prefix('credit-notes')->name('credit-notes.')->group(function () {
            Route::get('/', [InvoiceController::class, 'indexCreditNotes'])->name('index');
            Route::get('/create', [InvoiceController::class, 'createCreditNote'])->name('create');
            Route::post('/store', [InvoiceController::class, 'storeCreditNotes'])->name('store');
        });

        // Complementos de pago
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [InvoiceController::class, 'indexPayments'])->name('index');
            Route::get('/create', [InvoiceController::class, 'createPayment'])->name('create');
            Route::post('/store', [InvoiceController::class, 'storePayment'])->name('store');
            Route::get('/edit/{id}', [InvoiceController::class, 'editPayment'])->name('edit');
            Route::put('/update/{id}', [InvoiceController::class, 'updatePayment'])->name('update');

        });



        Route::post('/ajax/search', [InvoiceController::class, 'searchInvoices'])->name('ajax.search');
    });

// Nomina
Route::prefix('payrolls')
    ->name('payrolls.')
    ->middleware(['auth', 'single.session', 'can:integral'])
    ->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/create', [PayrollController::class, 'create'])->name('create');
        Route::post('/store', [PayrollController::class, 'store'])->name('store');
        Route::get('/show/{id}', [PayrollController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [PayrollController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [PayrollController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [PayrollController::class, 'destroy'])->name('destroy');

        Route::get('/stamp/{id}', [PayrollController::class, 'stampPayroll'])->name('stamp');
        Route::get('/download/{id}', [PayrollController::class, 'downloadPayroll'])->name('download');
    });

Route::get('/snake', function () {
    return view('minigm.snake');
});
Route::get('/breakout', function () {
    return view('minigm.breakout');
});


Route::get('/google-drive/auth', [GoogleDriveController::class, 'redirectToGoogle'])
    ->name('google.drive.auth');

Route::get('/google-drive/callback', [GoogleDriveController::class, 'handleGoogleCallback'])
    ->name('google.drive.callback');

Route::get('/google-drive/test', [GoogleDriveController::class, 'testConnection'])
    ->name('google.drive.test');

Route::get('/loading-erp', [PagesController::class, 'loadingERP'])->name('loading-erp');

require __DIR__ . '/auth.php';