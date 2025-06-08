<?php

use App\Http\Controllers\ConcessionairesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ReceiptsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Psy\Readline\Transient;

use function Pest\Laravel\get;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'admin', 'middleware' => ['user.auth']], function () {
    
    //Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    //Fees Management
    Route::get('fees/list', [FeesController::class, 'GetFeesList'])->name('fees.list');
    Route::get('fees/list/deleted', [FeesController::class, 'deletedFeesList'])->name('fees.list.deleted');

    //Customer Payments
    Route::match(['get', 'post'], '/payments/student/new', [PaymentsController::class, 'StudentPayment'])->name('payments.student.new');
    Route::match(['get', 'post'], '/payments/outsider/new', [PaymentsController::class, 'OutsiderPayment'])->name('payments.outsider.new');
    //List of pending payments
    Route::get('/payments/pending', [PaymentsController::class, 'GetPendingPaymentsList'])->name('payments.pending');
    //View Details of Pending Payments and Edit them before approving payment
    Route::match(['get', 'put'], '/payments/pending/{transactionId}', [PaymentsController::class, 'updateUnpaidTransaction'])->name('payments.update');
    
    //Transaction Managements
    Route::get('/transactions/history', [TransactionsController::class, 'GetTransactionsHistory'])->name('receipts.list');
    Route::get('/transactions/customer/receipt/{id}', [TransactionsController::class, 'GetCustomerReceipt'])->name('customer.receipt');
    Route::get('/transactions/concessionaire/receipt/{id}', [TransactionsController::class, 'GetConcessionaireReceipt'])->name('concessionaire.receipt');

    //Receipt PDF Management
    Route::get('/customer/receipt/{id}', [TransactionsController::class, 'customerReceiptPDF'])->name('customer.receipt.pdf');
    Route::get('/concessionaire/receipt/{id}', [TransactionsController::class, 'concessionaireReceiptPDF'])->name('concessionaire.receipt.pdf');

    //Concessionaire Management
    Route::get('concessionaires/list', [ConcessionairesController::class, 'GetConcessionairesList'])->name('concessionaires.list');
    Route::get('concessionaires/billing/list', [ConcessionairesController::class, 'GetBillingList'])->name('concessionaires.billing.list');
    Route::match(['get', 'post'], 'concessionaires/billing/new', [ConcessionairesController::class, 'CreateNewBilling'])->name('concessionaires.billing.new');
    Route::match(['get', 'post'], 'concessionaires/billing/payment', [ConcessionairesController::class, 'BillsPayment'])->name('concessionaires.billing.payment');

    //User Management
    Route::get('/users/profile', [UsersController::class, 'showUserProfile'])->name('user.profile');
    Route::post('/users/profile', [UsersController::class, 'updateProfile'])->name('user.update');
    Route::post('/users/new/password', [UsersController::class, 'newPassword'])->name('new.password.save');

    //Data Analysis and Reports

    //Superadmin only pages
    Route::group(['prefix' => 'admin', 'middleware' => ['admin.auth:Superadmin']], function () {
        
        //Fees Maintenance
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
        Route::post('concessionaires/add', [ConcessionairesController::class, 'AddNewConcessionaire'])->name('concessionaires.add');
        Route::post('concessionaires/update/{id}', [ConcessionairesController::class, 'updateConcessionaire'])->name('concessionaires.update');

    });

});

Route::group(['middleware' => 'guest', 'prefix' > '/admin'], function() {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'loginPost'])->name('login.submit');
});

Route::group(['middleware' => 'guest', 'prefix' => '/customer'], function () {
    //Student-side Webpages
    Route::group(['prefix' => '/student'], function(){
        //Student Home Page
        Route::get('/dashboard', function () {
            return view('students.student-dashboard');
        })->name('student.dashboard');
        //Self-Service Student Payment Form
        Route::match(['get', 'post'], '/payment/form', [PaymentsController::class, 'selfServiceStudentPayment'])->name('student.payment.form');
        //Payment Form Success
        Route::get('/payment/submitted/{transactionId}', function ($transactionId) {
            return view('students.submitted', compact('transactionId'));
        })->name('students.submitted');
    });
    
    //Concessionaire Webpages
    Route::group(['prefix' => '/concessionaire'], function() {

    });
});

Route::group(['middleware' => ['user.auth']], function() {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::group(['middleware' => 'guest', 'prefix' => '/'], function () {

    Route::get('/', function () {
        return view('common.home');
    })->name('home');

});