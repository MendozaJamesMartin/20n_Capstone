<?php

use App\Http\Controllers\ConcessionairesController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReceiptsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\transactionController;
use App\Http\Controllers\TransactionsController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'admin', 'middleware' => ['user.auth', 'admin.auth:admin']], function () {
    Route::get('/home', function () {
        return view('common.home');
    })->name('admin.home');

    //Items List
    Route::get('fees/list', [FeesController::class, 'GetFeesList'])->name('fees.list');
    //Add Items
    Route::post('fees/add', [FeesController::class, 'AddFees'])->name('fees.add');
    //Update Items
    Route::post('fees/{id}/update', [FeesController::class, 'UpdateFees'])->name('fees.update');

    //List of Users
    Route::get('concessionaires/list', [ConcessionairesController::class, 'GetConcessionairesList'])->name('concessionaires.list');
    Route::post('concessionaires/add', [ConcessionairesController::class, 'AddNewConcessionaire'])->name('concessionaires.add');
    Route::get('students/list', [StudentsController::class, 'GetStudentsList'])->name('students.list');

    //Concessionaire Billings
    Route::get('concessionaires/billing/list', [ConcessionairesController::class, 'GetConcessionaireBillingList'])->name('concessionaires.billing');
    //Billing Statement
    Route::match(['get', 'post'], 'concessionaires/billing/new', [ConcessionairesController::class, 'CreateNewBilling'])->name('concessionaires.add.billing');

    //Transactions History
    Route::get('/transactions/list/unpaid', [TransactionsController::class, 'GetTransactionsList'])->name('transactions.list');
    Route::get('/transactions/student/details/{id}', [TransactionsController::class, 'GetStudentTransactionDetails'])->name('student.transaction.details');
    Route::get('/transactions/concessionaire/details/{id}', [TransactionsController::class, 'GetConcessionaireTransactionDetails'])->name('concessionaire.transaction.details');
    Route::get('/transactions/outsider/details/{id}', [TransactionsController::class, 'GetOutsiderTransactionDetails'])->name('outsider.transaction.details');

    //Insert Transactions
    Route::match(['get', 'post'], '/transactions/student/new', [TransactionsController::class, 'InsertNewStudentTransaction'])->name('student.transaction.new');
    Route::match(['get', 'post'], '/transactions/concessionaire/new', [TransactionsController::class, 'InsertNewConcessionaireTransaction'])->name('concessionaire.transaction.new');
    Route::match(['get', 'post'], '/transactions/outsider/new', [TransactionsController::class, 'InsertNewOutsiderTransaction'])->name('outsider.transaction.new');

    //Pay and Generate Receipt for Student Transaction
    Route::get('/pay-student-transaction', [TransactionsController::class, 'PayStudentTransaction'])->name('student.transaction.pay');
    Route::get('/generate-student-receipt/{id}', [TransactionsController::class, 'GenerateReceipt'])->name('student.generate.receipt');

    //Receipts
    Route::get('/receipts/list', [ReceiptsController::class, 'GetReceiptList'])->name('receipts.list');
    Route::get('/receipts/student/details/{id}', [ReceiptsController::class, 'GetStudentReceiptDetails'])->name('student.receipt.details');
    Route::get('/receipts/concessionaire/details/{id}', [ReceiptsController::class, 'GetConcessionaireReceiptDetails'])->name('concessionaire.receipt.details');
    Route::get('/receipts/outsider/details/{id}', [ReceiptsController::class, 'GetOutsiderReceiptDetails'])->name('outsider.receipt.details');

});

Route::group(['prefix' => 'student', 'middleware' => ['user.auth']], function () {

    Route::get('/home', function () {
        return view('student.home');
    })->name('student.home');

    //Transactions History
    Route::get('/transactions/history', [StudentsController::class, 'StudentTransactionHistory'])->name('transactions.history');
    Route::get('/transactions/details/{id}', [StudentsController::class, 'StudentTransactionDetails'])->name('transaction.details');

    //User Profile
    Route::get('/profile', [StudentsController::class, 'StudentProfile'])->name('student.profile');

    //Transaction Form
    Route::match(['get', 'post'], '/transactions/new/form', [StudentsController::class, 'NewStudentTransaction'])->name('transaction.form');

    //Items List
    Route::get('fees/list', [StudentsController::class, 'StudentFeesList'])->name('student.fees.list');
});

Route::group(['middleware' => ['user.auth']], function() {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::group(['middleware' => 'guest', 'prefix' => '/'], function () {

    Route::get('/register', [LoginController::class, 'register'])->name('register');
    Route::post('/register', [LoginController::class, 'registerPost'])->name('register');

    Route::get('/register/admin', [LoginController::class, 'registerAdmin'])->name('register.admin');
    Route::post('/register/admin', [LoginController::class, 'registerPostAdmin'])->name('register.admin');

    Route::get('/', [LoginController::class, 'login'])->name('login');
    Route::post('/', [LoginController::class, 'loginPost'])->name('login.submit');

});
