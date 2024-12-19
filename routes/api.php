<?php

use App\Http\Controllers\DepositController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');


Route::post('/deposit/webhook', [DepositController::class, 'webhook'])->name('deposit-webhook');
