<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BillsController;
use App\Http\Controllers\ConcessionairesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ReceiptsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Psy\Readline\Transient;

use function Pest\Laravel\get;

Route::group(['prefix' => 'admin', 'middleware' => (['user.auth', 'verify'])], function () {
    
    //Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    //Customer Payments
    Route::match(['get', 'post'], '/payments/new', [PaymentsController::class, 'CustomerPayment'])->name('payments.customer.new');
    //List of pending payments
    Route::get('/payments/pending', [PaymentsController::class, 'GetPendingPaymentsList'])->name('payments.pending');
    //View Details of Pending Payments and Edit them before approving payment
    Route::match(['get', 'put'], '/payments/pending/{transactionId}', [PaymentsController::class, 'updateUnpaidTransaction'])->name('payments.update');
    //Delete Unpaid Payment
    Route::delete('/transactions/{id}/disapprove', [PaymentsController::class, 'disapproveTransaction'])->name('payments.disapprove');

    //Transaction Managements
    Route::get('/transactions/history', [TransactionsController::class, 'GetTransactionsHistory'])->name('receipts.list');
    Route::get('/transactions/customer/receipt/{id}', [TransactionsController::class, 'GetCustomerTransactionDetails'])->name('customer.transaction.details');

    //Finalize Customer Transaction
    Route::post('/transactions/finalize/{id}', [TransactionsController::class, 'finalizeTransaction'])->name('finalize.transation');
    Route::put('/transaction/cancel/receipt/{id}', [TransactionsController::class, 'cancelReceipt'])->name('cancel.receipt');

    //Receipt PDF Management
    Route::get('/customer/receipt/{id}', [TransactionsController::class, 'customerReceiptPDF'])->name('customer.receipt.pdf');
    Route::get('/concessionaire/receipt/{id}', [TransactionsController::class, 'concessionaireReceiptPDF'])->name('concessionaire.receipt.pdf');

    //Concessionaire Bills Management
    Route::get('concessionaires/billing/list', [BillsController::class, 'GetBillingList'])->name('concessionaires.billing.list');
    Route::match(['get', 'post'], 'concessionaires/billing/new', [BillsController::class, 'CreateNewBilling'])->name('concessionaires.billing.new');
    Route::get('concessionaire/billing/electricity/{id}', [BillsController::class, 'electricityBillingStatement'])->name('concessionaire.bill.electricity.pdf');
    Route::get('concessionaire/billing/water/{id}', [BillsController::class, 'waterBillingStatement'])->name('concessionaire.bill.water.pdf');

    //User Management
    Route::get('/users/profile', [UsersController::class, 'showUserProfile'])->name('user.profile');
    Route::post('/users/profile', [UsersController::class, 'updateProfile'])->name('user.update');
    Route::post('/users/new/password', [UsersController::class, 'newPassword'])->name('new.password.save');

    //Superadmin only pages
    Route::group(['prefix' => 'admin', 'middleware' => ['admin.auth:Superadmin']], function () {
        
        //Fees Management
        Route::get('fees/list', [FeesController::class, 'feesList'])->name('fees.list');
        Route::post('fees/add', [FeesController::class, 'AddFees'])->name('fees.add');
        Route::post('fees/{id}/update', [FeesController::class, 'UpdateFees'])->name('fees.update');
        Route::get('fees/{id}/delete', [FeesController::class, 'deleteFees'])->name('fees.delete');
        Route::get('fees/{id}/restore', [FeesController::class, 'restoreFees'])->name('fees.restore');

        //User Management
        Route::get('/users/list', [UsersController::class, 'getUsersList'])->name('users.list');
        Route::post('/users/update/role/{id}', [UsersController::class, 'updateUserRole'])->name('users.update.role');
        Route::get('/register', [LoginController::class, 'register'])->name('register');
        Route::post('/register', [LoginController::class, 'registerPost'])->name('register');

        //Concessionaires Management
        Route::get('concessionaires/list', [ConcessionairesController::class, 'GetConcessionairesList'])->name('concessionaires.list');
        Route::post('concessionaires/add', [ConcessionairesController::class, 'AddNewConcessionaire'])->name('concessionaires.add');
        Route::post('concessionaires/update/{id}', [ConcessionairesController::class, 'updateConcessionaire'])->name('concessionaires.update');
        Route::get('concessionaires/{id}/delete', [ConcessionairesController::class, 'deleteConcessionaire'])->name('concessionaires.delete');
        Route::get('concessionaires/{id}/restore', [ConcessionairesController::class, 'restoreConcessionaire'])->name('concessionaires.restore');

        //Receipts Management
        Route::get('/admin/receipts', [ReceiptsController::class, 'manage'])->name('receipts.manage');
        Route::post('/admin/receipts/add-batch', [ReceiptsController::class, 'addBatch'])->name('receipts.addBatch');
        Route::put('/admin/receipts/edit-batch/{id}', [ReceiptsController::class, 'editBatch'])->name('receipts.editBatch');
        Route::delete('/admin/receipts/delete-batch/{id}', [ReceiptsController::class, 'deleteBatch'])->name('receipts.deleteBatch');

        //Audit logs
        Route::get('/audit/logs', [AuditLogController::class, 'index'])->name('audit.logs');

        //Backup and Restore
        Route::get('/backups/manage', [BackupController::class, 'showManageView'])->name('backups.manage');
        Route::post('/backups/export', [BackupController::class, 'exportDatabase'])->name('backups.export');
        Route::post('/backups/restore/{id}', [BackupController::class, 'restoreBackup'])->name('backups.restore');
        Route::delete('/backups/delete/{id}', [BackupController::class, 'deleteBackup'])->name('backups.delete');

        //Transactions Monthly Report
        Route::get('/reports/monthly/export', [TransactionsController::class, 'exportMonthlyReport'])->name('reports.monthly.export');
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('data.analytics');
    });

});

Route::group(['middleware' => ['user.auth']], function() {
    Route::get('/verify-otp', [OtpController::class, 'showForm'])->name('otp.verify.form');
    Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/resend-otp', [OtpController::class, 'resend'])->name('otp.resend');
    
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::group(['middleware' => ['redirect.auth'], 'prefix' > '/admin'], function() {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'loginPost'])->name('login.submit');

    Route::get('/forgot/password/enter/email', [LoginController::class, 'forgotPassword'])->name('forgot.password.email');
    Route::post('/forgot/password/enter/email', [LoginController::class, 'forgotPasswordOtp'])->name('forgot.password.email');
    
    Route::get('/forgot/password/new', [LoginController::class, 'forgotPasswordForm'])->name('forgot.password.form');
    Route::post('/forgot/password/new', [LoginController::class, 'forgotPasswordPost'])->name('forgot.password.form');

    Route::post('/forgot-password/resend-otp', [LoginController::class, 'resendOtp'])->name('forgot.password.resendOtp');
});

Route::group(['middleware' => ['redirect.auth'], 'prefix' => '/customer'], function () {
    
    //Student-side Webpages
    Route::group(['prefix' => '/student'], function(){
        //Self-Service Student Payment Form
        Route::match(['get', 'post'], '/payment/form', [PaymentsController::class, 'selfServiceStudentPayment'])->name('student.payment.form');
        //Payment Form Success
        Route::get('/payment/submitted/{transaction_num}', function ($transaction_num) {
            return view('students.submitted', compact('transaction_num'));
        })->name('students.submitted');
    });
    
});

Route::group(['middleware' => ['redirect.auth'], 'prefix' => '/'], function () {

    Route::get('/', function () {
        return view('common.home');
    })->name('home');

});