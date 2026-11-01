<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BirthdayMessageAdminController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GameAdminController;
use App\Http\Controllers\Admin\NameEntryAdminController;
use App\Http\Controllers\Admin\QuizAdminController;
use App\Http\Controllers\Admin\UrduContentAdminController;
use App\Http\Controllers\Admin\WallpaperAdminController;
use App\Http\Controllers\BirthdayMessageController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NameEntryController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ShareEventController;
use App\Http\Controllers\UrduContentController;
use App\Http\Controllers\WallpaperController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::post('/share-events', [ShareEventController::class, 'store'])->name('share-events.store');

Route::prefix('quizzes')->group(function () {
    Route::get('/', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/{quiz:slug}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/{quiz:slug}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/{quiz:slug}/result/{result:result_slug}', [QuizController::class, 'result'])->name('quizzes.result');
});

Route::prefix('games')->group(function () {
    Route::get('/', [GameController::class, 'index'])->name('games.index');
    Route::get('/{game:slug}', [GameController::class, 'show'])->name('games.show');
    Route::post('/{game:slug}/scores', [GameController::class, 'storeScore'])->name('games.scores.store');
});

Route::prefix('birthday-wishes')->group(function () {
    Route::get('/', [BirthdayMessageController::class, 'index'])->name('birthday-messages.index');
    Route::get('/{birthdayMessage:slug}', [BirthdayMessageController::class, 'show'])->name('birthday-messages.show');
});

Route::prefix('islamic-names')->group(function () {
    Route::get('/', [NameEntryController::class, 'index'])->name('name-entries.index');
    Route::get('/{nameEntry:slug}', [NameEntryController::class, 'show'])->name('name-entries.show');
});

Route::prefix('ur/entertainment')->group(function () {
    Route::get('/', [UrduContentController::class, 'index'])->name('urdu-content.index');
    Route::get('/{urduContent:slug}', [UrduContentController::class, 'show'])->name('urdu-content.show');
});

Route::prefix('wallpapers')->group(function () {
    Route::get('/', [WallpaperController::class, 'index'])->name('wallpapers.index');
    Route::get('/{wallpaper:slug}', [WallpaperController::class, 'show'])->name('wallpapers.show');
    Route::get('/{wallpaper:slug}/download', [WallpaperController::class, 'download'])->name('wallpapers.download');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('quizzes', QuizAdminController::class)->except(['show']);
        Route::post('quizzes/{quiz}/publish', [QuizAdminController::class, 'publish'])->name('quizzes.publish');
        Route::post('quizzes/{quiz}/unpublish', [QuizAdminController::class, 'unpublish'])->name('quizzes.unpublish');

        Route::resource('games', GameAdminController::class)->except(['show']);
        Route::post('games/{game}/publish', [GameAdminController::class, 'publish'])->name('games.publish');
        Route::post('games/{game}/unpublish', [GameAdminController::class, 'unpublish'])->name('games.unpublish');

        Route::resource('birthday-messages', BirthdayMessageAdminController::class)->except(['show']);
        Route::post('birthday-messages/{birthday_message}/publish', [BirthdayMessageAdminController::class, 'publish'])->name('birthday-messages.publish');
        Route::post('birthday-messages/{birthday_message}/unpublish', [BirthdayMessageAdminController::class, 'unpublish'])->name('birthday-messages.unpublish');

        Route::resource('name-entries', NameEntryAdminController::class)->except(['show']);
        Route::post('name-entries/{name_entry}/publish', [NameEntryAdminController::class, 'publish'])->name('name-entries.publish');
        Route::post('name-entries/{name_entry}/unpublish', [NameEntryAdminController::class, 'unpublish'])->name('name-entries.unpublish');

        Route::resource('categories', CategoryAdminController::class)->only(['index', 'store', 'update']);

        Route::resource('urdu-content', UrduContentAdminController::class)->except(['show']);
        Route::post('urdu-content/{urdu_content}/publish', [UrduContentAdminController::class, 'publish'])->name('urdu-content.publish');
        Route::post('urdu-content/{urdu_content}/unpublish', [UrduContentAdminController::class, 'unpublish'])->name('urdu-content.unpublish');

        Route::resource('wallpapers', WallpaperAdminController::class)->except(['show']);
        Route::post('wallpapers/{wallpaper}/publish', [WallpaperAdminController::class, 'publish'])->name('wallpapers.publish');
        Route::post('wallpapers/{wallpaper}/unpublish', [WallpaperAdminController::class, 'unpublish'])->name('wallpapers.unpublish');
    });
});
