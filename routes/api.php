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
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookReviewController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\OngkirController;
use App\Http\Controllers\VoucherController;

// ─── PUBLIC ROUTES ───────────────────────────────────────────────────────────

// Settings (public)
Route::get('/settings', [SettingController::class, 'index']);

// Auth
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Books (public catalog)
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);
Route::get('/books/slug/{slug}', [BookController::class, 'showBySlug']);

// Categories (public)
Route::get('/categories', [CategoryController::class, 'index']);

// Book Reviews (public)
Route::get('/reviews', [BookReviewController::class, 'index']);
Route::post('/reviews', [BookReviewController::class, 'store']);

// Articles (public)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);

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

// Ongkir (public)
Route::get('/ongkir/provinces', [OngkirController::class, 'getProvinces']);
Route::get('/ongkir/cities/{province}', [OngkirController::class, 'getCities']);
Route::post('/ongkir/calculate', [OngkirController::class, 'calculate']);

// ─── PROTECTED ROUTES (require Sanctum token) ─────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    // Auth (All authenticated users)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard Stats (All admin users)
    Route::get('/admin/dashboard-stats', function () {
        return response()->json([
            'total_books' => \App\Models\Book::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_articles' => \App\Models\Article::count(),
        ]);
    });

    // Books & Categories (superadmin, editor)
    Route::middleware('role:superadmin,editor')->group(function () {
        Route::get('/admin/books', [BookController::class, 'adminIndex']);
        Route::post('/admin/books', [BookController::class, 'store']);
        Route::put('/admin/books/{book}', [BookController::class, 'update']);
        Route::delete('/admin/books/{book}', [BookController::class, 'destroy']);
        
        Route::post('/admin/categories', [CategoryController::class, 'store']);
        Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy']);
    });

    // Orders (superadmin, cs)
    Route::middleware('role:superadmin,cs')->group(function () {
        Route::get('/admin/orders', [OrderController::class, 'index']);
        Route::get('/admin/orders/{order}', [OrderController::class, 'show']);
        Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus']);
    });

    // Content Management: Testimonials, Promos, Portfolios, Articles, Reviews (superadmin, admin, editor)
    // Editor allowed for articles, Admin Konten allowed for the rest.
    Route::middleware('role:superadmin,admin,editor')->group(function () {
        // Articles
        Route::post('/admin/articles', [ArticleController::class, 'store']);
        Route::put('/admin/articles/{article}', [ArticleController::class, 'update']);
        Route::delete('/admin/articles/{article}', [ArticleController::class, 'destroy']);
    });

    Route::middleware('role:superadmin,admin')->group(function () {
        Route::get('/admin/testimonials', [TestimonialController::class, 'adminIndex']);
        Route::post('/admin/testimonials', [TestimonialController::class, 'store']);
        Route::put('/admin/testimonials/{testimonial}', [TestimonialController::class, 'update']);
        Route::delete('/admin/testimonials/{testimonial}', [TestimonialController::class, 'destroy']);

        Route::get('/admin/promos', [PromoController::class, 'adminIndex']);
        Route::post('/admin/promos', [PromoController::class, 'store']);
        Route::put('/admin/promos/{promo}', [PromoController::class, 'update']);
        Route::delete('/admin/promos/{promo}', [PromoController::class, 'destroy']);

        Route::post('/admin/portfolios', [PortfolioController::class, 'store']);
        Route::put('/admin/portfolios/{portfolio}', [PortfolioController::class, 'update']);
        Route::delete('/admin/portfolios/{portfolio}', [PortfolioController::class, 'destroy']);
        
        Route::delete('/admin/reviews/{bookReview}', [BookReviewController::class, 'destroy']);
        
        Route::get('/admin/vouchers', [VoucherController::class, 'index']);
        Route::post('/admin/vouchers', [VoucherController::class, 'store']);
        Route::put('/admin/vouchers/{voucher}', [VoucherController::class, 'update']);
        Route::delete('/admin/vouchers/{voucher}', [VoucherController::class, 'destroy']);
    });

    // Settings and Users (superadmin only)
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::post('/admin/users', [UserController::class, 'store']);
        Route::put('/admin/users/{user}', [UserController::class, 'update']);
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy']);

        Route::post('/admin/settings', [SettingController::class, 'update']);
    });
});
