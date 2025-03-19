<?php

use App\Http\Controllers\ConcessionairesController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\ReceiptsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'admin'], function () {
    Route::get('/home', function () {
        return view('common.home');
    });

    //Items List
    Route::get('fees/list', [FeesController::class, 'GetFeesList'])->name('FeesList');
    //Add Items
    Route::post('fees/add', [FeesController::class, 'AddFees'])->name('AddFees');
    //Update Items
    Route::post('fees/{id}/update', [FeesController::class, 'UpdateFees'])->name('UpdateFees');

    //Concessionaires
    Route::get('concessionaires/list', [ConcessionairesController::class, 'GetConcessionairesList'])->name('ConcessionairesList');
    Route::post('concessionaires/add', [ConcessionairesController::class, 'AddNewConcessionaire'])->name('AddConcessionaires');
    Route::get('concessionaires/billing/list', [ConcessionairesController::class, 'GetConcessionaireBillingList'])->name('ConcessionaireBilling');
    //Billing Statement
    Route::match(['get', 'post'], 'concessionaires/billing/new', [ConcessionairesController::class, 'CreateNewBilling'])->name('AddConcessionaireBilling');
    Route::get('/concessionaires/billing/details', function () {
        return view('common.concessionaires.billing-details');
    });

    //Transactions History
    Route::get('/transactions/list', [TransactionsController::class, 'GetTransactionsList'])->name('TransactionsList');
    Route::get('/transactions/student/details/{id}', [TransactionsController::class, 'GetStudentTransactionDetails'])->name('StudentTransactionDetails');
    Route::get('/transactions/concessionaire/details/{id}', [TransactionsController::class, 'GetConcessionaireTransactionDetails'])->name('ConcessionaireTransactionDetails');

    //Insert Transactions
    Route::match(['get', 'post'], '/transactions/student/new', [TransactionsController::class, 'InsertNewStudentTransaction'])->name('InsertNewStudentTransaction');
    Route::match(['get', 'post'], '/transactions/concessionaire/new', [TransactionsController::class, 'InsertNewConcessionaireTransaction'])->name('InsertNewConcessionaireTransaction');

    //Pay and Generate Receipt for Student Transaction
    Route::get('/pay-student-transaction', [TransactionsController::class, 'PayStudentTransaction'])->name('PayStudentTransaction');
    Route::get('/generate-student-receipt/{id}', [TransactionsController::class, 'GenerateReceipt'])->name('GenerateStudentReceipt');

    //Receipts
    Route::get('/receipts/list', [ReceiptsController::class, 'GetReceiptList'])->name('ReceiptsList');
    Route::get('/receipts/student/details/{id}', [ReceiptsController::class, 'GetStudentReceiptDetails'])->name('StudentReceiptDetails');
    Route::get('/receipts/concessionaire/details/{id}', [ReceiptsController::class, 'GetConcessionaireReceiptDetails'])->name('ConcessionairetReceiptDetails');
});

Route::group(['prefix' => 'student'], function () {

    Route::get('/home', function () {
        return view('user.home');
    });

    //Transactions History
    Route::get('/transactions/history', [StudentsController::class, 'StudentTransactionHistory'])->name('TransactionsHistory');
    Route::get('/transactions/details/{id}', [StudentsController::class, 'StudentTransactionDetails'])->name('StudentTransactionDetails');

    //Transaction Form
    Route::match(['get', 'post'], '/transactions/new/form', [StudentsController::class, 'NewStudentTransaction'])->name('NewStudentTransaction');

    //Items List
    Route::get('fees/list', [StudentsController::class, 'StudentFeesList'])->name('StudentFeesList');
});

Route::group(['prefix' => 'guest'], function () {

    Route::get('/register', function () {
        return view('login.register');
    });

    Route::get('/login', function () {
        return view('login.login');
    });

});
