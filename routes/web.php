<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Auth Controllers (flat namespace)
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientPackageController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\AdminBookingController;
use App\Models\EventPackage;
use App\Models\Review;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminController as AdminAdminController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\CalendarController as AdminCalendarController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\InventoryAssignmentController as AdminInventoryAssignmentController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;

// Client Controllers
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\AppointmentController as ClientAppointmentController;
use App\Http\Controllers\Client\CalendarController as ClientCalendarController;
use App\Http\Controllers\Client\NotificationController as ClientNotificationController;
use App\Http\Controllers\Client\ProfileController as ClientProfileController;
use App\Http\Controllers\Client\ReviewController as ClientReviewController;

Route::get('/', function () {
    // An already-authenticated visitor never sees the marketing page — they
    // go straight to their own dashboard. Only guests see the welcome page.
    // Kept consistent with AuthController::redirectToDashboard().
    if (Auth::check()) {
        return redirect()->route(match (Auth::user()->role) {
            'admin' => 'admin.dashboard',
            default => 'client.dashboard',
        });
    }

    $packages = EventPackage::where('is_available', true)->latest()->take(3)->get();

    $approvedReviews = Review::with(['client.user', 'booking'])
        ->where('status', 'Approved')
        ->latest()
        ->take(6)
        ->get();

    $reviewStats = [
        'average' => round(Review::where('status', 'Approved')->avg('rating') ?? 0, 1),
        'total' => Review::where('status', 'Approved')->count(),
    ];

    return view('welcome', compact('packages', 'approvedReviews', 'reviewStats'));
});

// Authentication
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ─── CLIENT ROUTES ─────────────────────────────────────────────
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

        // Packages
        Route::get('/packages', [ClientPackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/{package}', [ClientPackageController::class, 'show'])->name('packages.show');

        // Bookings
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        // Static "availability" and "{booking}/edit" segments must be
        // registered before the plain "/{booking}" wildcard below, or they'd
        // be swallowed as an id.
        Route::get('/bookings/availability', [BookingController::class, 'availability'])->name('bookings.availability');
        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

        // Reviews (only for a client's own Completed events)
        Route::get('/reviews', [ClientReviewController::class, 'index'])->name('reviews.index');
        Route::get('/bookings/{booking}/review', [ClientReviewController::class, 'create'])->name('reviews.create');
        Route::post('/bookings/{booking}/review', [ClientReviewController::class, 'store'])->name('reviews.store');
        Route::get('/reviews/{review}/edit', [ClientReviewController::class, 'edit'])->name('reviews.edit');
        Route::put('/reviews/{review}', [ClientReviewController::class, 'update'])->name('reviews.update');

        // Appointments
        Route::get('/appointments', [ClientAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create', [ClientAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [ClientAppointmentController::class, 'store'])->name('appointments.store');

        // Calendar
        Route::get('/calendar', [ClientCalendarController::class, 'index'])->name('calendar.index');

        // Notifications
        Route::get('/notifications', [ClientNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [ClientNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [ClientNotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
        Route::delete('/notifications/{notification}', [ClientNotificationController::class, 'destroy'])->name('notifications.destroy');

        // Profile
        Route::get('/profile', [ClientProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ClientProfileController::class, 'update'])->name('profile.update');
    });

    // ─── ADMIN ROUTES ──────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Packages
        Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
        Route::get('/packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

        // Bookings
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/decline', [AdminBookingController::class, 'decline'])->name('bookings.decline');
        Route::post('/bookings/{booking}/complete', [AdminBookingController::class, 'complete'])->name('bookings.complete');

        // Reviews (moderate client feedback before it appears publicly)
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/analytics', [AdminReviewController::class, 'analytics'])->name('reviews.analytics');
        Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        // Admin Accounts (admins managing other admin accounts)
        Route::get('/admins', [AdminAdminController::class, 'index'])->name('admins.index');
        Route::get('/admins/create', [AdminAdminController::class, 'create'])->name('admins.create');
        Route::post('/admins', [AdminAdminController::class, 'store'])->name('admins.store');
        Route::get('/admins/{admin}/edit', [AdminAdminController::class, 'edit'])->name('admins.edit');
        Route::put('/admins/{admin}', [AdminAdminController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}', [AdminAdminController::class, 'destroy'])->name('admins.destroy');

        // Clients
        Route::get('/clients', [AdminClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/{client}', [AdminClientController::class, 'show'])->name('clients.show');

        // Calendar
        Route::get('/calendar', [AdminCalendarController::class, 'index'])->name('calendar.index');

        // Appointments
        Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');
        Route::post('/appointments/{appointment}/approve', [AdminAppointmentController::class, 'approve'])->name('appointments.approve');
        Route::post('/appointments/{appointment}/decline', [AdminAppointmentController::class, 'decline'])->name('appointments.decline');
        Route::post('/appointments/{appointment}/reschedule', [AdminAppointmentController::class, 'reschedule'])->name('appointments.reschedule');

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/create', [AdminNotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications', [AdminNotificationController::class, 'store'])->name('notifications.store');
        Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Inventory Monitoring
        Route::prefix('inventory')->name('inventory.')->group(function () {
            // Static "assignments" segment must be registered before the
            // "/{inventoryItem}" wildcard below, or it would be swallowed as an id.
            Route::prefix('assignments')->name('assignments.')->group(function () {
                Route::get('/', [AdminInventoryAssignmentController::class, 'index'])->name('index');
                Route::get('/create', [AdminInventoryAssignmentController::class, 'create'])->name('create');
                Route::post('/', [AdminInventoryAssignmentController::class, 'store'])->name('store');
                Route::put('/{inventoryAssignment}/resolve', [AdminInventoryAssignmentController::class, 'resolve'])->name('resolve');
                Route::delete('/{inventoryAssignment}', [AdminInventoryAssignmentController::class, 'destroy'])->name('destroy');
            });

            Route::get('/', [AdminInventoryController::class, 'index'])->name('index');
            Route::get('/create', [AdminInventoryController::class, 'create'])->name('create');
            Route::post('/', [AdminInventoryController::class, 'store'])->name('store');
            Route::get('/{inventoryItem}', [AdminInventoryController::class, 'show'])->name('show');
            Route::get('/{inventoryItem}/edit', [AdminInventoryController::class, 'edit'])->name('edit');
            Route::put('/{inventoryItem}', [AdminInventoryController::class, 'update'])->name('update');
            Route::delete('/{inventoryItem}', [AdminInventoryController::class, 'destroy'])->name('destroy');
        });

        // Settings (Admin's own account)
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    });
});
