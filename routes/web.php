<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('frontend.index');
});
Route::get('/contact-us', function () {
    return view('frontend.contact-us');
});
Route::get('/buy-phone', function () {
    return view('frontend.buy-phone');
});
Route::get('/single', function () {
    return view('frontend.single');
});
Route::get('/pay-bills', function () {
    return view('frontend.pay-bills');
});
Route::get('/signup', function () {
    return view('frontend.signup');
});
Route::get('/signin', function () {
    return view('frontend.signin');
});