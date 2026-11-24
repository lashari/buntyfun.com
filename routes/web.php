<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AiKeyAdminController;
use App\Http\Controllers\Admin\AiStudioController;
use App\Http\Controllers\Admin\AiToolAdminController;
use App\Http\Controllers\Admin\AutomationController;
use App\Http\Controllers\Admin\MessageAdminController;
use App\Http\Controllers\Admin\BirthdayMessageAdminController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GameAdminController;
use App\Http\Controllers\Admin\NameEntryAdminController;
use App\Http\Controllers\Admin\QuizAdminController;
use App\Http\Controllers\Admin\SocialAdminController;
use App\Http\Controllers\Admin\UrduContentAdminController;
use App\Http\Controllers\Admin\WallpaperAdminController;
use App\Http\Controllers\AiFunController;
use App\Http\Controllers\BirthdayMessageController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NameEntryController;
use App\Http\Controllers\PageController;
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
Route::get('/feed.xml', [SeoController::class, 'feed'])->name('feed');
Route::get('/ads.txt', [PageController::class, 'adsTxt'])->name('ads-txt');

Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/editorial-policy', [PageController::class, 'editorial'])->name('pages.editorial');
Route::redirect('/privacy', '/privacy-policy', 301);

Route::post('/share-events', [ShareEventController::class, 'store'])->middleware('throttle:30,1')->name('share-events.store');

Route::prefix('quizzes')->group(function () {
    Route::get('/', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/{quiz:slug}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/{quiz:slug}/submit', [QuizController::class, 'submit'])->middleware('throttle:20,1')->name('quizzes.submit');
    Route::get('/{quiz:slug}/result/{result:result_slug}', [QuizController::class, 'result'])->name('quizzes.result');
});

Route::prefix('games')->group(function () {
    Route::get('/', [GameController::class, 'index'])->name('games.index');
    Route::get('/{game:slug}', [GameController::class, 'show'])->name('games.show');
    Route::post('/{game:slug}/scores', [GameController::class, 'storeScore'])->middleware('throttle:20,1')->name('games.scores.store');
});

Route::prefix('birthday-wishes')->group(function () {
    Route::get('/', [BirthdayMessageController::class, 'index'])->name('birthday-messages.index');
    Route::get('/urdu', [BirthdayMessageController::class, 'urdu'])->name('birthday-messages.urdu');
    Route::get('/for/{relationship}', [BirthdayMessageController::class, 'relationship'])
        ->where('relationship', '[a-z-]+')->name('birthday-messages.relationship');
    Route::get('/{birthdayMessage:slug}', [BirthdayMessageController::class, 'show'])->name('birthday-messages.show');
});

Route::prefix('islamic-names')->group(function () {
    Route::get('/', [NameEntryController::class, 'index'])->name('name-entries.index');
    Route::get('/{nameEntry:slug}', [NameEntryController::class, 'show'])->name('name-entries.show');
});

Route::get('/{urduPage}', [UrduContentController::class, 'page'])
    ->where('urduPage', implode('|', array_keys(\App\Content\UrduPages::PAGES)))
    ->name('urdu-content.page');
Route::prefix('ur/entertainment')->group(function () {
    Route::get('/', [UrduContentController::class, 'index'])->name('urdu-content.index');
    Route::get('/{urduContent:slug}', [UrduContentController::class, 'show'])->name('urdu-content.show');
});

Route::get('/new-year-wishes', [MessageController::class, 'newYear'])->name('messages.new-year');
Route::get('/{messagePage}', [MessageController::class, 'page'])
    ->where('messagePage', implode('|', array_keys(\App\Content\MessagePages::PAGES)))
    ->name('messages.page');

Route::prefix('messages')->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/{message:slug}', [MessageController::class, 'show'])->name('messages.show');
});

Route::prefix('wallpapers')->group(function () {
    Route::get('/', [WallpaperController::class, 'index'])->name('wallpapers.index');
    Route::get('/c/{category}', [WallpaperController::class, 'category'])->where('category', '[a-z0-9-]+')->name('wallpapers.category');
    Route::get('/{wallpaper:slug}', [WallpaperController::class, 'show'])->name('wallpapers.show');
    Route::get('/{wallpaper:slug}/download', [WallpaperController::class, 'download'])->name('wallpapers.download');
});

Route::prefix('ai-fun')->group(function () {
    Route::get('/', [AiFunController::class, 'index'])->name('ai-tools.index');
    Route::get('/{aiTool:slug}', [AiFunController::class, 'show'])->name('ai-tools.show');
    Route::post('/{aiTool:slug}/generate', [AiFunController::class, 'generate'])
        ->middleware('throttle:6,1')
        ->name('ai-tools.generate');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
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

        Route::resource('messages', MessageAdminController::class)->except(['show']);
        Route::post('messages/{message}/publish', [MessageAdminController::class, 'publish'])->name('messages.publish');
        Route::post('messages/{message}/unpublish', [MessageAdminController::class, 'unpublish'])->name('messages.unpublish');

        Route::get('import', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('import.index');
        Route::post('import', [\App\Http\Controllers\Admin\ImportController::class, 'store'])->middleware('throttle:10,1')->name('import.store');
        Route::post('import/thumbnails', [\App\Http\Controllers\Admin\ImportController::class, 'thumbs'])->name('import.thumbs');
        Route::post('import/descriptions', [\App\Http\Controllers\Admin\ImportController::class, 'descriptions'])->middleware('throttle:30,1')->name('import.descriptions');
        Route::get('import/{run}', [\App\Http\Controllers\Admin\ImportController::class, 'show'])->name('import.show');
        Route::post('import/{run}/step', [\App\Http\Controllers\Admin\ImportController::class, 'step'])->name('import.step');
        Route::post('import/{run}/stop', [\App\Http\Controllers\Admin\ImportController::class, 'stop'])->name('import.stop');
        Route::post('import/{run}/publish', [\App\Http\Controllers\Admin\ImportController::class, 'publish'])->name('import.publish');
        Route::post('import/{run}/undo', [\App\Http\Controllers\Admin\ImportController::class, 'undo'])->name('import.undo');
        Route::get('import/{run}/csv', [\App\Http\Controllers\Admin\ImportController::class, 'csv'])->name('import.csv');
        Route::get('import/{run}/zip', [\App\Http\Controllers\Admin\ImportController::class, 'zip'])->name('import.zip');

        Route::get('ai-studio', [AiStudioController::class, 'index'])->name('ai-studio.index');
        Route::post('ai-studio/generate', [AiStudioController::class, 'generate'])->middleware('throttle:10,1')->name('ai-studio.generate');
        Route::get('ai-studio/{draft}', [AiStudioController::class, 'show'])->name('ai-studio.show');
        Route::put('ai-studio/{draft}', [AiStudioController::class, 'update'])->name('ai-studio.update');
        Route::post('ai-studio/{draft}/approve', [AiStudioController::class, 'approve'])->name('ai-studio.approve');
        Route::post('ai-studio/{draft}/reject', [AiStudioController::class, 'reject'])->name('ai-studio.reject');
        Route::post('ai-studio/{draft}/regenerate', [AiStudioController::class, 'regenerate'])->middleware('throttle:10,1')->name('ai-studio.regenerate');

        Route::get('growth-plan', [\App\Http\Controllers\Admin\GrowthPlanController::class, 'index'])->name('growth-plan');
        Route::get('automation', [AutomationController::class, 'index'])->name('automation.index');
        Route::get('automation/calendar', [AutomationController::class, 'calendar'])->name('automation.calendar');
        Route::get('automation/settings', [AutomationController::class, 'settings'])->name('automation.settings');
        Route::put('automation/settings', [AutomationController::class, 'saveSettings'])->name('automation.settings.save');
        Route::get('social', [SocialAdminController::class, 'index'])->name('social.index');
        Route::get('social/create', [SocialAdminController::class, 'create'])->name('social.create');
        Route::post('social', [SocialAdminController::class, 'store'])->name('social.store');
        Route::get('social/meta/connect', [SocialAdminController::class, 'connect'])->name('social.meta.connect');
        Route::get('social/meta/callback', [SocialAdminController::class, 'callback'])->name('social.meta.callback');
        Route::delete('social/accounts/{account}', [SocialAdminController::class, 'disconnect'])->name('social.accounts.disconnect');
        Route::post('social/{post}/approve', [SocialAdminController::class, 'approve'])->name('social.approve');
        Route::post('social/{post}/publish-now', [SocialAdminController::class, 'publishNow'])->middleware('throttle:10,1')->name('social.publish-now');
        Route::post('social/{post}/cancel', [SocialAdminController::class, 'cancel'])->name('social.cancel');
        Route::post('social/{post}/retry', [SocialAdminController::class, 'retry'])->name('social.retry');

        Route::post('ai-keys', [AiKeyAdminController::class, 'store'])->middleware('throttle:10,1')->name('ai-keys.store');
        Route::post('ai-keys/{key}/toggle', [AiKeyAdminController::class, 'toggle'])->name('ai-keys.toggle');
        Route::post('ai-keys/{key}/test', [AiKeyAdminController::class, 'test'])->middleware('throttle:10,1')->name('ai-keys.test');
        Route::delete('ai-keys/{key}', [AiKeyAdminController::class, 'destroy'])->name('ai-keys.destroy');
        Route::get('automation/runs/{run}', [AutomationController::class, 'showRun'])->name('automation.runs.show');
        Route::put('automation/{job}', [AutomationController::class, 'update'])->name('automation.update');
        Route::post('automation/{job}/run', [AutomationController::class, 'run'])->middleware('throttle:10,1')->name('automation.run');

        Route::resource('ai-tools', AiToolAdminController::class)->except(['show']);
        Route::post('ai-tools/{ai_tool}/publish', [AiToolAdminController::class, 'publish'])->name('ai-tools.publish');
        Route::post('ai-tools/{ai_tool}/unpublish', [AiToolAdminController::class, 'unpublish'])->name('ai-tools.unpublish');
    });
});
