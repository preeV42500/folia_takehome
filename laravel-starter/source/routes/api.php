<?php

use App\Controllers\API\UserController;
use App\Controllers\API\ReminderController;
use Illuminate\Support\Facades\Route;

/**
 * Use this file to define new API routes under the /api/... path
 * 
 * Here are some example, user related endpoints we have established as an example
 */

Route::get('/users/{id}', [UserController::class, 'read']);
Route::post('/users', [UserController::class, 'create']);

Route::get('/users/{user_id}/reminders', [ReminderController::class, 'getByUserId']);
// Reminder related routes 

// get all reminders
Route::get('/reminders', [ReminderController::class, 'readAll']);

// get reminder by id 
Route::get('/reminders/{id}', [ReminderController::class, 'read']);

// search by keyword 
Route::get('/search', [ReminderController::class, 'getByKeyword']);

// get reminders based on recurrences within date range 
Route::get('/date-range', [ReminderController::class, 'getRemindersForDateRange']);

// create reminder
Route::post('/reminders', [ReminderController::class, 'create']);

// update reminder 
Route::put('/reminders/{id}', [ReminderController::class, 'update']);

// delete reminder 
Route::delete('/reminders/{id}', [ReminderController::class, 'delete']);


