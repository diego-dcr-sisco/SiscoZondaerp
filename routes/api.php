<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;
use App\Http\Controllers\GraphicController;
use App\Http\Controllers\LotController;



/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::get('/token/session/get', [AppController::class, 'getCsrfToken']);
Route::post('/login', [AppController::class, 'login']);
Route::post('/logout', [AppController::class, 'logout']);

// Efectivo v1.3
Route::get('/orders/{id}/{date}', [AppController::class, 'orders']);
Route::get('/user/getData', [AppController::class, 'getUsers']);
Route::post('/report/chemicalapplications', [AppController::class, 'setChemicalApplications']);
Route::post('/report/pestcontrol', [AppController::class, 'setPestControl']);

// Efectivo v1.4
Route::get('/v14/data/getOrders/{id}/{dates}', [AppController::class, 'getOrdersV14']);
Route::post('/v14/report/chemicalapplications', [AppController::class, 'setChemicalApplicationsV14']);
Route::post('/v14/report/pestcontrol', [AppController::class, 'setPestControlV14']);

Route::post('/optyareas/store', [AppController::class, 'setOpportunityAreas']);

Route::get('/refresh-new-customers', [GraphicController::class, 'refreshNewCustomers'])->name('crm.chart.customers');
Route::get('/refresh-new-customers-by-year', [GraphicController::class, 'refreshNewCustomersByYear'])->name('crm.chart.customersByYear');
Route::get('/refresh-leads', [GraphicController::class, 'refreshLeadsDataset'])->name('crm.chart.monthlyLeads');
Route::get('/refresh-monthlyServices', [GraphicController::class, 'refreshMonthlyServices'])->name('crm.chart.monthlyServices');
Route::get('/refresh-service-orders', [GraphicController::class, 'refreshServiceOrders'])->name('crm.chart.serviceOrders');

Route::get('/lots', [LotController::class, 'getLotsByProduct']);


Route::post('/reports/handle', [AppController::class, 'handleReport']);
Route::post('/device/update-location', [AppController::class, 'updateDeviceLocation']);


