<?php

use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\DailyEmailSummaryConfigurationController;
use App\Http\Controllers\FindingCategoryController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReportEmailSettingController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffMemberController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'active'])->name('dashboard');

Route::middleware(['auth', 'active', 'role:glavni_admin,administrator_klinike'])->group(function () {
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/daily-email-summary', [DailyEmailSummaryConfigurationController::class, 'edit'])
        ->name('settings.daily-email-summary.edit');
    Route::put('settings/daily-email-summary', [DailyEmailSummaryConfigurationController::class, 'update'])
        ->name('settings.daily-email-summary.update');

    Route::resource('locations', LocationController::class);
    Route::resource('service-categories', ServiceCategoryController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('finding-categories', FindingCategoryController::class);
    Route::resource('findings', FindingController::class);
    Route::resource('staff-members', StaffMemberController::class);
    Route::resource('users', UserManagementController::class);
    Route::resource('report-email-settings', ReportEmailSettingController::class);
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth', 'active', 'role:glavni_admin,administrator_klinike,medicinska_sestra'])->group(function () {
    Route::resource('daily-reports', DailyReportController::class);
    Route::post('daily-reports/{daily_report}/items', [DailyReportController::class, 'storeItem'])
        ->name('daily-reports.items.store');
    Route::get('daily-reports/{daily_report}/items/{item}/edit', [DailyReportController::class, 'editItem'])
        ->name('daily-reports.items.edit');
    Route::put('daily-reports/{daily_report}/items/{item}', [DailyReportController::class, 'updateItem'])
        ->name('daily-reports.items.update');
    Route::delete('daily-reports/{daily_report}/items/{item}', [DailyReportController::class, 'destroyItem'])
        ->name('daily-reports.items.destroy');
    Route::post('daily-reports/{daily_report}/finding-items', [DailyReportController::class, 'storeFindingItem'])
        ->name('daily-reports.finding-items.store');
    Route::delete('daily-reports/{daily_report}/finding-items/{finding_item}', [DailyReportController::class, 'destroyFindingItem'])
        ->name('daily-reports.finding-items.destroy');
    Route::post('daily-reports/{daily_report}/submit', [DailyReportController::class, 'submit'])
        ->name('daily-reports.submit');
    Route::post('daily-reports/{daily_report}/reopen', [DailyReportController::class, 'reopen'])
        ->name('daily-reports.reopen');
    Route::post('patients/{patient}/payments', [PatientController::class, 'storePayment'])
        ->name('patients.payments.store');
    Route::resource('patients', PatientController::class);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
