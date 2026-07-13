<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FamilleController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineDocumentTypeController;
use App\Http\Controllers\MachineMaintenanceController;
use App\Http\Controllers\MagazineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductConversionController;
use App\Http\Controllers\ProductionConsumptionController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProductionOutputController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RawMaterialCategoryController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\RawMaterialPurchaseController;
use App\Http\Controllers\RechargePartController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SituationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierSituationController;
use App\Http\Controllers\TraiteController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleDocumentTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductStockMovementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {

    // Dashboard Routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');

    // Raw Material Routes
    Route::get('products/by-type/{type}', [SalesOrderController::class, 'getProductsByType'])->name('products.by-type');
    Route::get('raw-materials/get-by-code', [RawMaterialController::class, 'getByCode'])->name('raw-materials.get-by-code');
    Route::get('/sales-orders/client/{id}/unpaid', [SalesOrderController::class, 'getClientUnpaidOrders'])->name('sales.orders.client.unpaid');
    Route::get('/raw-materials/list', [RawMaterialController::class, 'getList'])->name('raw-materials.list');
    Route::get('/raw-materials/list-for-sale', [RawMaterialController::class, 'getListForSale'])->name('raw-materials.getListForSale');
    // Route::get('raw-materials/list', [SalesOrderController::class, 'getRawMaterialsList'])->name('raw-materials.list');
    Route::get('/raw-materials/{id}/stock-movements', [RawMaterialController::class, 'stockMovements'])
    ->name('raw-materials.stock-movements');
    Route::resource('raw-materials', RawMaterialController::class);
    Route::post('raw-materials/{id}/adjust-stock', [RawMaterialController::class, 'adjustStock'])->name('raw-materials.adjust-stock');
    Route::get('raw-materials-data/autocomplete', [RawMaterialController::class, 'autocomplete'])->name('raw-materials.autocomplete');
    Route::get('raw-materials/{id}/stock-details', [RawMaterialController::class, 'getStockDetails'])->name('raw-materials.stock-details');
    Route::get('raw-materials/stock-info/{id}', [RawMaterialController::class, 'getStockInfoPurchase'])->name('raw-materials.stock-info');

    // Raw Material Categories Routes
    Route::resource('raw-material-categories', RawMaterialCategoryController::class);
    Route::get('raw-material-categories-data/select', [RawMaterialCategoryController::class, 'getCategories'])->name('raw-material-categories.select');

    // Magazine Routes
    Route::prefix('magazines')->group(function () {
        Route::get('/', [MagazineController::class, 'index'])->name('magazines.index');
        Route::post('/', [MagazineController::class, 'store'])->name('magazines.store');
        Route::get('/{id}', [MagazineController::class, 'show'])->name('magazines.show');
        Route::put('/{id}', [MagazineController::class, 'update'])->name('magazines.update');
        Route::delete('/{id}', [MagazineController::class, 'destroy'])->name('magazines.destroy');
        Route::get('/select/list', [MagazineController::class, 'getMagazinesForSelect'])->name('magazines.select');
    });

    Route::post('/employees/{employee}/createUser', [EmployeeController::class, 'createUser'])->name('employees.createUser');

    // Raw Material Purchases Routes
    Route::get('/raw-material-purchases/available-checks', [RawMaterialPurchaseController::class, 'getAvailableChecks'])->name('raw-material-purchases.available-checks');
    Route::get('/raw-material-purchases/available-traites', [RawMaterialPurchaseController::class, 'getAvailableTraites'])->name('raw-material-purchases.available-traites');
    Route::get('/raw-material-purchases/supplier/{supplierId}/purchases', [RawMaterialPurchaseController::class, 'getSupplierPurchasesList'])->name('raw-material-purchases.supplier-purchases');
    Route::post('/raw-material-purchases/supplier/{supplierId}/distribute-payment', [RawMaterialPurchaseController::class, 'distributeSupplierPayment'])->name('raw-material-purchases.distribute-payment');
    Route::resource('raw-material-purchases', RawMaterialPurchaseController::class);
    Route::get('raw-material-purchases/{id}/receipt', [RawMaterialPurchaseController::class, 'showReceiptForm'])->name('raw-material-purchases.receipt');
    Route::post('raw-material-purchases/{id}/receipt', [RawMaterialPurchaseController::class, 'processReceipt'])->name('raw-material-purchases.process-receipt');
    Route::get('raw-material-purchases/statistics', [RawMaterialPurchaseController::class, 'getStatistics'])->name('raw-material-purchases.statistics');
    Route::get('raw-material-purchases/{id}/pdf', [RawMaterialPurchaseController::class, 'generatePdf'])->name('raw-material-purchases.pdf');
    Route::get('raw-materials/{id}/details', [RawMaterialController::class, 'getDetails'])->name('raw-materials.details');
    Route::get('raw-materials/{id}/stock', [RawMaterialController::class, 'getStockInfo'])->name('raw-materials.stock');
    Route::post('/checks/store', [RawMaterialPurchaseController::class, 'storeCheck'])->name('checks.store');
    Route::post('/purchases/update-payment-status', [RawMaterialPurchaseController::class, 'updatePaymentStatus'])->name('purchases.update-payment-status');
    Route::post('/raw-material-purchases/add-payment', [RawMaterialPurchaseController::class, 'addPayment'])->name('raw-material-purchases.add-payment');
    Route::get('/raw-material-purchases/{id}/details', [RawMaterialPurchaseController::class, 'getPurchaseDetails'])->name('raw-material-purchases.details');
    Route::patch('/raw-material-purchases/payment-documents/{documentId}/payment-method', [RawMaterialPurchaseController::class, 'updatePaymentDocument'])->name('raw-material-purchases.update-payment-document');
    Route::put('/raw-material-purchases/payment-documents/{documentId}', [RawMaterialPurchaseController::class, 'updatePaymentDocument']);
    Route::delete('/raw-material-purchases/payment-documents/{documentId}', [RawMaterialPurchaseController::class, 'deletePaymentDocument'])->name('raw-material-purchases.delete-payment-document');

    Route::prefix('employees/{employee}')->name('employees.documents.')->group(function () {
        Route::get('/documents', [EmployeeDocumentController::class, 'index'])->name('index');
        Route::post('/documents/upload', [EmployeeDocumentController::class, 'upload'])->name('upload');
    });


    Route::prefix('documents')->name('employees.documents.')->group(function () {
        Route::get('/{document}/download', [EmployeeDocumentController::class, 'download'])->name('download');
        Route::get('/{document}/preview', [EmployeeDocumentController::class, 'preview'])->name('preview');
        Route::delete('/{document}', [EmployeeDocumentController::class, 'destroy'])->name('destroy');
    });

    // Check management routes
    // Route::resource('checks', CheckController::class);

    // Products
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('products/statistics', [ProductController::class, 'getStatistics'])->name('products.statistics');
    Route::get('products/stock-info/{id}', [ProductController::class, 'getStockInfo'])->name('products.stock-info');
    Route::get('products/check-low-stock', [ProductController::class, 'checkLowStock'])->name('products.check-low-stock');
    Route::get('/products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');
    Route::get('products/{id}/production-time', [ProductController::class, 'getProductionTime'])->name('products.production-time');
    Route::get('products/famille-stock/{id}', [ProductController::class, 'getFamilleStockDetails'])->name('products.famille-stock');
    Route::put('products/{id}/toggle-familles', [ProductController::class, 'toggleFamilles'])->name('products.toggle-familles');
    Route::get('/products/get-familles/{id}', [ProductController::class, 'getFamillesForProduct'])
    ->name('products.get-familles');
    Route::post('/products/add-stock/{id}', [ProductController::class, 'addStock'])
        ->name('products.add-stock');
    Route::post('/products/{id}/update-family-prices', [ProductController::class, 'updateFamilyPrices'])->name('products.update-family-prices');
    Route::resource('products', ProductController::class);

    // Product Categories
    Route::resource('product-categories', ProductCategoryController::class)->except(['create', '    show']);


    Route::get('production-orders/get-bom-materials', [ProductionOrderController::class, 'getBomMaterials'])->name('production-orders.get-bom-materials');

    Route::post('/production-orders/{id}/override-quality', [ProductionOutputController::class, 'overrideQuality'])
        ->name('production-orders.override-quality')
        ->middleware(['can:edit_production_orders']);

    Route::get('/production-orders/{id}/quality-details', [ProductionOutputController::class, 'getQualityDetails'])
        ->name('production-orders.quality-details');

    // Production Orders Routes
    Route::prefix('production-orders')->name('production-orders.')->group(function () {
        Route::get('/', [ProductionOrderController::class, 'index'])->name('index');
        Route::get('/create', [ProductionOrderController::class, 'create'])->name('create');
        Route::post('/', [ProductionOrderController::class, 'store'])->name('store');
        Route::get('/get-source-product-familles', [ProductionOrderController::class, 'getSourceProductFamilles'])
            ->name('get-source-product-familles');
        Route::post('/{id}/waste-declaration', [ProductionOrderController::class, 'handleWasteDeclaration'])->name('waste-declaration');
        Route::get('/needing-waste-declaration', [ProductionOrderController::class, 'getOrdersNeedingWasteDeclaration'])->name('needing-waste');


        // Data routes
        Route::get('/get-bom', [ProductionOrderController::class, 'getBom'])->name('get-bom');
        Route::get('/get-conversions', [ProductionOrderController::class, 'getConversions'])->name('get-conversions');
        Route::get('/dashboard-statistics', [ProductionOrderController::class, 'dashboardStatistics'])->name('dashboard-statistics');
        Route::get('/export', [ProductionOrderController::class, 'export'])->name('export');
        Route::get('/get-source-products', [ProductionOrderController::class, 'getSourceProducts'])->name('get-source-products');
        Route::get('/get-final-products', [ProductionOrderController::class, 'getFinalProducts'])->name('get-final-products');
        Route::get('/get-conversion-details', [ProductionOrderController::class, 'getConversionDetails'])->name('get-conversion-details');
        Route::get('/{id}/bom', [ProductionOrderController::class, 'getOrderBom'])->name('bom');
        Route::get('/get-familles', [ProductionOrderController::class, 'getFamilles'])
         ->name('get-familles');

        Route::get('/statistics', [ProductionOrderController::class, 'getStatistics'])->name('statistics');
        Route::get('/employee-report', [ProductionOrderController::class, 'employeeReport'])->name('employee-report');
        Route::get('/{id}', [ProductionOrderController::class, 'show'])->name('show');
        Route::get('/{id}/print', [ProductionOrderController::class, 'printOrder'])->name('print');
        Route::get('/{id}/edit', [ProductionOrderController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProductionOrderController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductionOrderController::class, 'destroy'])->name('destroy');

        // Action routes
        Route::post('/{id}/approve', [ProductionOrderController::class, 'approve'])->name('approve');
        Route::post('/{id}/start', [ProductionOrderController::class, 'start'])->name('start');
        Route::post('/{id}/complete', [ProductionOrderController::class, 'complete'])->name('complete');
        Route::post('/{id}/cancel', [ProductionOrderController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/cancellation-preview', [ProductionOrderController::class, 'getCancellationPreview'])->name('cancellation-preview');
    });

    //Production Conversions
    Route::get('products/{id}/conversions', [ProductConversionController::class, 'getConversionInfo']);
    Route::get('product-conversions/statistics', [ProductConversionController::class, 'getStatistics'])->name('product-conversions.statistics');
    Route::resource('product-conversions', ProductConversionController::class);

    // Production Output
    Route::get('production-output/statistics', [ProductionOutputController::class, 'getStatistics'])->name('production-output.statistics');
    Route::get('production-output/get-order-details', [ProductionOutputController::class, 'getOrderDetails'])->name('production-output.get-order-details');
    Route::post('production-output/batch', [ProductionOutputController::class, 'batchStore'])->name('production-output.batch-store');
    Route::get('production-output/batch/create', [ProductionOutputController::class, 'batchCreate'])->name('production-output.batch-create');
    Route::get('production-output/type3/{order_id}/create', [ProductionOutputController::class, 'createType3'])
    ->name('production-output.create-type3');
    Route::get('/production-output/create-type2/{order_id}', [ProductionOutputController::class, 'createType2'])
    ->name('production-output.create-type2');
    Route::get('/production-output/create-type4/{order_id}', [ProductionOutputController::class, 'createType4'])->name('production-output.create-type4');
    Route::post('/production-output/store-type4', [ProductionOutputController::class, 'storeType4'])->name('production-output.store-type4');
    Route::get('production-output/type5/{order_id}/create', [ProductionOutputController::class, 'createType5'])->name('production-output.create-type5');
    Route::post('/production-output/store-type5', [ProductionOutputController::class, 'storeType5'])->name('production-output.store-type5');
    Route::get('production-output/{id}/quick-view', [ProductionOutputController::class, 'quickView'])->name('production-output.quick-view');
    Route::get('production-orders/{id}/output-details', [ProductionOutputController::class, 'getOrderDetails'])->name('production-orders.output-details');
    Route::get('production-orders/{id}/output-summary', [ProductionOrderController::class, 'getOutputSummary'])->name('production-orders.output-summary');
    Route::get('production-orders/{order_id}/bom/{material_id}', [ProductionOrderController::class, 'getBomForMaterial'])->name('production-orders.bom-material');
    Route::post('production-orders/{id}/complete-with-consumption', [ProductionOrderController::class, 'completeWithConsumption'])
    ->name('production-orders.complete-with-consumption');
    Route::get('/production-orders/{id}/edit-order', [ProductionOrderController::class, 'editOrder'])->name('production-orders.edit-order');
    Route::post('/production-orders/{id}/cancel-production', [ProductionOrderController::class, 'cancelProduction'])->name('production-orders.cancel-production');
    Route::get('production-output/order-outputs/{orderId}', [ProductionOutputController::class, 'getOrderOutputs'])->name('production-output.order-outputs');
    Route::get('production-output/order-wastes/{orderId}', [ProductionOutputController::class, 'getOrderWastes'])->name('production-output.order-wastes');
    Route::get('production-output/order-volume/{orderId}', [ProductionOutputController::class, 'getOrderVolume'])->name('production-output.order-volume');
    Route::post('/production-output/store-type2', [ProductionOutputController::class, 'storeType2'])->name('production-output.store-type2');
    Route::post('/production-output/store-type3', [ProductionOutputController::class, 'storeType3'])->name('production-output.store-type3');
    Route::resource('production-output', ProductionOutputController::class);

    Route::get('/api/production-orders/{orderId}/consumptions', [ProductionOutputController::class, 'getOrderConsumptions']);
    Route::post('/api/production-orders/{orderId}/consumptions', [ProductionOutputController::class, 'saveConsumptions']);
    Route::get('/api/production-orders/{id}/consumed-blocks', [ProductionOrderController::class, 'getConsumedBlocks']);
    Route::get('/api/products/{productId}/famille/{familleId}/stock', [ProductionOutputController::class, 'getSourceStock']);
    Route::prefix('api/production-orders')->group(function () {
        Route::get('/{order_id}/consumed', [ProductionOutputController::class, 'getOrderConsumed']);
        Route::get('/{order_id}/product/{product_id}', [ProductionOutputController::class, 'getOrderProduct']);
        Route::get('/type2/{order_id}/consumed', [ProductionOutputController::class, 'getOrderConsumedType2']);
        Route::get('/type2/{order_id}/product/{product_id}', [ProductionOutputController::class, 'getOrderProductType2']);
    });

    // Familles Routes
    Route::resource('familles', FamilleController::class);
    Route::get('familles/{id}/products-data', [FamilleController::class, 'getProductsData'])->name('familles.products-data');
    Route::get('familles/by-product/{productId}', [FamilleController::class, 'getByProduct'])->name('familles.by-product');
    Route::post('familles/{id}/adjust-stock', [FamilleController::class, 'adjustStock'])->name('familles.adjust-stock');
    Route::post('familles/{id}/manage-products', [FamilleController::class, 'manageProducts'])->name('familles.manage-products');
    Route::get('familles/{familleId}/prices', [ProductController::class, 'getFamilyPrices'])->name('familles.prices');
    Route::get('/products/{id}/familles', [ProductController::class, 'getFamillesForProduct'])->name('products.familles');

    // API Routes for familles
    Route::get('api/familles/by-product/{productId}', [FamilleController::class, 'getByProduct']);
    Route::get('api/familles/stock/{productId}/{familleId}', [FamilleController::class, 'getStockInfo']);

    // Production Consumption
    Route::resource('production-consumption', ProductionConsumptionController::class);
    Route::get('production-consumption/statistics', [ProductionConsumptionController::class, 'getStatistics'])->name('production-consumption.statistics');
    Route::get('production-consumption/order/{order_id}', [ProductionConsumptionController::class, 'getOrderConsumptions'])->name('production-consumption.order');

    // Clients Routes
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/create', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('/statistics', [ClientController::class, 'getStatistics'])->name('statistics');
        Route::get('/select2', [ClientController::class, 'getClientsSelect2'])->name('select2');
        Route::get('/list', [ClientController::class, 'list'])->name('list');
        Route::get('/check-credit/{id}', [ClientController::class, 'checkCredit'])->name('check-credit');
        Route::post('/{id}/distribute-payment', [ClientController::class, 'distributePayment'])->name('clients.distribute-payment');
        Route::post('/{id}/add-balance', [ClientController::class, 'addBalance'])->name('add-balance');
        Route::get('/{id}/balance', [ClientController::class, 'getClientBalance'])->name('get-balance');
        Route::get('/{id}/credit-status', [ClientController::class, 'getCreditStatus'])->name('credit-status');
        Route::get('/{id}/documents', [ClientController::class, 'documents'])->name('documents');
        Route::post('/{id}/upload-document', [ClientController::class, 'uploadDocument'])->name('upload-document');
        Route::delete('/{id}/delete-document/{documentId}', [ClientController::class, 'deleteDocument'])->name('delete-document');
        Route::get('/{id}', [ClientController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ClientController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/situation', [SupplierSituationController::class, 'index'])->name('situation.index');
        Route::get('/situation/supplier/{supplierId}/purchases', [SupplierSituationController::class, 'getSupplierPurchases'])->name('situation.supplier.purchases');
        Route::get('/situation/supplier/{supplierId}/balance', [SupplierSituationController::class, 'getSupplierBalance'])->name('situation.supplier.balance');
        Route::get('/situation/supplier/{supplierId}/print', [SupplierSituationController::class, 'printSupplierSituation'])->name('situation.supplier.print');
        Route::post('/situation/supplier/{supplierId}/add-balance', [SupplierSituationController::class, 'addSupplierBalance'])->name('situation.add-balance');
        Route::post('/situation/supplier/{supplierId}/pay-by-balance', [SupplierSituationController::class, 'payByBalance'])->name('situation.pay-by-balance');
        Route::get('/situation/supplier/{supplierId}', [SupplierSituationController::class, 'supplierSituation'])->name('situation.supplier');
        Route::get('/situation/export', [SupplierSituationController::class, 'export'])->name('situation.export');
        Route::get('/situation/statistics', [SupplierSituationController::class, 'getStatistics'])->name('situation.statistics');
    });


    Route::prefix('credit-notes')->name('credit-notes.')->group(function() {
        Route::get('/', [CreditNoteController::class, 'index'])->name('index');
        Route::get('/create', [CreditNoteController::class, 'create'])->name('create');
        Route::post('/', [CreditNoteController::class, 'store'])->name('store');
        Route::get('/statistics', [CreditNoteController::class, 'getStatistics'])->name('statistics');

        // AJAX routes
        Route::get('/client/{clientId}/orders', [CreditNoteController::class, 'getClientOrders'])->name('client.orders');
        Route::get('/order/{orderId}/items', [CreditNoteController::class, 'getOrderItems'])->name('order.items');

        // Status routes
        Route::get('/client/{clientId}/info', [CreditNoteController::class, 'getClientInfo'])->name('client.info');
        Route::get('/client/{clientId}/all-orders', [CreditNoteController::class, 'getClientOrders'])->name('client.all-orders');
        Route::put('/{id}/approve', [CreditNoteController::class, 'approve'])->name('approve');
        Route::put('/{id}/reject', [CreditNoteController::class, 'reject'])->name('reject');
        Route::put('/{id}/process', [CreditNoteController::class, 'process'])->name('process');
        Route::get('/{id}/pdf', [CreditNoteController::class, 'generatePdf'])->name('pdf');
        Route::get('/{id}/edit', [CreditNoteController::class, 'edit'])->name('edit');
        Route::get('/{id}', [CreditNoteController::class, 'show'])->name('show');
        Route::put('/{id}', [CreditNoteController::class, 'update'])->name('update');
        Route::delete('/{id}', [CreditNoteController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('attendance')->name('attendance.')->group(function() {
        Route::get('/', [AttendanceController::class, 'monthlyCalendar'])->name('index');
        Route::post('/mark-today', [AttendanceController::class, 'markToday'])->name('mark-today');
        Route::get('/get-by-date', [AttendanceController::class, 'getByDate'])->name('get-by-date');
        Route::get('/report', [AttendanceController::class, 'monthlyReport'])->name('report');
        Route::get('/settings', [AttendanceController::class, 'settings'])->name('settings');
        Route::post('/settings', [AttendanceController::class, 'settings'])->name('settings.update');
        Route::put('/{id}', [AttendanceController::class, 'update'])->name('update');
        Route::get('/employee/{employeeId}/stats', [AttendanceController::class, 'getEmployeeStats'])->name('employee.stats');
        Route::get('/employee/{employeeId}/history', [AttendanceController::class, 'getEmployeeHistory'])->name('employee.history');
        Route::get('/employee/{employeeId}/details', [AttendanceController::class, 'employeeDetails'])->name('employee.details');
        Route::get('/employee/{employeeId}/payment', [AttendanceController::class, 'getEmployeePayment'])->name('employee.payment');
        Route::post('/save-hours-calendar', [AttendanceController::class, 'saveHoursCalendar'])->name('save-hours-calendar');
        Route::delete('/delete-cell-calendar', [AttendanceController::class, 'deleteCellCalendar'])->name('delete-cell-calendar');
        Route::post('/save-avance', [AttendanceController::class, 'saveAvance'])->name('save-avance');
    });

    Route::resource('product-stock-movements', ProductStockMovementController::class)->only(['index', 'show']);

    // Sales Routes
    Route::prefix('sales')->name('sales.')->group(function () {
        // Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [SalesOrderController::class, 'index'])->name('index');
            Route::get('/create', [SalesOrderController::class, 'create'])->name('create');
            Route::post('/', [SalesOrderController::class, 'store'])->name('store');
            Route::get('/statistics', [SalesOrderController::class, 'getStatistics'])->name('statistics');
            Route::get('/products/{id}/price', [SalesOrderController::class, 'getProductPrice'])->name('products.price');
            Route::get('/revenue', [SalesOrderController::class, 'getRevenueStatistics'])->name('revenue');
            Route::get('/cash-flow-statistics', [SalesOrderController::class, 'getCashFlowStatistics'])->name('cash-flow');
            Route::get('/total-value', [SalesOrderController::class, 'getTotalOrdersValue'])->name('total-value');
            Route::get('/volume-statistics', [SalesOrderController::class, 'getVolumeStatistics'])->name('volume-statistics');
            Route::post('/{order}/add-payment', [SalesOrderController::class, 'addPayment'])->name('add-payment');
            Route::get('/{id}/edit', [SalesOrderController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SalesOrderController::class, 'update'])->name('update');
            Route::get('/{id}', [SalesOrderController::class, 'show'])->name('show');
            Route::delete('/{id}', [SalesOrderController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/create-invoice', [SalesOrderController::class, 'createInvoice'])->name('create-invoice');
            Route::get('/get-product-price/{id}', [SalesOrderController::class, 'getProductPrice'])->name('get-product-price');
            Route::get('/{id}/items', [SalesOrderController::class, 'getOrderItems'])->name('items');
            Route::delete('/{orderId}/payments/{paymentId}', [SalesOrderController::class, 'deletePayment'])->name('delete-payment');
            Route::get('/delivery-note/{id}', [SalesOrderController::class, 'generateDeliveryNote'])->name('delivery-note');
            Route::get('/{id}/delivery-note/view', [SalesOrderController::class, 'viewDeliveryNote'])->name('delivery-note.view');
        });

        Route::get('/situation', [SituationController::class, 'index'])->name('situation.index');
        Route::get('/situation/client/{clientId}', [SituationController::class, 'clientSituation'])->name('situation.client');
        Route::get('/situation/client/{clientId}/print', [SituationController::class, 'printClientSituation'])->name('situation.client.print');
        Route::get('/situation/export', [SituationController::class, 'export'])->name('situation.export');

        Route::prefix('quotations')->name('quotations.')->group(function () {
            Route::get('/', [QuotationController::class, 'index'])->name('index');
            Route::get('/create', [QuotationController::class, 'create'])->name('create');
            Route::post('/', [QuotationController::class, 'store'])->name('store');
            Route::get('/statistics', [QuotationController::class, 'getStatistics'])->name('statistics');

            // AJAX routes (should come before parameter routes)
            Route::get('/products/{type}', [QuotationController::class, 'getProductsByType'])->name('products.by-type');
            Route::get('/raw-materials', [QuotationController::class, 'getRawMaterialsList'])->name('raw-materials.list');
            Route::get('/product/{id}', [QuotationController::class, 'getProductDetails'])->name('product.details');
            Route::get('/raw-material/{id}', [QuotationController::class, 'getRawMaterialDetails'])->name('raw-material.details');

            // Routes with specific patterns before the generic {id} route
            Route::get('/{id}/edit', [QuotationController::class, 'edit'])->name('edit');
            Route::put('/{id}', [QuotationController::class, 'update'])->name('update');
            Route::patch('/{id}/status', [QuotationController::class, 'updateStatus'])->name('update-status');
            Route::get('/{id}/duplicate', [QuotationController::class, 'duplicate'])->name('duplicate');
            Route::get('/{id}/pdf', [QuotationController::class, 'generatePdf'])->name('pdf');

            // Generic {id} route should be last
            Route::get('/{id}', [QuotationController::class, 'show'])->name('show');
            Route::delete('/{id}', [QuotationController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/create', [InvoiceController::class, 'create'])->name('create');
            Route::post('/', [InvoiceController::class, 'store'])->name('store');
            Route::get('/statistics', [InvoiceController::class, 'getStatistics'])->name('statistics');

            // AJAX routes
            Route::get('/products/{type}', [InvoiceController::class, 'getProductsByType'])->name('products.by-type');
            Route::get('/raw-materials', [InvoiceController::class, 'getRawMaterialsList'])->name('raw-materials.list');
            Route::get('/product/{id}', [InvoiceController::class, 'getProductDetails'])->name('product.details');
            Route::get('/raw-material/{id}', [InvoiceController::class, 'getRawMaterialDetails'])->name('raw-material.details');
            Route::get('/generate-number', [InvoiceController::class, 'generateNextInvoiceNumber'])->name('generate-number');
            Route::get('/get-sale-order/{orderId}', [InvoiceController::class, 'getSalesOrderDetails'])->name('get-sale-order');
            Route::get('/get-client-sales', [InvoiceController::class, 'getClientSalesOrders'])->name('get-client-sales');
            Route::post('/get-multiple-sales', [InvoiceController::class, 'getMultipleSalesOrders'])->name('get-multiple-sales');
            Route::post('/get-sales-by-ids', [InvoiceController::class, 'getSalesByIds'])->name('get-sales-by-ids');

            // Routes with specific patterns
            Route::get('/{id}/edit', [InvoiceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [InvoiceController::class, 'update'])->name('update');
            Route::get('/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name('duplicate');
            Route::get('/{id}/pdf', [InvoiceController::class, 'generatePdf'])->name('pdf');

            // Generic routes
            Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
            Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/client/{clientId}/orders', [PurchaseController::class, 'getClientOrders'])->name('client.orders');
        Route::get('/client/{clientId}/payments', [PurchaseController::class, 'getClientPayments'])->name('client.payments');
        Route::get('/statistics/data', [PurchaseController::class, 'getStatistics'])->name('statistics');
        Route::put('/{id}', [PurchaseController::class, 'update'])->name('update');
        Route::delete('/{id}', [PurchaseController::class, 'destroy'])->name('destroy');
        Route::post('/add-payment', [PurchaseController::class, 'addPaymentToClient'])->name('add-payment');
        Route::get('/{id}/download', [PurchaseController::class, 'downloadDocument'])->name('download');
        Route::get('/{id}', [PurchaseController::class, 'show'])->name('show');
    });

    // Inventory routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');

        // Data tables
        Route::get('/products/data', [InventoryController::class, 'getProducts'])->name('products.data');
        Route::get('/raw-materials/data', [InventoryController::class, 'getRawMaterials'])->name('raw-materials.data');
        Route::get('/pending-adjustments/data', [InventoryController::class, 'getPendingAdjustments'])->name('pending-adjustments.data');

        // Stock adjustments
        Route::post('/request-adjustment', [InventoryController::class, 'requestAdjustment'])->name('request-adjustment');
        Route::post('/approve-adjustment/{id}', [InventoryController::class, 'approveAdjustment'])->name('approve-adjustment');
        Route::post('/reject-adjustment/{id}', [InventoryController::class, 'rejectAdjustment'])->name('reject-adjustment');
        Route::post('/bulk-request-adjustments', [InventoryController::class, 'bulkRequestAdjustments'])->name('bulk-request-adjustments');
        Route::post('/approve-all-adjustments', [InventoryController::class, 'approveAllAdjustments'])->name('approve-all-adjustments');

        // Helpers
        Route::get('/product/{productId}/families', [InventoryController::class, 'getProductFamilies'])->name('product-families');
        Route::get('/raw-material/{id}/details', [InventoryController::class, 'getMaterialDetails'])->name('raw-material-details');
        Route::post('/raw-material/adjust-direct', [InventoryController::class, 'directAdjustRawMaterial'])->name('raw-material-adjust-direct');
    });

    // Checks
    Route::prefix('checks')->name('checks.')->group(function () {
        Route::get('/', [CheckController::class, 'index'])->name('index');
        Route::get('/create', [CheckController::class, 'create'])->name('create');
        Route::post('/', [CheckController::class, 'store'])->name('store');
        Route::get('/{id}', [CheckController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CheckController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CheckController::class, 'update'])->name('update');
        Route::delete('/{id}', [CheckController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/allocate', [CheckController::class, 'allocate'])->name('allocate');
        Route::post('/{id}/allocations', [CheckController::class, 'storeAllocation'])->name('allocations.store');
        Route::get('/statistics', [CheckController::class, 'getStatistics'])->name('statistics');
        Route::post('/{id}/clear', [CheckController::class, 'clearCheck'])->name('clear');
        Route::post('/{id}/mark-deposited', [CheckController::class, 'markAsDeposited'])->name('mark-deposited');
        Route::post('/{id}/mark-bounced', [CheckController::class, 'markAsBounced'])->name('mark-bounced');
    });

    // Traites Routes
    Route::resource('traites', TraiteController::class);
    Route::post('/traites/{id}/mark-paid', [TraiteController::class, 'markAsPaid'])->name('traites.mark-paid');
    Route::post('/traites/{id}/mark-overdue', [TraiteController::class, 'markAsOverdue'])->name('traites.mark-overdue');
    Route::post('/traites/{id}/mark-bounced', [TraiteController::class, 'markAsBounced'])->name('traites.mark-bounced');
    Route::get('/traites-statistics', [TraiteController::class, 'getStatistics'])->name('traites.statistics');

    // Suppliers Routes
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/statistics', [SupplierController::class, 'getStatistics'])->name('statistics');
        Route::get('/select2', [SupplierController::class, 'getSuppliersSelect2'])->name('select2');
        Route::get('/{id}', [SupplierController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('destroy');
    });

    // Profile Routes
    Route::prefix('profile')->name('profile.')->middleware(['auth'])->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::get('/password', [ProfileController::class, 'password'])->name('password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // Employee Routes
    Route::resource('employees', EmployeeController::class)->middleware(['admin']);
    Route::post('/{id}/create-user', [EmployeeController::class, 'createUser'])->name('employees.create-user')->middleware(['admin']);


    // Expense Routes
    Route::resource('expenses', ExpenseController::class)->middleware(['admin']);
    Route::post('/expenses/approve/{id}', [ExpenseController::class, 'approve'])->name('expenses.approve')->middleware(['admin']);

    // Expense Categories Routes
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show'])->middleware(['admin']);

    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Roles
    Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolePermissionController::class, 'rolesIndex'])->name('index');
            Route::get('/create', [RolePermissionController::class, 'rolesCreate'])->name('create');
            Route::post('/', [RolePermissionController::class, 'rolesStore'])->name('store');
            Route::get('/{id}', [RolePermissionController::class, 'rolesShow'])->name('show');
            Route::get('/{id}/edit', [RolePermissionController::class, 'rolesEdit'])->name('edit');
            Route::put('/{id}', [RolePermissionController::class, 'rolesUpdate'])->name('update');
            Route::delete('/{id}', [RolePermissionController::class, 'rolesDestroy'])->name('destroy');
            Route::get('/{id}/permissions', [RolePermissionController::class, 'rolesPermissions'])->name('permissions');
        });

        // Permissions
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [RolePermissionController::class, 'permissionsIndex'])->name('index');
            Route::post('/', [RolePermissionController::class, 'permissionsStore'])->name('store');
            Route::put('/{id}', [RolePermissionController::class, 'permissionsUpdate'])->name('update');
            Route::delete('/{id}', [RolePermissionController::class, 'permissionsDestroy'])->name('destroy');
        });

        // User Role Assignment
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/roles', [RolePermissionController::class, 'userRoles'])->name('roles');
            Route::post('/{id}/assign-roles', [RolePermissionController::class, 'assignUserRoles'])->name('assign-roles');
            Route::get('/{id}/roles', [RolePermissionController::class, 'getUserRoles'])->name('get-roles');
        });
    });

    // Vehicles
    Route::resource('vehicles', VehicleController::class);
    Route::get('/vehicles/{vehicleId}/documents/{documentTypeId}/history', [VehicleController::class, 'getDocumentHistory'])->name('vehicles.document-history');
    Route::post('/vehicles/documents/store', [VehicleController::class, 'storeDocument'])->name('vehicles.documents.store');
    Route::get('/vehicles/export/excel', [VehicleController::class, 'exportExcel'])->name('vehicles.export.excel');
    Route::get('/vehicles/export/pdf', [VehicleController::class, 'exportPdf'])->name('vehicles.export.pdf');
    Route::resource('vehicle-document-types', VehicleDocumentTypeController::class);

    Route::prefix('machines')->name('machines.')->group(function () {
        Route::get('/', [MachineController::class, 'index'])->name('index');
        Route::get('/create', [MachineController::class, 'create'])->name('create');
        Route::post('/', [MachineController::class, 'store'])->name('store');
        Route::get('/{id}', [MachineController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [MachineController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MachineController::class, 'update'])->name('update');
        Route::delete('/{id}', [MachineController::class, 'destroy'])->name('destroy');

        // Document routes
        Route::get('/{machineId}/document-history/{documentTypeId}', [MachineController::class, 'getDocumentHistory'])
            ->name('document-history');
        Route::post('/documents', [MachineController::class, 'storeDocument'])
            ->name('documents.store');
    });

    // Machine Document Types Routes
    Route::prefix('machine-document-types')->name('machine-document-types.')->group(function () {
        Route::get('/', [MachineDocumentTypeController::class, 'index'])->name('index');
        Route::get('/create', [MachineDocumentTypeController::class, 'create'])->name('create');
        Route::post('/', [MachineDocumentTypeController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [MachineDocumentTypeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MachineDocumentTypeController::class, 'update'])->name('update');
        Route::delete('/{id}', [MachineDocumentTypeController::class, 'destroy'])->name('destroy');
    });

    Route::get('/machines/export/excel', [MachineController::class, 'exportExcel'])->name('machines.export.excel');
    Route::get('/machines/export/pdf', [MachineController::class, 'exportPdf'])->name('machines.export.pdf');

    // Machine Maintenance Schedules Routes
    Route::prefix('machine-maintenance')->name('machine-maintenance.')->group(function () {
        Route::post('/', [MachineMaintenanceController::class, 'store'])->name('store');
        Route::put('/{id}', [MachineMaintenanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [MachineMaintenanceController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/complete', [MachineMaintenanceController::class, 'complete'])->name('complete');
        Route::get('/{id}/history', [MachineMaintenanceController::class, 'history'])->name('history');
    });


    // Drivers
    Route::get('/drivers/statistics', [DriverController::class, 'getStatistics'])->name('drivers.statistics');
    Route::get('/drivers/export/excel', [DriverController::class, 'exportExcel'])->name('drivers.export.excel');
    Route::get('/drivers/export/pdf', [DriverController::class, 'exportPdf'])->name('drivers.export.pdf');
    Route::resource('drivers', DriverController::class);

    Route::prefix('notifications')->middleware(['auth'])->group(function () {
        Route::get('/get', [NotificationController::class, 'getNotifications'])->name('notifications.get');
        Route::post('/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    });

    // Recharge Parts Routes
    Route::put('recharge-parts/{id}/adjust-stock', [RechargePartController::class, 'adjustStock'])->name('recharge-parts.adjust-stock');
    Route::get('recharge-parts/{id}/history', [RechargePartController::class, 'history'])->name('recharge-parts.history');
    Route::get('recharge-parts-statistics', [RechargePartController::class, 'getStatistics'])->name('recharge-parts.statistics');
    Route::get('recharge-parts-low-stock', [RechargePartController::class, 'getLowStock'])->name('recharge-parts.low-stock');
    Route::resource('recharge-parts', RechargePartController::class);


    // Admin Routes with permissions
    Route::middleware(['auth', 'permission:manage_roles'])->prefix('admin')->name('admin.')->group(function () {
        // Roles
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolePermissionController::class, 'rolesIndex'])->name('index');
            Route::get('/create', [RolePermissionController::class, 'rolesCreate'])->name('create');
            Route::post('/', [RolePermissionController::class, 'rolesStore'])->name('store');
            Route::get('/{id}', [RolePermissionController::class, 'rolesShow'])->name('show');
            Route::get('/{id}/edit', [RolePermissionController::class, 'rolesEdit'])->name('edit');
            Route::put('/{id}', [RolePermissionController::class, 'rolesUpdate'])->name('update');
            Route::delete('/{id}', [RolePermissionController::class, 'rolesDestroy'])->name('destroy');
            Route::get('/{id}/permissions', [RolePermissionController::class, 'rolesPermissions'])->name('permissions');
            Route::put('/{id}/permissions', [RolePermissionController::class, 'updateRolePermissions'])->name('update-permissions');
        });

        // Permissions
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [RolePermissionController::class, 'permissionsIndex'])->name('index');
            Route::post('/', [RolePermissionController::class, 'permissionsStore'])->name('store');
            Route::put('/{id}', [RolePermissionController::class, 'permissionsUpdate'])->name('update');
            Route::delete('/{id}', [RolePermissionController::class, 'permissionsDestroy'])->name('destroy');
        });

        // User Role Assignment
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/roles', [RolePermissionController::class, 'userRoles'])->name('roles');
            Route::post('/{id}/assign-roles', [RolePermissionController::class, 'assignUserRoles'])->name('assign-roles');
            Route::get('/{id}/roles', [RolePermissionController::class, 'getUserRoles'])->name('get-roles');
        });
    });

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/production-orders/{id}', [ProductionOrderController::class, 'apiShow']);

        Route::get('production-orders/{id}/wastes', [ProductionOrderController::class, 'getWastes'])
        ->name('api.production-orders.wastes');

        Route::get('/products/{id}/production-time', function ($id) {
            $product = \App\Models\Product::find($id);
            return response()->json([
                'success' => true,
                'production_time_days' => $product->production_time_days ?? 7
            ]);
        })->name('products.production-time');

        Route::get('/products/stock', function (Request $request) {
            $id = $request->get('id');

            if (!$id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID is required'
                ], 400);
            }

            $product = \App\Models\Product::with(['stock', 'familleStocks.famille'])->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $response = [
                'success' => true,
                'has_familles' => $product->has_familles,
                'unit' => $product->unit_of_measure
            ];

            if ($product->has_familles) {
                // For products with familles, return familles with their stock
                $familles = [];
                $totalAvailable = 0;

                foreach ($product->familleStocks as $familleStock) {
                    $available = $familleStock->current_quantity - $familleStock->reserved_quantity;
                    $totalAvailable += $available;

                    $familles[] = [
                        'famille_id' => $familleStock->famille_id,
                        'famille_name' => $familleStock->famille_name,
                        'famille_code' => $familleStock->famille ? $familleStock->famille->famille_code : '',
                        'current_quantity' => $familleStock->current_quantity,
                        'reserved_quantity' => $familleStock->reserved_quantity,
                        'available_quantity' => $available,
                        'location' => $familleStock->location,
                    ];
                }

                $response['familles'] = $familles;
                $response['available'] = $totalAvailable;
                $response['total_current'] = $product->familleStocks->sum('current_quantity');
                $response['total_reserved'] = $product->familleStocks->sum('reserved_quantity');
            } else {
                // For products without familles, return regular stock
                $available = $product->stock ?
                    $product->stock->current_quantity - $product->stock->reserved_quantity : 0;

                $response['available'] = $available;
                $response['current_quantity'] = $product->stock ? $product->stock->current_quantity : 0;
                $response['reserved_quantity'] = $product->stock ? $product->stock->reserved_quantity : 0;
                $response['location'] = $product->stock ? $product->stock->location : null;
            }

            return response()->json($response);
        })->name('products.stock');

        Route::get('/products/{product}/famille/{famille}/stock', function ($productId, $familleId) {
            $product = \App\Models\Product::find($productId);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $familleStock = \App\Models\ProductFamilleStock::where('product_id', $productId)
                ->where('famille_id', $familleId)
                ->first();

            if (!$familleStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Famille stock not found'
                ], 404);
            }

            $available = $familleStock->current_quantity - $familleStock->reserved_quantity;

            return response()->json([
                'success' => true,
                'data' => [
                    'famille_id' => $familleStock->famille_id,
                    'famille_name' => $familleStock->famille_name,
                    'current_quantity' => $familleStock->current_quantity,
                    'reserved_quantity' => $familleStock->reserved_quantity,
                    'available_quantity' => $available,
                    'location' => $familleStock->location,
                ]
            ]);
        })->name('products.famille.stock');

        Route::get('/products/{product}/familles', [ProductionOrderController::class, 'getProductFamilles']);

        Route::get('/products/{id}', function ($id) {
            $product = \App\Models\Product::with('stock')->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'product' => $product,
                'stock' => $product->stock,
                'unit_of_measure' => $product->unit_of_measure,
                'production_time_days' => $product->production_time_days
            ]);
        })->name('products.show');
    });
});
