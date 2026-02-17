<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('stripe',function(){
    return view('stripe');
})->name('stripe');


Route::get('stipe/connect/refresh', function () {
    return view('stripe_connect_refresh');
})->name('stripe.connect.refresh');
Route::get('/stripe/connect/return', function () {
    return view('stripe-connect');
})->name('stripe.connect.return');