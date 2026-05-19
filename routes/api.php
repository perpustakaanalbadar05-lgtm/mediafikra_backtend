<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;

// ─── PUBLIC ROUTES ───────────────────────────────────────────────────────────

// Settings (public)
Route::get('/settings', [SettingController::class, 'index']);

// Auth
Route::post('/login', [AuthController::class, 'login']);

// Books (public catalog)
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);
Route::get('/books/slug/{slug}', [BookController::class, 'showBySlug']);

// Testimonials (public)
Route::get('/testimonials', [TestimonialController::class, 'index']);

// Promos & News (public)
Route::get('/promos', [PromoController::class, 'index']);
Route::get('/promos/{promo}', [PromoController::class, 'show']);

// Portfolio (public)
Route::get('/portfolios', [PortfolioController::class, 'index']);
Route::get('/portfolios/{portfolio}', [PortfolioController::class, 'show']);

// Order / Checkout (public — user creates an order)
Route::post('/orders', [OrderController::class, 'store']);

// ─── PROTECTED ROUTES (require Sanctum token) ─────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Books (admin)
    Route::get('/admin/books', [BookController::class, 'adminIndex']);
    Route::post('/admin/books', [BookController::class, 'store']);
    Route::put('/admin/books/{book}', [BookController::class, 'update']);
    Route::delete('/admin/books/{book}', [BookController::class, 'destroy']);

    // Orders (admin)
    Route::get('/admin/orders', [OrderController::class, 'index']);
    Route::get('/admin/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus']);

    // Testimonials (admin)
    Route::get('/admin/testimonials', [TestimonialController::class, 'adminIndex']);
    Route::post('/admin/testimonials', [TestimonialController::class, 'store']);
    Route::put('/admin/testimonials/{testimonial}', [TestimonialController::class, 'update']);
    Route::delete('/admin/testimonials/{testimonial}', [TestimonialController::class, 'destroy']);

    // Promos (admin)
    Route::get('/admin/promos', [PromoController::class, 'adminIndex']);
    Route::post('/admin/promos', [PromoController::class, 'store']);
    Route::put('/admin/promos/{promo}', [PromoController::class, 'update']);
    Route::delete('/admin/promos/{promo}', [PromoController::class, 'destroy']);

    // Portfolios (admin)
    Route::post('/admin/portfolios', [PortfolioController::class, 'store']);
    Route::put('/admin/portfolios/{portfolio}', [PortfolioController::class, 'update']);
    Route::delete('/admin/portfolios/{portfolio}', [PortfolioController::class, 'destroy']);

    // Users (admin)
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::put('/admin/users/{user}', [UserController::class, 'update']);
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy']);

    // Settings (admin)
    Route::post('/admin/settings', [SettingController::class, 'update']);
});
