<?php

use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// CRUDs Limpos e sem duplicidade
Route::apiResource('customers', CustomerController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('contracts', ContractController::class);

// Endpoints adicionais de itens
Route::post('contracts/{contract}/items', [ContractController::class, 'addItem']);
Route::delete('contracts/{contract}/items/{serviceId}', [ContractController::class, 'removeItem']);