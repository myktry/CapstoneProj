<?php

use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()->role === 'admin') {
        return redirect('/admin');
    }
    return view('welcome');
})->name('home');

// Checkout (Stripe)
Route::middleware('auth')->get('/checkout/create', [CheckoutController::class, 'create'])->name('checkout.create');
Route::get('/booking/success', [CheckoutController::class, 'success'])->name('booking.success');
Route::get('/booking/cancel',  [CheckoutController::class, 'cancel'])->name('booking.cancel');


Route::get('/book-appointment', function () {
	if (auth()->user()?->role === 'admin') {
		return redirect('/admin');
	}

	return redirect()->route('home', ['booking' => 1]);
})->middleware('auth')->name('book.appointment');

Route::middleware('auth')->group(function () {
	Route::get('/dashboard', function () {
		if (auth()->user()?->role === 'admin') {
			return redirect('/admin');
		}

		return redirect()->route('home');
	})->name('dashboard');

	Route::view('/profile', 'profile')->name('profile');

	Route::post('/logout', function () {
		auth()->guard('web')->logout();
		request()->session()->invalidate();
		request()->session()->regenerateToken();

		return redirect()->route('home');
	})->name('logout');
});

require __DIR__.'/auth.php';

