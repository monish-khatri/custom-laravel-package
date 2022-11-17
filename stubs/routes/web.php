<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;
use App\Http\Middleware\ChangeSiteLanguage;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

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
// https://www.youtube.com/watch?v=H-euNqEKACA
Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/dashboard', function () {
        $language = session()->get('locale');
        if (! in_array($language, ['en', 'fr', 'hi'])) {
            $language = 'en';
        }
        App::setLocale($language);
        session()->put('locale', $language);
        return view('dashboard');
    })->middleware(ChangeSiteLanguage::class)->name('dashboard');

    Route::get('/published/blogs', [BlogController::class, 'published'])->name('blogs.published');
    Route::post('/trash-bin/{blog}/blogs', [BlogController::class, 'restore'])->name('blogs.restore');
    Route::get('/trash-bin/blogs', [BlogController::class, 'trashBin'])->name('blogs.trash_bin');
    Route::delete('/force-delete/{blog}/blogs', [BlogController::class, 'forceDestroy'])->name('blogs.forceDestroy');
    Route::post('/restore-all/blogs', [BlogController::class, 'restoreAll'])->name('blogs.restoreAll');
    Route::patch('/blogs/update_status/{blog}', [BlogController::class, 'updateStatus'])->name('blogs.update_status');

    Route::resource('blogs', BlogController::class)->missing(function (Request $request) {
        return Redirect::route('blogs.index');
    })->parameters(['blogs' => 'blog']);

    Route::post('/add_comment', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/remove_comment/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/pin_comment/{comment}', [CommentController::class, 'pinComment'])->name('comments.pin_comment');

    Route::get('change-lang/{locale}', function (Request $request, $locale) {
        if (! in_array($locale, ['en', 'fr', 'hi'])) {
            abort(404);
        }
        App::setLocale($locale);
        session()->put('locale', $locale);
        return redirect()->back();
    })->middleware('change_site_lang')->name('locale.setting');
});
