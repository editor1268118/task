<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Finance\DashboardController as FinanceDashboardController;
use App\Http\Controllers\Management\DashboardController as ManagementDashboardController;
use App\Http\Controllers\Crm\CustomerController;
use App\Http\Controllers\Crm\CustomerDocumentController;
use App\Http\Controllers\Crm\CustomerInteractionController;
use App\Http\Controllers\Crm\CustomerReportController;
use App\Http\Controllers\Operations\MasterOperationsBoardController;
use App\Http\Controllers\Sales\QueryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Amigos TMS — Web Routes
| All routes are organized by role with middleware protection.
|
*/

// ─── Public ────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
});

// ─── Authenticated Routes ──────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard redirect based on role
    Route::get('/dashboard', [DashboardRedirectController::class, 'index'])->name('dashboard');

    // ─── Super Admin Routes ────────────────────────────────────────
    Route::prefix('admin')
        ->middleware('role:super-admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::post('/cache/clear', [AdminDashboardController::class, 'clearCache'])->name('cache.clear');

            // Phase 2: Organization & User Management
            Route::resource('users', App\Http\Controllers\Admin\UserController::class);
            Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
            Route::resource('designations', App\Http\Controllers\Admin\DesignationController::class);
            Route::resource('task-types', App\Http\Controllers\Admin\TaskTypeController::class)->except(['show']);
            Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
        });

    // ─── Manager Routes ────────────────────────────────────────────
    Route::prefix('manager')
        ->middleware('role:manager')
        ->name('manager.')
        ->group(function () {
            Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        });

    Route::prefix('finance')
        ->name('finance.')
        ->group(function () {
            Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');
            Route::get('/ledger', [FinanceDashboardController::class, 'ledger'])->name('ledger');
            Route::get('/queue', [FinanceDashboardController::class, 'queue'])->name('queue');
        });

    Route::prefix('management')
        ->middleware('role:super-admin|manager')
        ->name('management.')
        ->group(function () {
            Route::get('/dashboard', [ManagementDashboardController::class, 'index'])->name('dashboard');
        });

    Route::prefix('operations')
        ->middleware('role:super-admin|manager|finance')
        ->name('operations.')
        ->group(function () {
            Route::get('master-board', [MasterOperationsBoardController::class, 'index'])->name('master-board.index');
            Route::get('master-board/export/{format}', [MasterOperationsBoardController::class, 'export'])->name('master-board.export');
            Route::get('master-board/print', [MasterOperationsBoardController::class, 'print'])->name('master-board.print');
            Route::post('master-board/filters', [MasterOperationsBoardController::class, 'saveFilter'])->name('master-board.filters.store');
            Route::post('master-board/columns', [MasterOperationsBoardController::class, 'saveColumns'])->name('master-board.columns.store');
        });

    Route::prefix('crm')->name('crm.')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::get('interactions', [CustomerInteractionController::class, 'index'])->name('interactions.index');
        Route::post('customers/{customer}/interactions', [CustomerInteractionController::class, 'store'])->name('customers.interactions.store');
        Route::post('customers/{customer}/documents', [CustomerDocumentController::class, 'store'])->name('customers.documents.store');
        Route::get('documents/{document}/download', [CustomerDocumentController::class, 'download'])->name('documents.download');
        Route::get('reports/customers', [CustomerReportController::class, 'index'])->name('reports.customers');
    });

    Route::prefix('sales')
        ->middleware('role:super-admin|manager|employee')
        ->name('sales.')
        ->group(function () {
            Route::get('queries/dashboard', [QueryController::class, 'dashboard'])->name('queries.dashboard');
            Route::get('queries/followups', [QueryController::class, 'followups'])->name('queries.followups');
            Route::get('queries/reports', [QueryController::class, 'reports'])->name('queries.reports');
            Route::get('queries/export/{format}', [QueryController::class, 'export'])->name('queries.export');
            Route::get('queries/print', [QueryController::class, 'print'])->name('queries.print');
            Route::post('queries/{query}/followups', [QueryController::class, 'addFollowup'])->name('queries.followups.store');
            Route::post('queries/{query}/discussions', [QueryController::class, 'addDiscussion'])->name('queries.discussions.store');
            Route::put('queries/{query}/discussions/{discussion}', [QueryController::class, 'updateDiscussion'])->name('queries.discussions.update');
            Route::delete('queries/{query}/discussions/{discussion}', [QueryController::class, 'deleteDiscussion'])->name('queries.discussions.destroy');
            Route::patch('queries/{query}/quick-status', [QueryController::class, 'quickStatus'])->name('queries.quick-status');
            Route::post('queries/{query}/reassign', [QueryController::class, 'reassign'])->name('queries.reassign');
            Route::post('queries/{query}/convert', [QueryController::class, 'convert'])->name('queries.convert');
            Route::resource('queries', QueryController::class);
        });

    // ─── Unified Task Management Routes ────────────────────────────
    Route::resource('tasks', App\Http\Controllers\TaskController::class);
    Route::patch('tasks/{task}/status', [App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.status.update');
    
    // Task Comments
    Route::post('tasks/{task}/comments', [App\Http\Controllers\TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('comments/{comment}', [App\Http\Controllers\TaskCommentController::class, 'destroy'])->name('comments.destroy');
    
    // Task Attachments
    Route::post('tasks/{task}/attachments', [App\Http\Controllers\TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::get('attachments/{attachment}/download', [App\Http\Controllers\TaskAttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('attachments/{attachment}', [App\Http\Controllers\TaskAttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Financial ledger entries remain available after operational completion.
    Route::post('tasks/{task}/receipts', [App\Http\Controllers\TaskFinanceController::class, 'storeReceipt'])->name('tasks.receipts.store');
    Route::post('tasks/{task}/vendor-payments', [App\Http\Controllers\TaskFinanceController::class, 'storeVendorPayment'])->name('tasks.vendor-payments.store');
    Route::post('finance/receipts/{receipt}/approve', [App\Http\Controllers\TaskFinanceController::class, 'approveReceipt'])->name('finance.receipts.approve');
    Route::post('finance/vendor-payments/{payment}/approve', [App\Http\Controllers\TaskFinanceController::class, 'approveVendorPayment'])->name('finance.vendor-payments.approve');
    Route::post('tasks/{task}/finance-approve', [App\Http\Controllers\TaskFinanceController::class, 'approveFinance'])->name('tasks.finance.approve');
    Route::post('tasks/{task}/management-close', [App\Http\Controllers\TaskFinanceController::class, 'approveManagementAndClose'])->name('tasks.management.close');

    // ─── Review & Approval Center ────────────────────────────────────
    Route::get('reviews', [App\Http\Controllers\ReviewCenterController::class, 'index'])->name('reviews.index');
    Route::post('reviews/{task}/action', [App\Http\Controllers\ReviewCenterController::class, 'action'])->name('reviews.action');

    // ─── Completion Workflow ─────────────────────────────────────────
    Route::prefix('tasks/{task}/completion')->name('tasks.completion.')->group(function () {
        Route::post('start', [App\Http\Controllers\CompletionWorkflowController::class, 'start'])->name('start');
        Route::get('wizard/{step?}', [App\Http\Controllers\CompletionWorkflowController::class, 'wizard'])->name('wizard');
        Route::post('wizard/{step}', [App\Http\Controllers\CompletionWorkflowController::class, 'storeStep'])->name('storeStep');
        Route::post('wizard/{step}/draft', [App\Http\Controllers\CompletionWorkflowController::class, 'saveDraft'])->name('saveDraft');
        Route::get('review', [App\Http\Controllers\CompletionWorkflowController::class, 'review'])->name('review');
        Route::post('complete', [App\Http\Controllers\CompletionWorkflowController::class, 'complete'])->name('complete');
    });

    // ─── Google Sync ──────────────────────────────────────────────────
    // Route::post('google-sync/run', [App\Http\Controllers\GoogleSyncController::class, 'runQueue'])->name('google-sync.run');
    // Route::post('google-sync/headers', [App\Http\Controllers\GoogleSyncController::class, 'initHeaders'])->name('google-sync.headers');


    // ─── Notifications ───────────────────────────────────────────────
    Route::get('notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notifications/read-all', [App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.readAll');

    // ─── Reports & Analytics ───────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('productivity', [App\Http\Controllers\ReportController::class, 'productivity'])->name('productivity');
        Route::get('department', [App\Http\Controllers\ReportController::class, 'department'])->name('department');
        Route::get('workload', [App\Http\Controllers\ReportController::class, 'workload'])->name('workload');
        Route::get('audit', [App\Http\Controllers\ReportController::class, 'audit'])->name('audit');
    });

    // ─── Employee Routes ───────────────────────────────────────────
    Route::prefix('employee')
        ->middleware('role:employee')
        ->name('employee.')
        ->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Employee\DashboardController::class, 'index'])->name('dashboard');
        });

    // ─── Profile Routes ────────────────────────────────────────────
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/photo/{user}', [App\Http\Controllers\ProfileController::class, 'showPhoto'])->name('profile.photo.show');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/photo', [App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── Auth Routes (Breeze) ──────────────────────────────────────────
require __DIR__.'/auth.php';
