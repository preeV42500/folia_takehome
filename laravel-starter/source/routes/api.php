<?php

use App\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/**
 * Use this file to define new API routes under the /api/... path
 * 
 * Here are some example, user related endpoints we have established as an example
 */

Route::get('/users/{id}', [UserController::class, 'read']);
Route::post('/users', [UserController::class, 'create']);

// Reminder related routes 

Route::get('/', [ReminderController::class, 'index']);
Route::get('reminders/{id}', [ReminderController::class, 'read']);
Route::get('reminders/{user_id}', [ReminderController::class, 'getByUserId']);

// search by keyword 
Route::get('reminders/search', [ReminderController::class, 'getByKeyword']);

// get reminders based on recurrences within date range 
Route::get('reminders/date-range', [ReminderController::class, 'getByDateRange']);

// create reminder
Route::post('/reminders', [ReminderController::class, 'create'])

// update reminder 
Route::put('/reminders/{id}', [ReminderController::class, 'update'])

// delete reminder 
Route::delete('/reminders/{id}', [ReminderController::class, 'delete'])

// create recurrence rule
Route::post('/reminders/{id}/recurrence-rules', [ReminderController::class, 'createRecurrenceRule']);

// update recurrence rule 
Route::put('/reminders/{reminder_id}/recurrence-rules/{recurrence_rule_id}', [ReminderController::class, 'updateRecurrenceRule']);

// delete recurrence rule 
Route::delete('/reminders/recurrence-rules/{recurrence_rule_id}', [ReminderController::class, 'deleteRecurrenceRule']);
