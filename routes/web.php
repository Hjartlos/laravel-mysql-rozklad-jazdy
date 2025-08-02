<?php

use App\Http\Controllers\Admin\AdminLineController;
use App\Http\Controllers\Admin\AdminOperatingDayController;
use App\Http\Controllers\Admin\AdminStopController;
use App\Http\Controllers\Admin\AdminTimetableController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\StopController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\AdminPurchasedTicketController;
use App\Http\Controllers\Admin\AdminTransactionController;

Route::get('/', [StopController::class, 'index'])->name('stops.index');
Route::get('/stops/{stopId}', [StopController::class, 'show'])->name('stops.show');

Route::get('/lines', [LineController::class, 'index'])->name('lines.index');
Route::get('/lines/{lineId}', [LineController::class, 'show'])->name('lines.show');

Route::get('/route-planner', [RouteController::class, 'index'])->name('route-planner');
Route::post('/route-planner/search', [RouteController::class, 'search'])->name('route-planner.search');

Route::get('/auth', [AuthController::class, 'showAuthForm'])->name('auth');
Route::get('/login', [AuthController::class, 'showAuthForm'])->name('login');
Route::get('/register', [AuthController::class, 'showAuthForm'])->name('register');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/tickets/select-route', [TicketController::class, 'selectRoute'])->name('tickets.select-route');
Route::post('/tickets/calculate', [TicketController::class, 'calculate'])->name('tickets.calculate');
Route::get('/tickets/checkout-route', [TicketController::class, 'checkoutRoute'])->name('tickets.checkout-route');
Route::get('/tickets/payment/success', [TicketController::class, 'success'])->name('tickets.success');
Route::get('/tickets/payment/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');
Route::get('/tickets/my-tickets', [TicketController::class, 'myTickets'])->name('tickets.my-tickets');
Route::get('/tickets/my-tickets/{id}', [TicketController::class, 'showTicket'])->name('tickets.show-ticket');

Route::get('/api/common-lines', [RouteController::class, 'getCommonLines'])->name('web.api.common-lines');
Route::get('/api/lines/{line}/route-segment-stops', [RouteController::class, 'getRouteSegmentStops'])->name('web.api.route-segment-stops');

Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('admin/stops', AdminStopController::class, [
            'names' => 'admin.stops',
            'except' => ['index', 'show']
        ]);

        Route::resource('admin/lines', AdminLineController::class, [
            'names' => 'admin.lines',
            'except' => ['index', 'show']
        ]);
        Route::get('/admin/lines/{line}/stops', [AdminLineController::class, 'stops'])->name('admin.lines.stops');

        Route::resource('admin/timetable', AdminTimetableController::class, [
            'names' => 'admin.timetable',
            'parameters' => ['timetable' => 'trip'],
            'except' => ['index', 'show']
        ]);
        Route::get('/admin/timetable/line/{line}/stops', [AdminTimetableController::class, 'getLineStops'])
            ->name('admin.timetable.line.stops');


        Route::resource('admin/operatingdays', AdminOperatingDayController::class, [
            'names' => 'admin.operatingdays',
            'parameters' => ['operatingdays' => 'operatingDay'],
            'except' => ['index', 'show']
        ]);

        Route::resource('admin/users', AdminUserController::class, [
            'names' => 'admin.users',
            'except' => ['index', 'show']
        ]);

        Route::resource('admin/tickets', AdminTicketController::class, [
            'names' => 'admin.tickets',
            'except' => ['index', 'show']
        ]);

        Route::resource('admin/purchasedtickets', AdminPurchasedTicketController::class, [
            'names' => 'admin.purchasedtickets',
            'parameters' => ['purchasedtickets' => 'purchasedTicket'],
            'except' => ['index']
        ]);

        Route::get('/admin/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('admin.transactions.show');
    });
});
