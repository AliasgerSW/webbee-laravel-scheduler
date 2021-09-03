<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchedulesProvider;

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
    return view('welcome');
});

//Get all schedule with active slots
Route::get('/schedules', [SchedulesProvider::class, 'getSchedulesWithSlots']);

//Book a schedule (Schedule and Slots value type validation added)
Route::post('/book/{schedule_id}/{slot_id}', [SchedulesProvider::class, 'book'])->whereNumber('schedule_id')->whereNumber('slot_id');
