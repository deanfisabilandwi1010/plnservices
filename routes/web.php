<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/*
| Public website
*/
Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/tentang-kami', 'about')->name('about');
    Route::get('/manajemen', 'management')->name('management');
    Route::get('/tata-kelola', 'governance')->name('governance');
    Route::get('/layanan', 'services')->name('services');
    Route::get('/layanan/{service:slug}', 'service')->name('services.show');
    Route::get('/portofolio', 'portfolio')->name('portfolio');
    Route::get('/portofolio/{portfolio:slug}', 'portfolioShow')->name('portfolio.show');
    Route::get('/berita', 'news')->name('news');
    Route::get('/berita/{post:slug}', 'newsShow')->name('news.show');
    Route::get('/pengadaan', 'procurement')->name('procurement');
    Route::get('/wilayah-kerja', 'network')->name('network');
    Route::get('/kontak', 'contact')->name('contact');
    Route::post('/kontak', 'sendFeedback')->middleware('throttle:5,1')->name('contact.send');
});

/*
| Admin portal
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('settings/{section}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{section}', [SettingController::class, 'update'])->name('settings.update');
        Route::post('editor-upload', [ResourceController::class, 'editorUpload'])->name('editor-upload');

        Route::controller(ResourceController::class)->prefix('{module}')->name('resource.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{id}', 'show')->whereNumber('id')->name('show');
            Route::get('{id}/edit', 'edit')->whereNumber('id')->name('edit');
            Route::put('{id}', 'update')->whereNumber('id')->name('update');
            Route::delete('{id}', 'destroy')->whereNumber('id')->name('destroy');
        });
    });
});
