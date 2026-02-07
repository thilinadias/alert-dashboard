<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/setup', [App\Http\Controllers\SetupController::class, 'index'])->name('setup.index');
Route::get('/setup/requirements', [App\Http\Controllers\SetupController::class, 'checkRequirements'])->name('setup.requirements');
Route::get('/setup/debug-env', [App\Http\Controllers\SetupController::class, 'debugEnv']);
Route::get('/setup/debug-config', [App\Http\Controllers\SetupController::class, 'debugConfig']);
Route::get('/setup/database', [App\Http\Controllers\SetupController::class, 'showDatabaseForm'])->name('setup.database');
Route::post('/setup/database', [App\Http\Controllers\SetupController::class, 'configureDatabase'])->name('setup.database.post');
Route::get('/setup/migrate', [App\Http\Controllers\SetupController::class, 'runMigrations'])->name('setup.migrate');
Route::get('/setup/admin', [App\Http\Controllers\SetupController::class, 'showAdminForm'])->name('setup.admin');
Route::post('/setup/admin', [App\Http\Controllers\SetupController::class, 'createAdmin'])->name('setup.admin.post');
Route::get('/setup/email', [App\Http\Controllers\SetupController::class, 'showEmailForm'])->name('setup.email');
Route::post('/setup/email', [App\Http\Controllers\SetupController::class, 'configureEmail'])->name('setup.email.post');
Route::get('/setup/ssl', [App\Http\Controllers\SetupController::class, 'showSslForm'])->name('setup.ssl');
Route::post('/setup/ssl/generate', [App\Http\Controllers\SetupController::class, 'generateSsl'])->name('setup.ssl.generate');
Route::get('/setup/finish', [App\Http\Controllers\SetupController::class, 'finish'])->name('setup.finish');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Alert Dashboard Routes
    Route::get('/alerts/export', [\App\Http\Controllers\AlertController::class, 'export'])->name('alerts.export');
    Route::get('/alerts', [\App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/sync', [\App\Http\Controllers\AlertController::class, 'sync'])->name('alerts.sync');
    Route::get('/alerts/critical', [\App\Http\Controllers\AlertController::class, 'critical'])->name('alerts.critical');
    Route::get('/alerts/default', [\App\Http\Controllers\AlertController::class, 'default'])->name('alerts.default');
    Route::get('/alerts/mine', [\App\Http\Controllers\AlertController::class, 'myAlerts'])->name('alerts.mine');
    Route::get('/alerts/{alert}', [\App\Http\Controllers\AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{alert}/take', [\App\Http\Controllers\AlertController::class, 'take'])->name('alerts.take');
    Route::post('/alerts/{alert}/release', [\App\Http\Controllers\AlertController::class, 'release'])->name('alerts.release');
    Route::post('/alerts/{alert}/resolve', [\App\Http\Controllers\AlertController::class, 'resolve'])->name('alerts.resolve');
    Route::post('/alerts/{alert}/close', [\App\Http\Controllers\AlertController::class, 'close'])->name('alerts.close');
    Route::post('/alerts/{alert}/reopen', [\App\Http\Controllers\AlertController::class, 'reopen'])->name('alerts.reopen');
    Route::post('/alerts/bulk-resolve', [\App\Http\Controllers\AlertController::class, 'bulkResolve'])->name('alerts.bulk-resolve');
});

// OAuth Routes
Route::get('/oauth/google', [App\Http\Controllers\OAuthController::class, 'redirectToGoogle'])->name('oauth.google');
Route::get('/oauth/callback', [App\Http\Controllers\OAuthController::class, 'handleGoogleCallback'])->name('oauth.callback');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Reports (Managers & Admins)
    Route::middleware(['permission:view_reports'])->group(function () {
        Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/download', [\App\Http\Controllers\ReportController::class, 'download'])->name('reports.download');
    });

    // User Management (Managers & Admins)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->middleware('permission:manage_users');

    // System Settings (Admins Only - or specific permission)
    Route::middleware(['permission:manage_settings'])->group(function () {
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::resource('classification-rules', \App\Http\Controllers\Admin\ClassificationRuleController::class);
        Route::post('classification-rules/reorder', [\App\Http\Controllers\Admin\ClassificationRuleController::class, 'reorder'])->name('classification-rules.reorder');
        Route::resource('sla-policies', \App\Http\Controllers\Admin\SlaPolicyController::class);
        Route::resource('clients', \App\Http\Controllers\Admin\ClientController::class);
        
        // System Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    });
});
