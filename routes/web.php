<?php

use Illuminate\Support\Facades\Route;
// ════════════════════════════════════════════════════════════════════════════
// routes/web.php  — Landing page + shared auth routes
// ════════════════════════════════════════════════════════════════════════════
use App\Http\Controllers\LandingController;

use App\Http\Controllers\Auth\VendorAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\AdminAuthController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Admin\VendorManagementController;
use App\Http\Controllers\Admin\CustomerManagementController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PlatformSettingsController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;

use App\Http\Controllers\Auth\OcrDocumentController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboard;
use App\Http\Controllers\Vendor\EmployeeController;
use App\Http\Controllers\Vendor\InventoryController;
use App\Http\Controllers\Vendor\PosController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\RoleController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\SubscriptionController;
use App\Http\Controllers\Vendor\ServiceCatalogController;
use App\Http\Controllers\Vendor\ServiceController;
use App\Http\Controllers\Vendor\ChatController as VendorChatController;
use App\Http\Controllers\Employee\HrSelfServiceController;
use App\Http\Controllers\Vendor\HrAttendanceController;
use App\Http\Controllers\Vendor\HrEmployeeController;
use App\Http\Controllers\Vendor\HrLeaveController;
use App\Http\Controllers\Vendor\HrPayrollController;
use App\Http\Controllers\Vendor\ReviewController as VendorReviewController;
use App\Http\Controllers\Vendor\WarrantyController as VendorWarrantyController;

use App\Http\Controllers\Vendor\DeliveryController as VendorDeliveryController;
use App\Http\Controllers\Vendor\SupplierController as VendorSupplierController;

use App\Http\Controllers\Customer\{
    CartController, CustomerLocationController, CustomerSolarContractController, CustomerSolarController, OrderController,
    VendorBrowseController, ProductBrowseController,
    ServiceBookingController
};
use App\Http\Controllers\Customer\ChatController as CustomerChatController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Customer\WarrantyController as CustomerWarrantyController;
use App\Http\Controllers\Supplier\SupplierOrderController;
use App\Http\Controllers\Supplier\SupplierProductController;
use App\Http\Controllers\Vendor\PurchaseOrderController;
use App\Http\Controllers\Vendor\PurchaseRequestController;
use App\Http\Controllers\Vendor\StoreSettingsController;
use App\Http\Controllers\Vendor\DeliveryController as VendorDeliveryController;
use App\Http\Controllers\Vendor\VendorSolarContractController;
use App\Http\Controllers\Vendor\VendorSolarController;
use Illuminate\Support\Facades\Mail;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [LandingController::class, 'index'])->name('home');
Route::post('/vendor/validate-document', [OcrDocumentController::class, 'validate'])->name('vendor.validate.document');

// ── Admin auth (separate from vendor/customer) ────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login',   [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login',  [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// ── Vendor auth ───────────────────────────────────────────────────────────
Route::prefix('vendor')->name('vendor.')->group(function () {
    Route::get('register',        [VendorAuthController::class, 'showRegister'])->name('register');
    Route::post('register',       [VendorAuthController::class, 'register']);
    Route::get('register/status', [VendorAuthController::class, 'status'])->name('register.status')->middleware('auth');
    Route::get('login',           [VendorAuthController::class, 'showLogin'])->name('login');
    Route::post('login',          [VendorAuthController::class, 'login']);
    Route::post('logout',         [VendorAuthController::class, 'logout'])->name('logout')->middleware('auth');
});

// ── Customer auth ─────────────────────────────────────────────────────────
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('register',  [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [CustomerAuthController::class, 'register']);
    Route::get('login',     [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('login',    [CustomerAuthController::class, 'login']);
    Route::post('logout',   [CustomerAuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::post('customers/{customer}/verify',  [CustomerManagementController::class, 'verify'])->name('verify');
    Route::post('customers/{customer}/restore', [CustomerManagementController::class, 'restore'])->name('restore');
});

// Generic /login redirect
Route::get('/login', fn () => redirect()->route('customer.login'))->name('login');


// ════════════════════════════════════════════════════════════════════════════
// routes/admin.php  — All admin portal routes
// Registered in bootstrap/app.php with prefix 'admin' + middleware 'admin'
// ════════════════════════════════════════════════════════════════════════════


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Vendor approval queue
    Route::prefix('vendors/pending')->name('vendors.')->group(function () {
        Route::get('/',                              [VendorApprovalController::class, 'index'])->name('pending');
        Route::get('{vendor}',                       [VendorApprovalController::class, 'show'])->name('show');
        Route::post('{vendor}/approve',              [VendorApprovalController::class, 'approve'])->name('approve');
        Route::post('{vendor}/reject',               [VendorApprovalController::class, 'reject'])->name('reject');
        Route::post('{vendor}/request-revision',     [VendorApprovalController::class, 'requestRevision'])->name('revision');
    });

    // Vendor management (active vendors)
    Route::resource('vendors', VendorManagementController::class)->except(['create', 'store']);
    Route::post('vendors/{vendor}/suspend',    [VendorManagementController::class, 'suspend'])->name('vendors.suspend');
    Route::post('vendors/{vendor}/reactivate', [VendorManagementController::class, 'reactivate'])->name('vendors.reactivate');

    // Customer management
    Route::resource('customers', CustomerManagementController::class)->only(['index', 'show']);
    Route::post('customers/{customer}/suspend', [CustomerManagementController::class, 'suspend'])->name('customers.suspend');
    Route::post('customers/{customer}/restore', [CustomerManagementController::class, 'restore'])->name('customers.restore');
    Route::post('customers/{customer}/verify',  [CustomerManagementController::class, 'verify']) ->name('customers.verify');

    Route::get('orders',         [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show']) ->name('orders.show');


    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/',                        [AdminSubscriptionController::class, 'index'])            ->name('index');
        Route::post('{subscription}/extend',   [AdminSubscriptionController::class, 'extendSubscription'])->name('extend');
        Route::post('{subscription}/cancel',   [AdminSubscriptionController::class, 'cancelSubscription'])->name('cancel');
        Route::get('plans',                    [AdminSubscriptionController::class, 'plans'])            ->name('plans');
        Route::get('plans/create',             [AdminSubscriptionController::class, 'createPlan'])       ->name('plans.create');
        Route::post('plans',                   [AdminSubscriptionController::class, 'storePlan'])        ->name('plans.store');
        Route::get('plans/{plan}/edit',        [AdminSubscriptionController::class, 'editPlan'])         ->name('plans.edit');
        Route::put('plans/{plan}',             [AdminSubscriptionController::class, 'updatePlan'])       ->name('plans.update');
        Route::post('plans/{plan}/toggle',     [AdminSubscriptionController::class, 'togglePlan'])       ->name('plans.toggle');
    });

    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/',              [AdminReviewController::class, 'index'])  ->name('index');
        Route::post('{review}/hide', [AdminReviewController::class, 'hide'])   ->name('hide');
        Route::delete('{review}',    [AdminReviewController::class, 'delete']) ->name('delete');
    });

        // Platform Settings
        Route::get('settings',  [PlatformSettingsController::class, 'index']) ->name('settings.index');
        Route::put('settings',  [PlatformSettingsController::class, 'update'])->name('settings.update');

    // AJAX stats
    Route::get('vendors/stats', [VendorApprovalController::class, 'stats'])->name('vendors.stats');
});


// ════════════════════════════════════════════════════════════════════════════
// routes/vendor.php  — All vendor portal routes
// Middleware stack: auth → vendor → vendor.verified → subscription.active
// ════════════════════════════════════════════════════════════════════════════

Route::middleware(['auth', 'vendor', 'vendor.verified'])->prefix('vendor')->name('vendor.')->group(function () {

    // Subscription page — accessible after approval but BEFORE active status
    Route::get('subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('subscription/success',   [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('subscription/cancel',    [SubscriptionController::class, 'cancel'])->name('subscription.cancel');


    // Everything below also requires an active subscription
    Route::middleware(['subscription.active', 'module.access'])->group(function () {
        Route::get('dashboard', [VendorDashboard::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('products/{product}/update',                 [ProductController::class, 'update'])   ->name('products.update');
        Route::post('products/{product}/toggle-status',          [ProductController::class, 'toggleStatus'])   ->name('products.toggle-status');
        Route::post('products/{product}/images/delete',          [ProductController::class, 'deleteImage'])    ->name('products.images.delete');
        Route::post('products/{product}/images/primary',         [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');

        // ── Inventory ─────────────────────────────────────────────────────────────
        Route::prefix('inventory')->name('inventory.')->group(function () {

            Route::get('/',                              [InventoryController::class, 'index'])          ->name('index');
            Route::get('movements',                      [InventoryController::class, 'movements'])      ->name('movements');
            Route::get('{inventory}',                    [InventoryController::class, 'show'])           ->name('show');
            Route::post('{inventory}/adjust',            [InventoryController::class, 'adjust'])         ->name('adjust');
            Route::post('{inventory}/add',               [InventoryController::class, 'addStock'])       ->name('add');
            Route::post('{inventory}/settings',          [InventoryController::class, 'updateSettings']) ->name('settings');
            Route::get('datatable',                      [InventoryController::class, 'datatable'])      ->name('datatable');
        });

        // ── Employees ─────────────────────────────────────────────────────────────
        Route::resource('employees', EmployeeController::class)->except(['show']);
        Route::post('employees/{employee}/toggle',          [EmployeeController::class, 'toggleActive'])  ->name('employees.toggle');
        Route::patch('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');

        // ── Roles ─────────────────────────────────────────────────────────────────
        Route::resource('roles', RoleController::class)->except(['show', 'create', 'edit']);
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');

        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/',                [PosController::class, 'index'])   ->name('index');
            Route::get('/history',         [PosController::class, 'history']) ->name('history');
            Route::get('/search',          [PosController::class, 'search'])  ->name('search');
            Route::get('/scan',            [PosController::class, 'scanBarcode'])->name('scan');
            Route::get('/grid',            [PosController::class, 'productGrid'])->name('grid');
            Route::post('/process',        [PosController::class, 'process']) ->name('process');
            Route::get('/{transaction}/receipt', [PosController::class, 'receipt'])->name('receipt');
            Route::post('/{transaction}/void',   [PosController::class, 'void'])   ->name('void');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/',                   [VendorOrderController::class, 'index'])         ->name('index');
            Route::get('/{order}',            [VendorOrderController::class, 'show'])          ->name('show');
            Route::post('/{order}/status',    [VendorOrderController::class, 'updateStatus'])  ->name('status');
            Route::post('/{order}/delivery',  [VendorOrderController::class, 'assignDelivery'])->name('delivery');
            Route::post('/{order}/delivered', [VendorOrderController::class, 'markDelivered']) ->name('delivered');
            Route::get('/{order}/invoice',    [VendorOrderController::class, 'invoice'])       ->name('invoice');
        });

        // ── Delivery ─────────────────────────────────────────────────────────────
        Route::prefix('delivery')->name('delivery.')->group(function () {
            Route::get('/',                    [VendorDeliveryController::class, 'index'])        ->name('index');
            Route::get('/{delivery}',          [VendorDeliveryController::class, 'show'])         ->name('show');
            Route::patch('/{delivery}/status',  [VendorDeliveryController::class, 'updateStatus'])->name('status');
        });

        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/',                                  [ServiceController::class, 'index'])       ->name('index');
            Route::get('/{serviceRequest}',                  [ServiceController::class, 'show'])        ->name('show');
            Route::post('/{serviceRequest}/status',          [ServiceController::class, 'updateStatus'])->name('status');
            Route::post('/{serviceRequest}/quote',           [ServiceController::class, 'submitQuote']) ->name('quote');
            Route::post('/{serviceRequest}/proof',           [ServiceController::class, 'uploadProof']) ->name('proof.upload');
            Route::delete('/{serviceRequest}/proof/{proof}', [ServiceController::class, 'deleteProof']) ->name('proof.delete');
            Route::post('/{serviceRequest}/assignees',        [ServiceController::class, 'updateAssignees'])->name('assignees');
        });

        Route::prefix('service-catalog')->name('service-catalog.')->group(function () {
            Route::get('/',                        [ServiceCatalogController::class, 'index'])         ->name('index');
            Route::post('/',                       [ServiceCatalogController::class, 'store'])         ->name('store');
            Route::get('/{vendorService}/edit',    [ServiceCatalogController::class, 'edit'])          ->name('edit');
            Route::put('/{vendorService}',         [ServiceCatalogController::class, 'update'])        ->name('update');
            Route::delete('/{vendorService}',      [ServiceCatalogController::class, 'destroy'])       ->name('destroy');
            Route::post('/{vendorService}/toggle', [ServiceCatalogController::class, 'toggle'])        ->name('toggle');
            Route::post('/{vendorService}/featured',[ServiceCatalogController::class, 'toggleFeatured'])->name('featured');
            Route::post('/reorder',                [ServiceCatalogController::class, 'reorder'])       ->name('reorder');
        });

        Route::prefix('chat')->name('chat.')->group(function () {
            // Inbox (no room selected)
            Route::get('/', [VendorChatController::class, 'index'])->name('index');

            // Open a specific conversation
            Route::get('/{chatRoom}', [VendorChatController::class, 'show'])->name('show');

            // Send a message
            Route::post('/{chatRoom}/send', [VendorChatController::class, 'send'])->name('send');

            // Poll for new messages (called every 3s by JS)
            Route::get('/{chatRoom}/poll', [VendorChatController::class, 'poll'])->name('poll');

            // Typing indicator
            Route::post('/{chatRoom}/typing', [VendorChatController::class, 'typing'])->name('typing');

            // Quick reply / predefined templates management
            Route::get('/settings/predefined', [VendorChatController::class, 'predefined'])->name('predefined');
            Route::post('/settings/predefined', [VendorChatController::class, 'storePredefined'])->name('predefined.store');
            Route::delete('/settings/predefined/{reply}', [VendorChatController::class, 'destroyPredefined'])->name('predefined.destroy');
        });

        Route::prefix('hr')->name('hr.')->group(function () {

            // ── Employee Records (HR Officer) ─────────────────────────────────
            Route::prefix('employees')->name('employees.')->group(function () {
                Route::get('/',                              [HrEmployeeController::class, 'index'])  ->name('index');
                Route::get('/create',                        [HrEmployeeController::class, 'create']) ->name('create');
                Route::post('/',                             [HrEmployeeController::class, 'store'])  ->name('store');
                Route::get('/{profile}',                     [HrEmployeeController::class, 'show'])   ->name('show');
                Route::get('/{profile}/edit',                [HrEmployeeController::class, 'edit'])   ->name('edit');
                Route::put('/{profile}',                     [HrEmployeeController::class, 'update']) ->name('update');
                Route::patch('/{profile}/archive',           [HrEmployeeController::class, 'archive'])->name('archive');
            });

            // ── Payroll ────────────────────────────────────────────────────────
            Route::prefix('payroll')->name('payroll.')->group(function () {
                Route::get('/',                              [HrPayrollController::class, 'index'])         ->name('index');
                Route::get('/create',                        [HrPayrollController::class, 'create'])        ->name('create');
                Route::post('/',                             [HrPayrollController::class, 'store'])         ->name('store');
                Route::get('/{payrollPeriod}',               [HrPayrollController::class, 'show'])          ->name('show');
                Route::post('/{payrollPeriod}/compute',      [HrPayrollController::class, 'compute'])       ->name('compute');
                Route::post('/{payrollPeriod}/submit',       [HrPayrollController::class, 'submitApproval'])->name('submit');
                Route::post('/{payrollPeriod}/approve',      [HrPayrollController::class, 'approve'])       ->name('approve');
                Route::get('/{payrollPeriod}/export',        [HrPayrollController::class, 'export'])        ->name('export');
            });

            // ── Attendance ─────────────────────────────────────────────────────
            Route::prefix('attendance')->name('attendance.')->group(function () {
                Route::get('/',                              [HrAttendanceController::class, 'index'])         ->name('index');
                Route::post('/',                             [HrAttendanceController::class, 'store'])         ->name('store');
                Route::get('/poll',                          [HrAttendanceController::class, 'poll'])          ->name('poll');  // ← ADD
                Route::get('/report',                        [HrAttendanceController::class, 'report'])        ->name('report');
                Route::get('/settings',                      [HrAttendanceController::class, 'settings'])      ->name('settings');
                Route::post('/settings',                     [HrAttendanceController::class, 'updateSettings'])->name('settings.update');
            });

            // ── Leave & Overtime Approvals ─────────────────────────────────────
            Route::prefix('leaves')->name('leaves.')->group(function () {
                Route::get('/',                              [HrLeaveController::class, 'leaveIndex'])    ->name('index');
                Route::post('/{leaveRequest}/approve',       [HrLeaveController::class, 'leaveApprove'])  ->name('approve');
                Route::get('/overtime',                      [HrLeaveController::class, 'overtimeIndex']) ->name('overtime');
                Route::post('/overtime/{overtimeRequest}/approve', [HrLeaveController::class, 'overtimeApprove'])->name('overtime.approve');
            });

            // ── Employee Self-Service ──────────────────────────────────────────
            Route::prefix('self')->name('self.')->group(function () {
                Route::get('/',                              [HrSelfServiceController::class, 'dashboard'])    ->name('dashboard');
                Route::post('/time-in',                      [HrSelfServiceController::class, 'timeIn'])       ->name('time-in');
                Route::post('/time-out',                     [HrSelfServiceController::class, 'timeOut'])      ->name('time-out');
                Route::get('/attendance',                    [HrSelfServiceController::class, 'myAttendance']) ->name('attendance');
                Route::get('/leaves',                        [HrSelfServiceController::class, 'myLeaves'])     ->name('leaves');
                Route::post('/leaves',                       [HrSelfServiceController::class, 'storeLeave'])   ->name('leaves.store');
                Route::patch('/leaves/{leaveRequest}/cancel',[HrSelfServiceController::class, 'cancelLeave'])  ->name('leaves.cancel');
                Route::get('/overtime',                      [HrSelfServiceController::class, 'myOvertime'])   ->name('overtime');
                Route::post('/overtime',                     [HrSelfServiceController::class, 'storeOvertime'])->name('overtime.store');
                Route::get('/payslips',                      [HrSelfServiceController::class, 'myPayslips'])   ->name('payslips');
                Route::get('/payslips/{payrollItem}',        [HrSelfServiceController::class, 'showPayslip'])  ->name('payslip.show');
            });

        });

        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/',                       [VendorReviewController::class, 'index'])       ->name('index');
            Route::post('/{review}/reply',        [VendorReviewController::class, 'reply'])       ->name('reply');
            Route::delete('/{review}/reply',      [VendorReviewController::class, 'deleteReply']) ->name('reply.delete');
            Route::post('/{review}/flag',         [VendorReviewController::class, 'flag'])        ->name('flag');
        });

        Route::prefix('warranty') ->name('warranty.')->group(function () {
            Route::get('/',                                 [VendorWarrantyController::class, 'index'])->name('index');
            Route::get('/{warrantyRequest}',                [VendorWarrantyController::class, 'show'])->name('show');
            Route::patch('/{warrantyRequest}/status',       [VendorWarrantyController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/{warrantyRequest}/proof',         [VendorWarrantyController::class, 'uploadProof'])->name('uploadProof');
            Route::post('/{warrantyRequest}/note',          [VendorWarrantyController::class, 'addNote'])->name('addNote');

        });

        Route::prefix('solar')->name('solar.')->group(function () {
            Route::get('/',                              [VendorSolarController::class, 'index'])          ->name('index');
            Route::get('/{solarProject}',                [VendorSolarController::class, 'show'])           ->name('show');
            Route::patch('/{solarProject}/status',       [VendorSolarController::class, 'updateStatus'])   ->name('status');
            Route::patch('/{solarProject}/assign',       [VendorSolarController::class, 'assignEngineer']) ->name('assign');
            Route::patch('/{solarProject}/schedule',     [VendorSolarController::class, 'schedule'])       ->name('schedule');
            Route::get('/{solarProject}/quotation/create',[VendorSolarController::class, 'createQuotation'])->name('quotation.create');
            Route::post('/{solarProject}/quotation',     [VendorSolarController::class, 'storeQuotation']) ->name('quotation.store');
            Route::post('/{solarProject}/proof',         [VendorSolarController::class, 'uploadProof'])    ->name('proof');
            Route::get('/{solarProject}/edit',           [VendorSolarController::class, 'edit'])->name('edit');
            Route::patch('/solar/{solarProject}',              [VendorSolarController::class, 'update'])->name('update');
            Route::post('/{solarProject}/note',          [VendorSolarController::class, 'addNote'])        ->name('note');
        });

        Route::prefix('solar/{solarProject}/contract')->name('solar.contract.')->group(function () {
            Route::get('/create',                                     [VendorSolarContractController::class, 'create'])             ->name('create');
            Route::post('/',                                          [VendorSolarContractController::class, 'store'])              ->name('store');
            Route::get('/{solarContract}',                            [VendorSolarContractController::class, 'show'])               ->name('show');
            Route::patch('/{solarContract}/sign',                     [VendorSolarContractController::class, 'sign'])               ->name('sign');
            Route::patch('/{solarContract}/adjustment/{adjustment}',  [VendorSolarContractController::class, 'respondAdjustment'])  ->name('adjustment.respond');
            Route::post('/{solarContract}/schedule/{schedule}/payment',[VendorSolarContractController::class, 'logPayment'])        ->name('payment');
            Route::get('/{solarContract}/invoice',                    [VendorSolarContractController::class, 'invoice'])            ->name('invoice');
        });

         Route::prefix('procurement/pr')->name('procurement.pr.')->group(function () {
            Route::get('/',        [PurchaseRequestController::class, 'index'])  ->name('index');
            Route::get('/create',  [PurchaseRequestController::class, 'create']) ->name('create');
            Route::post('/',       [PurchaseRequestController::class, 'store'])  ->name('store');

            // ↓ MUST be before /{pr} — static segments before wildcards
            Route::get('/supplier/{supplier}/products',
                [PurchaseRequestController::class, 'supplierProducts'])->name('supplier.products');

            Route::get('/{pr}',           [PurchaseRequestController::class, 'show'])   ->name('show');
            Route::post('/{pr}/submit',   [PurchaseRequestController::class, 'submit']) ->name('submit');
            Route::post('/{pr}/approve',  [PurchaseRequestController::class, 'approve'])->name('approve');
            Route::post('/{pr}/reject',   [PurchaseRequestController::class, 'reject']) ->name('reject');
            Route::post('/{pr}/cancel',   [PurchaseRequestController::class, 'cancel']) ->name('cancel');

        });

        // ── Purchase Orders ──────────────────────────────────────────────────
        Route::prefix('procurement/po')->name('procurement.po.')->group(function () {
            Route::get('/',                          [PurchaseOrderController::class, 'index'])          ->name('index');
            Route::get('/create-from-pr/{pr}',       [PurchaseOrderController::class, 'createFromPR'])   ->name('createFromPR');
            Route::post('/',                         [PurchaseOrderController::class, 'store'])          ->name('store');
            Route::get('/{order}',                   [PurchaseOrderController::class, 'show'])           ->name('show');
            Route::post('/{order}/submit',           [PurchaseOrderController::class, 'submit'])         ->name('submit');
            Route::post('/{order}/confirm-delivery', [PurchaseOrderController::class, 'confirmDelivery'])->name('confirmDelivery');
            Route::post('/{order}/cancel',           [PurchaseOrderController::class, 'cancel'])         ->name('cancel');
        });

        Route::prefix('vendor/suppliers')->name('suppliers.')->group(function () {

            Route::get('/',                     [VendorSupplierController::class, 'index'])->name('index');
            Route::get('/create',               [VendorSupplierController::class, 'create'])->name('create');
            Route::post('/store',               [VendorSupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}',           [VendorSupplierController::class, 'show'])->name('show');
            Route::get('/{supplier}/edit',      [VendorSupplierController::class, 'edit'])->name('edit');
            Route::patch('/{supplier}/update',  [VendorSupplierController::class, 'edit'])->name('update');
            Route::delete('/{supplier}/destroy',[VendorSupplierController::class, 'destroy'])->name('destroy');

            Route::post('suppliers/{supplier}/toggle-preferred',[VendorSupplierController::class, 'togglePreferred'])->name('togglePreferred');
            Route::post('suppliers/{supplier}/status', [VendorSupplierController::class, 'updateStatus'])->name('updateStatus');

        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/',              [StoreSettingsController::class, 'index'])             ->name('index');
            Route::put('/branding',      [StoreSettingsController::class, 'updateBranding'])    ->name('branding');
            Route::put('/highlights',    [StoreSettingsController::class, 'updateHighlights'])  ->name('highlights');
            Route::put('/contact',       [StoreSettingsController::class, 'updateContact'])     ->name('contact');
            Route::put('/location',      [StoreSettingsController::class, 'updateLocation'])    ->name('location');
            Route::put('/hours',         [StoreSettingsController::class, 'updateHours'])       ->name('hours');
            Route::put('/policies',      [StoreSettingsController::class, 'updatePolicies'])    ->name('policies');
            Route::put('/preferences',   [StoreSettingsController::class, 'updatePreferences']) ->name('preferences');
        });

        Route::get('/test-mail', function () {
            try {
                Mail::raw('Test email from Laravel!', function ($m) {
                    $m->to('joey.ametin@gmail.com')->subject('Test Mail');
                });
                return 'Mail sent successfully!';
            } catch (\Exception $e) {
                return 'Mail failed: ' . $e->getMessage();
            }
        });
    });

});


// ════════════════════════════════════════════════════════════════════════════
// routes/customer.php  — All customer portal routes
// ════════════════════════════════════════════════════════════════════════════


Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('dashboard', [CustomerDashboard::class, 'index'])->name('dashboard');

    // Cart
    Route::get('cart',            [CartController::class, 'index'])   ->name('cart.index');
    Route::post('cart/add',       [CartController::class, 'add'])     ->name('cart.add');
    Route::post('cart/update',    [CartController::class, 'update'])  ->name('cart.update');
    Route::post('cart/remove',    [CartController::class, 'remove'])  ->name('cart.remove');
    Route::post('cart/clear',     [CartController::class, 'clear'])   ->name('cart.clear');
    Route::get('checkout',        [CartController::class, 'checkout'])->name('checkout');


    // Orders
    Route::resource('orders', OrderController::class)->only(['index','show','store']);
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('orders/{order}/paypal/success', [OrderController::class, 'paypalSuccess'])->name('orders.paypal.success');
    Route::get('orders/{order}/paypal/cancel',  [OrderController::class, 'paypalCancel']) ->name('orders.paypal.cancel');

    // Vendors
    Route::get('vendors',          [VendorBrowseController::class, 'index'])->name('vendors.index');
    Route::get('vendors/{vendor}', [VendorBrowseController::class, 'show']) ->name('vendors.show');

    // Products
    Route::get('products',           [ProductBrowseController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [ProductBrowseController::class, 'show']) ->name('products.show');

    // Services
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/',                        [ServiceBookingController::class, 'index'])  ->name('index');
        Route::get('/book',                    [ServiceBookingController::class, 'create']) ->name('create');
        Route::post('/book',                   [ServiceBookingController::class, 'store'])  ->name('store');
        Route::get('/{serviceRequest}',        [ServiceBookingController::class, 'show'])   ->name('show');
        Route::post('/{serviceRequest}/action',[ServiceBookingController::class, 'action'])->name('action');
    });

    Route::prefix('chat')->name('chat.')->group(function () {
        // Inbox
        Route::get('/', [CustomerChatController::class, 'index'])->name('index');

        // Open / start a conversation with a specific vendor
        Route::get('/vendor/{vendor}', [CustomerChatController::class, 'show'])->name('show');

        // Send a message
        Route::post('/vendor/{vendor}/send', [CustomerChatController::class, 'send'])->name('send');

        // Trigger a predefined auto-reply
        Route::post('/vendor/{vendor}/auto-reply', [CustomerChatController::class, 'autoReply'])->name('auto-reply');

        // Poll for new messages
        Route::get('/vendor/{vendor}/poll', [CustomerChatController::class, 'poll'])->name('poll');

        // Typing indicator
        Route::post('/vendor/{vendor}/typing', [CustomerChatController::class, 'typing'])->name('typing');
    });


    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/',                                          [CustomerReviewController::class, 'index'])          ->name('index');
        Route::get('/order/{order}/create',                      [CustomerReviewController::class, 'createForOrder']) ->name('order.create');
        Route::post('/order/{order}',                            [CustomerReviewController::class, 'storeForOrder'])  ->name('order.store');
        Route::get('/service/{serviceRequest}/create',           [CustomerReviewController::class, 'createForService'])->name('service.create');
        Route::post('/service/{serviceRequest}',                 [CustomerReviewController::class, 'storeForService'])->name('service.store');
    });

    Route::prefix('warranty') ->name('warranty.')->group(function () {
        Route::get('/', [CustomerWarrantyController::class, 'index'])
            ->name('index');

        Route::get('/create', [CustomerWarrantyController::class, 'create'])
            ->name('create');

        Route::post('/store', [CustomerWarrantyController::class, 'store'])
            ->name('store');

        Route::get('/{warrantyRequest}', [CustomerWarrantyController::class, 'show'])
            ->name('show');

        Route::post('/{warrantyRequest}/cancel', [CustomerWarrantyController::class, 'cancel'])
            ->name('cancel');

        Route::get('/shops/{vendor}/warranty/terms', [CustomerWarrantyController::class, 'terms'])
            ->name('customer.warranty.terms');

    });

    Route::prefix('solar')->name('solar.')->group(function () {
        Route::get('/',                                          [CustomerSolarController::class, 'index'])           ->name('index');
        Route::get('/create',                                    [CustomerSolarController::class, 'create'])          ->name('create');
        Route::post('/',                                         [CustomerSolarController::class, 'store'])           ->name('store');
        Route::get('/solar/paypal/success/{project}',                      [CustomerSolarController::class, 'paypalSuccess']) ->name('paypal.success');
        Route::get('/solar/paypal/cancel',                       [CustomerSolarController::class, 'paypalCancel'])  ->name('paypal.cancel');
        Route::get('/solar/{project}/invoice',                   [CustomerSolarController::class, 'invoice'])     ->name('invoice');

        Route::get('/{solarProject}',                            [CustomerSolarController::class, 'show'])            ->name('show');
        Route::patch('/{solarProject}/quotation/{solarQuotation}/respond', [CustomerSolarController::class, 'respondQuotation'])->name('quotation.respond');

        Route::post('/{solarProject}/documents',                 [CustomerSolarController::class, 'uploadDocument'])  ->name('documents.store');
    });

    Route::prefix('solar/{solarProject}/contract')->name('solar.contract.')->group(function () {
        Route::get('/{solarContract}',                            [CustomerSolarContractController::class, 'show'])             ->name('show');
        Route::patch('/{solarContract}/approve',                  [CustomerSolarContractController::class, 'approve'])          ->name('approve');
        Route::post('/{solarContract}/adjust',                    [CustomerSolarContractController::class, 'requestAdjustment'])->name('adjust');
        Route::get('/{solarContract}/invoice',                    [CustomerSolarContractController::class, 'invoice'])          ->name('invoice');
    });
    Route::get('/solar/{solarProject}/review',                    [CustomerSolarContractController::class, 'reviewCreate'])     ->name('solar.review.create');
    Route::post('/solar/{solarProject}/review',                   [CustomerSolarContractController::class, 'reviewStore'])      ->name('solar.review.store');

    Route::post('location/update',         [CustomerLocationController::class, 'update'])        ->name('location.update');
    Route::get('location/reverse-geocode', [CustomerLocationController::class, 'reverseGeocode'])->name('location.reverse-geocode');


});


Route::middleware(['auth', 'supplier'])->prefix('supplier')->name('supplier.')->group(function () {

    Route::get('dashboard', fn() => view('supplier.dashboard'))->name('dashboard');

    // Product catalogue
    Route::resource('products', SupplierOrderController::class);

    Route::delete('products/images/{image}', [SupplierProductController::class, 'destroyImage'])
        ->name('products.images.destroy');

    // Purchase Order management (supplier side)
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/',                            [SupplierOrderController::class, 'index'])         ->name('index');
        Route::get('/{id}',                        [SupplierOrderController::class, 'show'])          ->name('show');
        Route::post('/{id}/approve',               [SupplierOrderController::class, 'approve'])       ->name('approve');
        Route::post('/{id}/reject',                [SupplierOrderController::class, 'reject'])        ->name('reject');
        Route::post('/{id}/mark-processing',       [SupplierOrderController::class, 'markProcessing'])->name('markProcessing');
        Route::post('/{id}/mark-shipped',          [SupplierOrderController::class, 'markShipped'])   ->name('markShipped');
        Route::post('/{id}/mark-delivered',        [SupplierOrderController::class, 'markDelivered']) ->name('markDelivered');
    });

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/',                            [SupplierProductController::class, 'index'])         ->name('index');
        Route::get('/create',                       [SupplierProductController::class, 'create'])->name('create');

        Route::post('/', [SupplierProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [SupplierProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [SupplierProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [SupplierProductController::class, 'destroy'])->name('destroy');
        Route::delete('/image/{image}', [SupplierProductController::class, 'destroyImage'])->name('image.destroy');

    });
});
