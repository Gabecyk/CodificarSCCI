<?php

use App\Http\Controllers\Api\ResponsibleController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('tickets', [TicketController::class, 'index']);
Route::post('tickets', [TicketController::class, 'store']);
Route::get('tickets/{id}', [TicketController::class, 'show'])->whereNumber('id');
Route::put('tickets/{id}', [TicketController::class, 'update'])->whereNumber('id');

Route::get('responsibles', [ResponsibleController::class, 'index']);
